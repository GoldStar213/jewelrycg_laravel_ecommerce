<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelCheckoutRequest;
use App\Http\Requests\GalleryRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\ServicePackageRequest;
use App\Http\Requests\StorePaymentIntentRequest;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\OrderServiceDelivery;
use App\Models\OrderServiceRequirement;
use App\Models\OrderServiceRevisionRequest;
use App\Models\SellersProfile;
use App\Models\SellersWalletHistory;
use App\Models\ServiceCategorie;
use App\Models\ServiceOrder;
use App\Models\ServicePackage;
use App\Models\ServicePost;
use App\Models\ServicePostCategorie;
use App\Models\ServicePostTag;
use App\Models\ServiceRequirement;
use App\Models\ServiceRequirementChoice;
use App\Models\ServiceReview;
use App\Models\ServiceTags;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Notification;
use App\Models\OrderServiceExtendDeliveryTime;
use App\Models\Message;
use App\Models\ServicePackagesCustom;
use App\Mail\ServiceOrderPlacedMail;
use App\Mail\ServiceOrderPlacedNotifySellerMail;
use App\Mail\NotificationMail;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use DateTime;


class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::id();
        return view('seller.services.list', [
            'services' => ServicePost::with(['categories', 'postauthor'])->where('user_id', $user_id)->orderBy('id', 'DESC')->get(),
        ]);
    }

    public function all()
    {
        $services = ServicePost::with(['uploads', 'categories.category', 'postauthor.uploads', 'seller', 'packages'])
            ->leftJoin('orders_services', 'services.id', 'orders_services.service_id')
            ->leftJoin('service_reviews', 'service_reviews.order_id', 'orders_services.id')
            ->leftJoin('users', 'users.id', 'orders_services.user_id')
            ->select('services.*', DB::raw('FORMAT(AVG(case users.role when 0 then service_reviews.rating else null end), 1) rating, COUNT(case users.role when 0 then service_reviews.id else null end) count'))
            ->where('services.status', 1)
            ->groupBy('services.id')
            ->orderBy('services.id', 'DESC')
            ->get();

        // $services->each(function ($service) {
        //     $service->thumb_file_name = FFileManagerController::get_thumb_path($service->thumb->file_name);
        // });

        return view('service.index', [
            'services' => $services,
        ]);
    }

    public function detail($slug)
    {
        // Get the service post by slug
        $service = ServicePost::where('slug', $slug)->firstOrFail();

        // Fetch related data for the service post
        $data = ServicePost::with(['uploads', 'categories.category', 'postauthor.uploads', 'seller', 'packages', 'tags.tag'])
            ->leftJoin('orders_services', 'services.id', 'orders_services.service_id')
            ->leftJoin('service_reviews', 'service_reviews.order_id', 'orders_services.id')
            ->leftJoin('users', 'users.id', 'orders_services.user_id')
            ->select('services.*', DB::raw('FORMAT(AVG(case users.role when 0 then service_reviews.rating else null end), 1) rating, COUNT(case users.role when 0 then service_reviews.id else null end) count'))
            ->where('services.status', 1)
            ->where('slug', $slug)
            ->groupBy('services.id')
            ->orderBy('services.id', 'DESC')
            ->firstOrFail();

        // Retrieve the rating for the seller's profile
        $rating = SellersProfile::withWhereHas('user', fn($query) => $query->where('username', $data->postauthor->username))
            ->leftJoin('services', 'services.user_id', 'sellers_profile.user_id')
            ->leftJoin('orders_services', 'services.id', 'orders_services.service_id')
            ->leftJoin('service_reviews', function ($join) use ($service) {
                $join->on('service_reviews.order_id', 'orders_services.id')
                    ->where('service_reviews.user_id', '!=', $service->user_id);
            })
            ->select(DB::raw('FORMAT(AVG(service_reviews.rating), 1) rating, COUNT(service_reviews.id) count'))
            ->firstOrFail();

        // Retrieve reviews for the service post
        $review = ServiceReview::leftJoin('services', 'service_reviews.service_id', 'services.id')
            ->leftJoin('users', 'users.id', 'service_reviews.user_id')
            ->select('service_reviews.*', 'services.*', 'users.*', 'service_reviews.created_at as created_at')
            ->where('service_reviews.service_id', $service->id)
            ->where('service_reviews.user_id', '!=', $service->user_id)
            ->get();
        
        foreach ($review as $reviewItem) {
            $avatarUpload = Upload::find($reviewItem->avatar);
            $featuredUpload = Upload::find($reviewItem->review_attachment_id);
            if ($avatarUpload) {
                $reviewItem->avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);
            } else {
                $reviewItem->avatar_url = ''; // or set a default avatar URL
            }

            if ($featuredUpload) {
                $reviewItem->featured_img = $featuredUpload->getImageOptimizedFullName(600);
            } else {
                $reviewItem->featured_img = ''; // or set a default avatar URL
            }
        }
        
        
        // Retrieve the count of reviews for the service post
        $review_count = ServiceReview::leftJoin('services', 'service_reviews.service_id', 'services.id')
            ->where('service_reviews.service_id', $service->id)
            ->where('service_reviews.user_id', '!=', $service->user_id)
            ->count();

        // Calculate the average rating for the service post
        $average_rating = ServiceReview::select(DB::raw('AVG(rating) as average_rating'))
            ->where('service_id', $service->id)
            ->where('user_id', '!=', $service->user_id)
            ->first()->average_rating;

        $average_rating = number_format($average_rating ?: 0, 1);

        // Prepare the gallery data
        $gallery_ids = explode(',', $data->gallery);
        $galleries = [];
        for ($i = 0; $i < count($gallery_ids); $i++) {
            $gallery = Upload::where('id', $gallery_ids[$i])->first();
            if (!$gallery) {
                continue;
            }
            array_push($galleries, $gallery);
        }

        $tag_ids = [];
        for ($i = 0; $i < count($data->tags); $i++) {
            array_push($tag_ids, $data->tags[$i]->id_tag);
        }
        $data->tag_ids = $tag_ids;
        $data->galleries = $galleries;

        // Pass the data to the view
        return view('service.detail', [
            'service' => $data,
            'rating' => $rating,
            'average_rating' => $average_rating,
            'review_count' => $review_count,
            'review' => $review
        ]);
    }


    public function trash()
    {
        return view('seller.services.trash', [
            'services' => ServicePost::onlyTrashed()->orderBy('id', 'DESC')->get(),
        ]);
    }

    public function get()
    {
        $user_id = Auth::id();
        return datatables()->of(ServicePost::where('user_id', $user_id)->get())
            ->addIndexColumn()
            ->editColumn('cover_image', function ($row) {
                return "<img src='" . $row->cover_image . "'>";
            })
            ->addColumn('action', function ($row) {

                $btn = '<a href="' . route('seller.services.edit', $row->id) . '"  class="edit btn btn-info btn-sm">Edit</a>';
                $btn = $btn . '<a href="javascript:void(0)" class="edit btn btn-danger btn-sm">Delete</a>';

                return $btn;
            })
            ->rawColumns(['action', 'cover_image'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $step
     * @return \Illuminate\Http\Response
     */
    public function create($step = 0, $post_id = -1)
    {
        $data = null;
        if ($post_id != -1) {
            $data = ServicePost::with(['uploads', 'tags', 'categories', 'packages', 'requirements.choices'])->findOrFail($post_id);
            $gallery_ids = explode(',', $data->gallery);

            $galleries = [];
            for ($i = 0; $i < count($gallery_ids); $i++) {
                array_push($galleries, Upload::where('id', $gallery_ids[$i])->first());
            }

            $tag_ids = [];
            for ($i = 0; $i < count($data->tags); $i++) {
                array_push($tag_ids, $data->tags[$i]->id_tag);
            }

            $data->requirements->each(function ($requirement) {
                $choices = [];
                for ($i = 0; $i < count($requirement->choices); $i++) {
                    array_push($choices, $requirement->choices[$i]->choice);
                }
                $requirement->choices_str = join(",", $choices);
            });

            $data->tag_ids = $tag_ids;
            $data->galleries = $galleries;
        }

        $packages = ServicePackage::withTrashed()->where('service_id', $post_id)->get();

        $service = ServicePost::firstOrNew(['id' => $post_id]);
        $loadOptionCustomPricing = $service['option_custompricing'] ? $service['option_custompricing']: 0;

        // $step = 1;
        return view('seller.services.create', [
            'categories' => ServiceCategorie::all(),
            'tags' => ServiceTags::all(),
            'step' => $step,
            'post_id' => $post_id,
            'data' => $data,
            'packages' => $packages,
            'packages' => $packages,
            'loadOptionCustomPricing' => $loadOptionCustomPricing
        ]);
    }

    private function generateSlug($string)
    {
        // replace space into '-'
        $str_rersut = str_replace(' ', '-', $string);
        // remove #hashtag
        return str_replace('#', '', $str_rersut);
    }

    private function registerNewTag($tag)
    {
        $last = ServiceTags::where('name', $tag)->first();

        if ($last) {
            return $last->id;
        }

        $servicetag = ServiceTags::create([
            'name' => $tag,
            'slug' => $this->slugify($tag),
        ]);
        return $servicetag->id;
    }

    public function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        // removed #hastag
        $text = str_replace('#', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostStoreRequest $request)
    {
        $step = $request->step + 1;
        $post_id = $request->service_id;
        $tags = (array) $request->input('tags');
        $categories = (array) $request->input('categories');

        $data = $request->input();
        $data['user_id'] = Auth::id();

        $service = ServicePost::firstOrNew(['id' => $post_id]);
        $service->fill($data);
        $service->save();

        if (!$service->slug) {
            $count = ServicePost::where('slug', $this->slugify($request->name))->count();
            $slug = '';
            if ($count) {
                $slug = $this->slugify($request->name) . $count;
            } else {
                $slug = $this->slugify($request->name);
            }

            $service->update(['slug' => $slug]);
        }

        $post_id = $service->id;

        ServicePostTag::where('id_service', $post_id)->delete();
        ServicePostCategorie::where('id_post', $post_id)->delete();

        foreach ($tags as $tag) {
            $id_tag = (!is_numeric($tag)) ? $this->registerNewTag($tag) : $tag;
            ServicePostTag::create([
                'id_tag' => $id_tag,
                'id_service' => $post_id,
            ]);

        }

        foreach ($categories as $categorie) {
            ServicePostCategorie::create([
                'id_category' => $categorie,
                'id_post' => $post_id,
            ]);
        }

        return redirect()->route('seller.services.create', ['step' => $step, 'post_id' => $post_id]);
    }

    public function gallery(GalleryRequest $request)
    {
        $step = $request->step + 1;
        $post_id = $request->service_id;
        $thumb = $request->thumb;
        $gallery = $request->gallery;

        ServicePost::where('id', $post_id)->update(['thumbnail' => $thumb, 'gallery' => $gallery]);

        return redirect()->route('seller.services.create', ['step' => $step, 'post_id' => $post_id]);
    }

    private function create_requirement_choices($requirement_id, $choices_str)
    {
        $choices = explode(',', $choices_str);

        for ($i = 0; $i < count($choices); ++$i) {
            $requirement_choice = ServiceRequirementChoice::onlyTrashed()->first();
            if (!$requirement_choice) {
                $requirement_choice = new ServiceRequirementChoice();
            }
            $requirement_choice->requirement_id = $requirement_id;
            $requirement_choice->choice = $choices[$i];
            $requirement_choice->save();
            $requirement_choice->restore();
        }
    }

    public function requirement(Request $request)
    {
        $data = $request->input();
        $step = $data['step'] + 1;
        $post_id = $data['service_id'];
        $questions = $request->input('question');

        $last = ServiceRequirement::where('service_id', $post_id)->get();

        $last->each(function ($item) {
            ServiceRequirementChoice::where('requirement_id', $item->id)->delete();
        });
        ServiceRequirement::where('service_id', $post_id)->delete();

        if ($questions) {
            for ($i = 0; $i < count($questions); $i++) {
                if ($questions[$i]) {
                    $requirement = ServiceRequirement::withTrashed()->find($data['id'][$i]);
                    if (!$requirement) {
                        $requirement = new ServiceRequirement();
                    }
                    $requirement->service_id = $post_id;
                    $requirement->question = $data['question'][$i];
                    $requirement->type = $data['type'][$i];
                    $requirement->required = $data['required'][$i] == "true" ? 1 : 0;
                    $requirement->save();
                    $requirement->restore();

                    if ($requirement->type > 1) {
                        $this->create_requirement_choices($requirement->id, $data['choices'][$i]);
                    }
                }
            }
        }

        return redirect()->route('seller.services.create', ['step' => $step, 'post_id' => $post_id]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function package(Request $request) {
        
        $data = $request->input();
        $step = $request->step + 1;
        $post_id = $request->service_id;
        $names = $request->input('name');
        $package_count = $request->input('package_count');

        $service = ServicePost::firstOrNew(['id' => $post_id]);
        $service['option_custompricing'] = $data['option_custompricing'];
        $service->save();

        ServicePackage::withTrashed()->where('service_id', $post_id)->restore();
        $lasts = ServicePackage::where('service_id', $post_id)->get();
        $i = 0;

        if (count($lasts) > $package_count) {
            $i = 0;
            for (; $i < $package_count; $i++) {
                if ($names[$i]) {
                    $lasts[$i]['name'] = $data['name'][$i];
                    $lasts[$i]['service_id'] = $data['service_id'];
                    $lasts[$i]['description'] = $data['description'][$i];
                    $lasts[$i]['price'] = $data['price'][$i] * 100;
                    $lasts[$i]['revisions'] = $data['revisions'][$i];
                    $lasts[$i]['delivery_time'] = $data['delivery_time'][$i];
                    $lasts[$i]->save();
                }
            }
            for (; $i < count($lasts); $i++) {
                $lasts[$i]->delete();
            }
        } else {
            $i = 0;
            for (; $i < count($lasts); $i++) {
                if ($names[$i]) {
                    $lasts[$i]['name'] = $data['name'][$i];
                    $lasts[$i]['service_id'] = $data['service_id'];
                    $lasts[$i]['description'] = $data['description'][$i];
                    $lasts[$i]['price'] = $data['price'][$i] * 100;
                    $lasts[$i]['revisions'] = $data['revisions'][$i];
                    $lasts[$i]['delivery_time'] = $data['delivery_time'][$i];
                    $lasts[$i]->save();
                } else {
                    $lasts[$i]->delete();
                }
            }
            for (; $i < $package_count; $i++) {
                if ($names[$i]) {
                    $newPackage = new ServicePackage();
                    $newPackage['name'] = $data['name'][$i];
                    $newPackage['service_id'] = $data['service_id'];
                    $newPackage['description'] = $data['description'][$i];
                    $newPackage['price'] = $data['price'][$i] * 100;
                    $newPackage['revisions'] = $data['revisions'][$i];
                    $newPackage['delivery_time'] = $data['delivery_time'][$i];
                    $newPackage->save();
                }
            }
        }
        
        return redirect()->route('seller.services.create', ['step' => $step, 'post_id' => $post_id]);
    }

    public function review(Request $request)
    {
        $post_id = $request->service_id;

        $service = ServicePost::firstOrNew(['id' => $post_id]);
        $service['status'] = 1;
        $service->save();

        return redirect()->route('seller.services.list');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('seller.services.create', ['step' => 0, 'post_id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostStoreRequest $request, $id)
    {
        $slug_count = ServicePost::whereName($request->name)->count();
        $suffix = ($slug_count == 0) ? '' : '-' . (string) $slug_count + 1;

        $tags = (array) $request->input('tags');
        $categories = (array) $request->input('categories');

        $service = ServicePost::findOrFail($id);
        $data = $request->input();
        $data['user_id'] = Auth::id();

        $slug = $request->slug;

        if ($slug == '') {
            $slug = $request->name;
        }

        if (ServicePost::where('id', '!=', $id)->where('slug', $this->slugify($slug))->count()) {
            $data['slug'] = $this->slugify($slug) . "-1";
        } else {
            $data['slug'] = $this->slugify($slug);
        }

        $service->update($data);

        ServicePostTag::where('id_post', $service->id)->delete();
        ServicePostCategorie::where('id_post', $service->id)->delete();

        foreach ($tags as $tag) {
            $id_tag = (!is_numeric($tag)) ? $this->registerNewTag($tag) : $tag;
            ServicePostTag::create([
                'id_tag' => $id_tag,
                'id_post' => $service->id,
            ]);
        }

        foreach ($categories as $categorie) {
            ServicePostCategorie::create([
                'id_category' => $categorie,
                'id_post' => $service->id,
            ]);
        }
        return redirect()->route('seller.services.edit', $service->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // ServicePost::whereId($id)->delete();
        $service = ServicePost::find($id);
        if( $service ) {
            $service->status = ServicePost::$DELETED;
            $service->save();
        }
        return redirect()->route('seller.services.list');

    }

    public function recover($id)
    {
        ServicePost::withTrashed()->find($id)->restore();
        return redirect()->route('seller.services.trash');
    }

    public function get_billing($id, $custom=0)
    {
        if ($custom == 1) {
            $package = ServicePackagesCustom::with('service.uploads')->findOrFail($id);                

            if ($package->status != 0) {
                return back();
            }

            Session::put('iscustom', $custom);
            Session::put('custom_package_id', $id);
        } else {
            Session::forget('iscustom');
            Session::forget('custom_package_id');
            $package =ServicePackage::with('service.uploads')->findOrFail($id);
        }

        $isIncludeShipping = false;

        $countries = Country::all(['name', 'code']);
        if (auth()->user()) {
            $billing_address = auth()->user()->address_billing ? (UserAddress::find(auth()->user()->address_billing) ?? "NULL") : "NULL";
        } else {
            $billing_address = "NULL";
        }
        $user_ip = request()->ip();
        $location = geoip()->getLocation($user_ip);
        return view('service.checkout.billing')->with([
            'countries' => $countries,
            'package' => $package,
            'locale' => 'checkout',
            'isIncludeShipping' => $isIncludeShipping,
            'billing' => $billing_address,
            'location' => $location,
            'custom' => $custom,
        ]);
    }

    public function post_billing($id, Request $request)
    {
        $request->session()->put('billing_address1', $request->address1);
        $request->session()->put('billing_address2', $request->address2);
        $request->session()->put('billing_city', $request->city);
        $request->session()->put('billing_state', $request->state);
        $request->session()->put('billing_country', $request->country);
        $request->session()->put('billing_zipcode', $request->pin_code);
        $request->session()->put('billing_phonenumber', $request->phone);
        $request->session()->put('billing_firstname', $request->first_name);
        $request->session()->put('billing_lastname', $request->last_name);
        $request->session()->put('coupon_id', $request->coupon_id);
        $request->session()->put('package_id', $request->package_id);
        if (!auth()->user()) {
            $request->session()->put('billing_email', $request->email);
        }
        if ($request->isRemember && auth()->user()) {
            $userAddress = UserAddress::find(auth()->user()->address_billing);
            if ($userAddress) {
                $userAddress->first_name = $request->first_name;
                $userAddress->last_name = $request->last_name;
                $userAddress->address = $request->address1;
                $userAddress->address2 = $request->address2;
                $userAddress->city = $request->city;
                $userAddress->state = $request->state;
                $userAddress->country = $request->country;
                $userAddress->postal_code = $request->pin_code;
                $userAddress->phone = $request->phone;
                $userAddress->update();
                $user = User::find(auth()->id());
                $user->address_shipping = $userAddress->id;
                $user->save();
            } else {
                $userAddressInfo = UserAddress::create([
                    'user_id' => auth()->id(),
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'address' => $request->address1,
                    'address2' => $request->address2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'postal_code' => $request->pin_code,
                    'phone' => $request->phone,
                ]);

                $user = User::find(auth()->id());
                $user->address_billing = $userAddressInfo->id;
                $user->save();
            }
        }

        return redirect()->route('services.payment.get', ['id' => $id, 'custom' => $request->custom]);
    }

    public function get_payment($id, $custom, Request $request)   
    {
        if (Session::get('iscustom') == 1) {
            $custom_package_id = Session::get('custom_package_id');
            $package = ServicePackagesCustom::with('service.uploads')->findOrFail($custom_package_id);
        } else {
            $package = ServicePackage::with('service.uploads')->findOrFail($id);
        }

        $isIncludeShipping = false;

        $coupon_id = $request->session()->get('coupon_id', 0);
        $coupon_id = $coupon_id ?? 0;

        return view('service.checkout.payment')->with([
            'package' => $package,
            'locale' => 'checkout',
            'isIncludeShipping' => $isIncludeShipping,
            'coupon_id' => $coupon_id,
            'custom' => $custom,
        ]);
    }

    public function create_payment_intent($id, StorePaymentIntentRequest $req)
    {
        $custom = $req->custom;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        header('Content-Type: application/json');

        if (Session::get('iscustom') == 1) {
            $custom_package_id = Session::get('custom_package_id');
            $package = ServicePackagesCustom::with('service.uploads')->findOrFail($custom_package_id);
        } else {
            $package =ServicePackage::with('service.uploads')->findOrFail($id);
        }

        try {
            if (auth()->user()) {
                $orderId = auth()->id() . strtoupper(uniqid());
                $username = auth()->user()->first_name . " " . auth()->user()->last_name;
            } else {
                $orderId = '0' . strtoupper(uniqid());
                $username = $req->session()->get('billing_firstname') . " " . $req->session()->get('billing_lastname');
            }
            $req->session()->put('order_id', $orderId);

            $description = env('APP_NAME') . ' Order#S' . $orderId;

            ////////////////////////////////////////////////////////////////////////////////////////////////
            // Calculate the total and tax
            $coupon_code = $req->coupon_code;
            $arrCouponInfo = Coupon::getCouponByUser($coupon_code);
            $coupon = $arrCouponInfo['coupon'];

            $sub_total = $package->price;
            if ($coupon == null) {
                $shipping_option_id = $req->session()->get('shipping_option_id', 0);

                if ($shipping_option_id) {
                    $sub_total += ShippingOption::find($shipping_option_id)->price;
                }

                $taxPrice = 0;
                $serviceFee = 0;
                $serviceFee = $sub_total * (5.5 / 100);

                $total = $sub_total + floor($taxPrice + 0.5) + $serviceFee;
            } else {
                $discount = 0;
                $shipping_price = 0;

                if ($coupon->type == 0) {
                    $discount = $coupon->amount * 100;
                } else {
                    $discount = floor($sub_total * $coupon->amount / 100 + 0.5);
                }

                $shipping_option_id = $req->session()->get('shipping_option_id', 0);
                if ($shipping_option_id) {
                    $shipping_price = ShippingOption::find($shipping_option_id)->price;
                }

                $taxPrice = 0;
                $serviceFee = 0;
                if ($sub_total < $discount) {
                    $total = 0;
                } else {
                    $serviceFee = $sub_total * (5.5 / 100);
                    $taxPrice = $taxPrice * ($sub_total - $discount) / $sub_total;
                    $total = $sub_total - $discount + $shipping_price + floor($taxPrice + 0.5) + $serviceFee;
                }
            }

            // Create a PaymentIntent with amount and currency
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $total,
                'currency' => 'usd',
                'customer' => null,
                'description' => $description,
                'statement_descriptor' => substr($description, 0, 22),
                'shipping' => [
                    'address' => [
                        'city' => $req->session()->get('billing_city'),
                        'state' => $req->session()->get('billing_state'),
                        'country' => $req->session()->get('billing_country'),
                        'postal_code' => $req->session()->get('billing_zipcode'),
                        'line1' => $req->session()->get('billing_address1'),
                        'line2' => $req->session()->get('billing_address2'),
                    ],
                    'name' => $username,
                    'phone' => $req->session()->get('billing_phonenumber'),
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            return $output;
        } catch (Error $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function store_order($id, Request $request)
    {   
        $custom = $request->custom;

        $this->validate($request, (new PlaceOrderRequest)->rules());

        $order = new ServiceOrder();
        $total = 0;

        if (Session::get('iscustom') == 1) {
            $custom_package_id = Session::get('custom_package_id');
            $package = ServicePackagesCustom::with('service.uploads')->findOrFail($custom_package_id);
        } else {
            $package =ServicePackage::with('service.uploads')->findOrFail($id);
        }

        $order->user_id = auth()->id();

        $order->service_id = $package->service_id;
        $order->service_name = $package->service->name;
        $order->package_name = $package->name;
        $order->package_description = $package->description;
        $order->package_price = $package->price;
        $order->package_delivery_time = $package->delivery_time;
        $order->revisions = $package->revisions;
        $order->order_id = "S" . auth()->id() . strtoupper(uniqid());
        

        $order->payment_intent = '';
        $order->save();

        $request->session()->put('order_id', $order->id);

        return response(['ok' => true], 200);
    }

    public function finish(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $user_id = $user->id;
        $order_id = $request->session()->get('order_id');
        $order = ServiceOrder::with(['service.uploads'])->findOrFail($order_id);

        $package_id = $request->session()->get('package_id');

        // set delivery time
        if (Session::get('iscustom') == 1) {
            $custom_package_id = Session::get('custom_package_id');
            $package = ServicePackagesCustom::with('service.uploads')->findOrFail($custom_package_id);
            $package->status = 1; // accepted
            $package->save();

             // requirement not required
            if ($package->requirements_status != 0) {
                $order->original_delivery_time = Date('y-m-d H:i:s', strtotime('+' . $package->delivery_time . ' days'));
                $order->status = 1;
            }
            else
            {
                // counted and no requirements so set time
                if (ServiceRequirement::where('service_id', $package->service_id)->count() == 0) {
                    $order->original_delivery_time = Date('y-m-d H:i:s', strtotime('+' . $package->delivery_time . ' days'));
                    $order->status = 1;
                }
            }
        }
        else
        {
            // counted and no requirements so set time
            if (ServiceRequirement::where('service_id', $order->service_id)->count() == 0) {
                $order->original_delivery_time = Date('y-m-d H:i:s', strtotime('+' . $order->package_delivery_time . ' days'));
                $order->status = 1;
            }
        }

        $order->status_payment = 2; // paid
        $order->payment_intent = $request->get('payment_intent');
        $order->save();
        
        $amount = 0;
        $seller = $order->service->seller;
        if ($seller) {
            if ($seller->sales_commission_rate) {
                $amount = $order->package_price * $seller->sales_commission_rate / 100;
            } else {
                $amount = $order->package_price * Config::get('constants.default_sales_commission_rate') / 100;
            }
            SellersWalletHistory::create([
                'user_id' => $seller->user_id,
                'amount' => $amount,
                'order_id' => $order->id,
                'sale_type' => 1,
                'type' => 'add',
                'status' => 0,
            ]);

            $selleruser = User::find($seller->user_id);
            Mail::to($selleruser->email)->send(new ServiceOrderPlacedNotifySellerMail($order));

            /* Send notification to seller */
            Notification::create([
                'status' => 0,
                'user_id' => $seller->user_id,
                'thumb' => 0,
                'message' => 'You received a new order on your service ('. $order->service->name .').',
                'link' => '/seller/orders'
            ]);

            $seller->wallet = $seller->wallet + $amount;
            $seller->save();
        }


        Mail::to(auth()->user()->email)->send(new ServiceOrderPlacedMail($order));


        /* Add notification setting */
        if(Auth::check()){
            Notification::create([
                'status' => 0,
                'user_id' => Auth::id(),
                'thumb' => 0,
                'message' => 'You successfully ordered service('. $order->service->name .'). Please submit the requirements to continue.',
                'link' => '/services/order/'.$order->order_id
            ]);
        }

        Session::forget('iscustom');
        Session::forget('custom_package_id');
        return redirect()->route('services.order_detail', ['id' => $order->order_id]);
    }

    public function cancel(CancelCheckoutRequest $req)
    {
        $order_id = $req->session()->get('order_id');
        $error = $req->error;
        $order = ServiceOrder::findOrFail($order_id);

        $order->status_payment = 2;
        $order->payment_intent = $error['payment_intent']['id'];
        $order->status_payment_reason = $error['code'];
        $order->save();

        $req->session()->forget('order_id');

        return response(null, 204);
    }

    public function answer(Request $request)
    {
        $order_id = $request->order_id;
        $answers = $request->answer;
        $order = ServiceOrder::with('service.requirements')->findOrFail($order_id);
        $author = User::findOrFail($order->service->user_id);

        OrderServiceRequirement::where('order_id', $order_id)->delete();
        for ($i = 0; $i < count($answers); $i++) {
            if ($answers[$i] == '') {
                continue;
            }

            $answer = new OrderServiceRequirement();
            $answer->order_id = $order_id;
            $answer->requirement_id = $order->service->requirements[$i]->id;
            $answer->answer = $answers[$i];

            $answer->save();
        }

        $order->status = 1;
        $order->original_delivery_time = Date('y-m-d H:i:s', strtotime('+' . $order->package_delivery_time . ' days'));
        $order->update();

        Notification::create([
            'status' => 0,
            'user_id' => $order->service->user_id,
            'thumb' => 0,
            'message' => $order->user->full_name . ' has submitted the requirements for your service order '. $order->order_id . '.',
            'link' => '/seller/order_detail/' . $order->order_id
        ]);

        $serviceuser = User::find($order->service->user_id);
        $subject = 'Requirements submitted for your Jewelry CG order';
        $message = $order->user->full_name . ' has submitted the requirements for your service order '. $order->order_id . '.'; 
        $link = '/seller/order_detail/' . $order->order_id; 
        Mail::to($serviceuser->email)->send(new NotificationMail($order->service->user_id, $subject, $message, $link));

        return redirect()->back()->with("message", "We have sent your message to " . $author->first_name . " " . $author->last_name);
    }

    public function orders(Request $request)
    {
        $user_id = Auth::id();
        $orders = ServiceOrder::where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate(10);

        return view('service.orders', ['orders' => $orders]);
    }

    public function order_detail($id, Request $request)
    {
        $order = ServiceOrder::with(['service.uploads', 'service.postauthor', 'review'])->where('order_id', $id)->firstOrFail();
        $requirements = ServiceRequirement::with('choices')->where('service_id', $order->service_id)->get();
        $answers = OrderServiceRequirement::with('requirement')->where('order_id', $order->id)->get();

        $answers->each(function ($answer) {
            if ($answer->requirement->type == 1) {
                $attach_ids = explode(',', $answer->answer);
                $attaches = [];

                for ($i = 0; $i < count($attach_ids); $i++) {
                    $upload = Upload::find($attach_ids[$i]);
                    if ($upload) {
                        array_push($attaches, $upload);
                    }
                }

                $answer->attaches = $attaches;
            } else if ($answer->requirement->type == 3) {
                $answer->answers = explode(',', $answer->answer);
            }
        });

        $deliveries = OrderServiceDelivery::with('revision')->where('order_id', $order->id)->get();

        $deliveries->each(function ($delivery) {
            $attach_ids = explode(',', $delivery->attachment);
            $attaches = [];

            for ($i = 0; $i < count($attach_ids); $i++) {
                $upload = Upload::find($attach_ids[$i]);
                if ($upload) {
                    array_push($attaches, $upload);
                }
            }

            $delivery->attaches = $attaches;
        });

        $seller = User::with('uploads')->findOrFail($order->service->user_id);

        // Fetch all revision requests using the order_id
        $extend_requests = OrderServiceExtendDeliveryTime::where('order_id', $order->order_id)->get();

        return view('service.order_detail', [
            'order' => $order,
            'requirements' => $requirements,
            'answers' => $answers,
            'deliveries' => $deliveries,
            'seller' => $seller,
            'extend_requests' => $extend_requests,
        ]);
    }

    public function dashboard()
    {
        $pendingBalances = SellersWalletHistory::where('type', 'add')->where('status', 0)->get();
        foreach ($pendingBalances as $pending) {
            if (Carbon::now()->diffInDays($pending->updated_at->startOfDay()) >= 14) {
                $wallet = SellersProfile::where('user_id', $pending->user_id)->first();
                if ($wallet) {
                    // $wallet->wallet += $pending->amount;
                    // $wallet->save();
                    $pending->status = 1;
                    $pending->save();
                }
            }
        }
        $services = ServicePost::where('user_id', auth()->id())->get();
        $seller = SellersProfile::where('user_id', auth()->id())->first();
        $pendingBalance = SellersWalletHistory::where('user_id', auth()->id())->whereIn('status', [0, 2])->select('amount')->get()->sum('amount');
        $totalEarned = SellersWalletHistory::where('user_id', auth()->id())->select('amount')->get()->sum('amount');
        return view('service.dashboard')->with([
            'services' => $services,
            'seller' => $seller,
            'pendingBalance' => $pendingBalance,
            'totalEarned' => $totalEarned,
        ]);
    }

    public function order_complete(Request $request)
    {
        $sellersWalletHistoryStatusComplete = 1;
        $order_id = $request->order_id;

        $order = ServiceOrder::with('service')->where('id', $order_id)->where('user_id', Auth::id())->firstOrFail();
        $order->status = 5;
        $order->update();

        $history = SellersWalletHistory::where('order_id', $order->id)->where('sale_type', 1)->firstOrFail();
        $history->status = $sellersWalletHistoryStatusComplete;
        $history->save();

        $seller = User::findOrFail($order->service->user_id);

        return redirect()->route('services.review', $order->order_id);
    }

    public function order_revision(Request $request)
    {
        $order_id = $request->order_id;
        $delivery_id = $request->delivery_id;

        $order = ServiceOrder::with('service')->where('id', $order_id)->where('user_id', Auth::id())->firstOrFail();
        //$order->original_delivery_time = Carbon::parse($order->original_delivery_time)->addDays($order->package_delivery_time)->format('Y-m-d H:i:s');
        $order->original_delivery_time = Carbon::now()->addDays($order->package_delivery_time)->format('Y-m-d H:i:s');
        $order->status = 2;
        $order->revisions = $order->revisions - 1;
        $order->update();

        $seller = User::findOrFail($order->service->user_id);

        $revision = new OrderServiceRevisionRequest();
        $revision->order_id = $order_id;
        $revision->user_id = Auth::id();
        $revision->delivery_id = $delivery_id;
        $revision->message = $request->message ? $request->message : "";
        $revision->save();

        /* send notification to the seller */
        Notification::create([
            'status' => 0,
            'user_id' => $order->service->postauthor->id,
            'thumb' => 0,
            'message' => $order->user->full_name . 'has requested a revision on service order #'. $order->order_id .'. View details.',
            'link' => '/seller/order_detail/' . $order->order_id
        ]);

        $serviceuser = User::find($order->service->postauthor->id);
        $subject = 'Revision requested Jewelry CG service order #'. $order->order_id;
        $message = $order->user->full_name . ' has requested a revision on service order #'. $order->order_id; 
        $link = '/seller/order_detail/' . $order->order_id; 
        Mail::to($serviceuser->email)->send(new NotificationMail($order->service->postauthor->id, $subject, $message, $link));

        return redirect()->back()->with("success", "Revision successfully requested.");
    }

    public function service_review_get($id)
    {
        $order = ServiceOrder::with(['review', 'service'])
            ->leftJoin('order_service_deliveries', 'orders_services.id', 'order_service_deliveries.order_id')
            ->leftJoin('users', 'orders_services.user_id', 'users.id')
            ->where('orders_services.order_id', $id)
            ->select('orders_services.*', 'order_service_deliveries.attachment_featured', 'users.role')
            ->firstOrFail();

        if ($order->user_id == Auth::id() && count($order->review)) {
            return redirect()->back()->with('error', 'You already left review!');
        } else if ($order->service->user_id == Auth::id() && count($order->review) != 1) {
            return redirect()->back()->with('error', "You can't leave left now");
        } else if ($order->user_id != Auth::id() && $order->service->user_id != Auth::id()) {
            return redirect()->back()->with('error', "You can't access to this page");
        }

        return view('service.review', compact('order'));
    }

    public function service_review_post(Request $request)
    {
        $order_id = $request->order_id;
        $rating = $request->rating;
        $review_attachement_id = $request->review_attachement_id;
        $review_attachement = 0;
        if ($review_attachement_id) $review_attachement = 1;

        $order = ServiceOrder::findOrFail($order_id);

        $service = ServicePost::where('id', $order->service_id)->firstOrFail();

        if ($order->user_id == Auth::id() && count($order->review)) {
            return redirect()->back()->with('error', 'You already left review!');
        } else if ($order->service->user_id == Auth::id() && count($order->review) != 1) {
            return redirect()->back()->with('error', "You can't leave left now");
        } else if ($order->user_id != Auth::id() && $order->service->user_id != Auth::id()) {
            return redirect()->back()->with('error', "You can't access to this page");
        }

        if (empty($request->review)) {
            return redirect()->back()->with('error', "Please provide a review with some content. Your review cannot be empty.");
        }

        if (empty($request->rating)) {
            return redirect()->back()->with('error', "Please make a selection for the review. Choose a rating between 1 and 5 stars to reflect the service received.");
        }

        
        if ($order->user_id == Auth::id()) {

            // insert buyer review
            ServiceReview::create([
                'order_id' => $order_id, 
                'rating' => $rating, 
                'review' => $request->review, 
                'user_id' => $order->user_id, 
                'service_id' => $order->service_id,
                'review_attachment' => $review_attachement,
                'review_attachment_id' => $review_attachement_id,
            ]);

            $notification = new Notification();
            $notification->message = Auth::user()->full_name . ' has left you a review on service order #'. $order->order_id . '.';
            $notification->user_id = $order->service->user_id;
            $notification->link = "/seller/order_detail/" . $order->order_id;
            $notification->save();

            return redirect()->route('services.order_detail', $order->order_id)->with('success', 'You successfully left a review.');
        } else {

            // insert seller review
            ServiceReview::create([
                'order_id' => $order_id, 
                'rating' => $rating, 
                'review' => $request->review, 
                'user_id' => $service->user_id, 
                'service_id' => $order->service_id,
            ]);

            $notification = new Notification();
            $notification->message = $order->service->postauthor->full_name . ' has left you a review on service order #' . $order->order_id . '.';
            $notification->user_id = $order->user_id;
            $notification->link = "/services/order/" . $order->order_id;
            $notification->save();

            return redirect()->route('seller.service.order.detail', $order->order_id)->with('success', 'You successfully left a review.');
        }
    }

    public function message(Request $request) {
        $user_id = Auth::user()->id;
        $seller = $request->seller;
        $content = $request->content;

        $message = new Message;
        $message->user_id = $user_id;
        $message->conversation_id = $seller;
        $message->message = $content;
        
        $message->save();

        return response()->json(true);
    }

    public function message_check_existed(Request $request) {
        $user_id = Auth::user()->id;
        $seller = $request->seller;

        $content = Message::where(['user_id' =>$user_id,'conversation_id' => $seller])->count();

        return response()->json($content);
    }

    public function create_custom_package(Request $request) {

        if ($request->price < 1) {
            return;
        }

        $service = ServicePost::where('id', $request->serviceId)->firstOrFail();
        $seller = User::with('uploads')->findOrFail($service->user_id);

        ServicePackagesCustom::create([
            'status' => $request->status,
            'service_id' => $request->serviceId,
            'user_id' => $request->userId,
            'name' => "Custom Offer",
            'description' => $request->description,
            'price' => $request->price * 100,
            'revisions' => $request->revisions,
            'delivery_time' => $request->deliveryTime,
            'expiration_time' => $request->expirationTime,
            'requirements_status' => $request->requirements_status
        ]);

        /* Send notification to buyer */
       Notification::create([
            'status' => 0,
            'user_id' => $request->userId,
            'thumb' => 0,
            'message' => "{$seller->first_name} {$seller->last_name} just sent you a Custom Offer.",
            'link' => "/chat/{$seller->username}"
        ]);

        $buyeruser = User::find($request->userId);
        $subject = "You just received a custom offer on Jewelry CG";
        $message = "{$seller->first_name} {$seller->last_name} just sent you a Custom Offer."; 
        $link = "/chat/{$seller->username}";
        Mail::to($buyeruser->email)->send(new NotificationMail($request->userId, $subject, $message, $link));

        $customPackages = ServicePackagesCustom::where(['user_id' => $request->userId, 'service_id' => $request->serviceId])->get();
        $currentCustomPackageId = $customPackages[sizeof($customPackages) - 1]->id;

        return response()->json($currentCustomPackageId);
        
    }

    public function update_custom_package(Request $request) {
        $custom = ServicePackagesCustom::find($request->id);
        if ($custom->status == 0) {
            $custom->status = $request->status;
            $custom->update();

            return response()->json(true);
        }

        return response()->json(false);
    }
}
