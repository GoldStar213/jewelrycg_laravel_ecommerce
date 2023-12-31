<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;

use App\Http\Requests\DeliverRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Models\Attribute;
use App\Models\OrderServiceDelivery;
use App\Models\OrderServiceRequirement;
use App\Models\Product;
use App\Models\ProductsCategorie;
use App\Models\ProductsTaxOption;
use App\Models\ProductsVariant;
use App\Models\SellerEditProductVariants;
use App\Models\SellerEditProducts;
use App\Models\ProductTag;
use App\Models\ProductTagsRelationship;
use App\Models\SellerPaymentMethod;
use App\Models\SellerPaymentDetail;
use App\Models\SellersProfile;
use App\Models\SellersWalletHistory;
use App\Models\SellerWalletWithdrawal;
use App\Models\ServiceOrder;
use App\Models\ServicePost;
use App\Models\ServiceTags;
use App\Models\Upload;
use App\Models\User;
use App\Models\Notification;
use App\Mail\NotificationMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{

    public function index()
    {
        return view('seller.index');
    }

    public function dashboard()
    {
        $products = Product::where('vendor', auth()->id())->get();
        $seller = SellersProfile::where('user_id', auth()->id())->firstOrFail();
        $withdrawable = SellersWalletHistory::where('user_id', auth()->id())
            ->where('type', 'add')
            ->where('status', 1)
            ->whereDate('updated_at', '<', date('Y-m-d', strtotime(Carbon::today()->toDateString() . " -14 days")))
            ->select('amount')
            ->get()
            ->sum('amount') - SellersWalletHistory::where('user_id', auth()->id())
            ->where('type', 'withdraw')
            ->select('amount')
            ->get()
            ->sum('amount');
        $totalEarned = SellersWalletHistory::where('user_id', auth()->id())->where('type', 'add')
            ->whereNotIn('status', [2,3])
            ->select('amount')->get()->sum('amount');

        return view('seller.dashboard')->with([
            'products' => $products,
            'seller' => $seller,
            'withdrawable' => $withdrawable,
            'totalEarned' => $totalEarned,
        ]);
    }
    /**
     * Show seller'sproduct create view
     */
    public function createProduct()
    {
        return view('seller.products.create', [
            'attributes' => Attribute::orderBy('id', 'DESC')->get(),
            'categories' => ProductsCategorie::all(),
            'tags' => ProductTag::all(),
            'taxes' => ProductsTaxOption::all(),
        ]);
    }

    public function editProduct($id) {
        $product = Product::whereId($id)->with(['tags', 'variants', 'variants.uploads'])->firstOrFail();
        $product->setPriceToFloat();
        $variants = ProductsVariant::where('product_id', $id)->get();

        $variants->each(function ($product) {
            $product->setPriceToFloat();
        });
        $selected_attributes = explode(',', $product->product_attributes);
        $prepare_values = Attribute::whereIn('id', $selected_attributes)->with(['values'])->get();
        return view('seller.products.edit', [
            'product' => $product,
            'variants' => $variants,
            'attributes' => Attribute::orderBy('id', 'DESC')->get(),
            'categories' => ProductsCategorie::all(),
            'taxes' => ProductsTaxOption::all(),
            'tags' => ProductTag::all(),
            'uploads' => Upload::whereIn('id', explode(',', $product->product_images))->get(),
            'selected_values' => $prepare_values,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeProduct(ProductStoreRequest $req)
    {
        $tags = (array) $req->input('tags');
        $variants = (array) $req->input('variant');
        $attributes = implode(",", (array) $req->input('attributes'));
        $values = implode(",", (array) $req->input('values'));
        $data = $req->all();
        $data['vendor'] = auth()->id();
        $data['price'] = Product::stringPriceToCents($req->price);
        $data['is_digital'] = 1;
        $data['status'] = 2;
        $data['is_virtual'] = 0;
        $data['is_backorder'] = 0;
        $data['is_madetoorder'] = 0;
        $data['is_trackingquantity'] = 0;
        $data['product_attributes'] = $attributes;
        $data['product_attribute_values'] = $values;
        $data['slug'] = str_replace('#', '', str_replace(" ", "-", strtolower($req->name)));
        $slug_count = Product::where('slug', $data['slug'])->count();
        if ($slug_count) {
            $data['slug'] = $data['slug'] . '-' . ($slug_count + 1);
        }
        $product = Product::create($data);
        $id_product = $product->id;

        foreach ($variants as $variant) {
            $variant_data = $variant;
            $variant_data['product_id'] = $id_product;
            $variant_data['variant_price'] = Product::stringPriceToCents($variant_data['variant_price']);

            ProductsVariant::create($variant_data);
        }

        foreach ($tags as $tag) {
            $id_tag = (!is_numeric($tag)) ? $this->registerNewTag($tag) : $tag;
            ProductTagsRelationship::create([
                'id_tag' => $id_tag,
                'id_product' => $id_product,
            ]);
        }

        return redirect()->route('seller.dashboard');
    }


    public function updateProduct(ProductStoreRequest $req, $product)
    {
        $tags = (array) $req->input('tags');
        $variants = (array) $req->input('variant');
        $attributes = implode(",", (array) $req->input('attributes'));
        $values = implode(",", (array) $req->input('values'));
        $product_images = $req->input('product_images');
        $product_3dpreview = $req->input('product_3dpreview');
        $data = $req->all();
        $data['vendor'] = auth()->id();
        $data['price'] = Product::stringPriceToCents($req->price);
        $data['is_digital'] = 1;
        $data['status'] = 2;
        $data['is_virtual'] = 0;
        $data['is_backorder'] = 0;
        $data['is_madetoorder'] = 0;
        $data['is_trackingquantity'] = 0;
        $data['product_attributes'] = $attributes;
        $data['product_attribute_values'] = $values;
        $data['product_images'] = $product_images;
        $data['product_3dpreview'] = $product_3dpreview;
        $data['slug'] = str_replace('#', '', str_replace(" ", "-", strtolower($req->name)));
        $slug_count = Product::where('slug', $data['slug'])->count();
        if ($slug_count) {
            $data['slug'] = $data['slug'] . '-' . ($slug_count + 1);
        }
        $data['product_id'] = (int)$product;
        $data['is_approved'] = 0;
        $edit_product = SellerEditProducts::where('product_id', $product)->first();
        if (!$edit_product) {
            $edit_product = SellerEditProducts::create($data);
        } else {
            $edit_product->update($data);
        }
        $id_product = $edit_product->product_id;

        $variantIds = [];
        foreach ($variants as $variant) {
            $variantIds[] = $variant['id'];
        }
        SellerEditProductVariants::where('product_id', $id_product)->whereNotIn('id', $variantIds)->delete();

        foreach ($variants as $variant) {
            $variant_data = $variant;
            $variant_data['variant_id'] = (int)$variant['id'];
            $variant_data['product_id'] = $id_product;
            $variant_data['variant_price'] = SellerEditProductVariants::stringPriceToCents($variant_data['variant_price']);
            SellerEditProductVariants::updateOrCreate(['product_id' => $id_product, 'variant_attribute_value' => $variant['variant_attribute_value']], $variant_data);
        }
        return redirect()->route('seller.dashboard');
    }

    /**
     * Transaction History
     */
    public function transactionHistory()
    {
        $transactions = SellersWalletHistory::where('user_id', auth()->id())->orderBy('created_at', 'DESC')->get();
        return view('seller.history', ['transactions' => $transactions]);
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

    public function service_orders(Request $request)
    {
        $tab = $request->input("tab");
        if (!$tab) {
            $tab = "active";
        }

        $query = ServiceOrder::whereHas('service',
            fn($query) => $query->where('user_id', Auth::id())
        )->with(['user', 'service']);

        $current = Carbon::now();
        switch ($tab) {
            case "active":
                $query->where('status', '<', 3);
                break;
            case "late":
                $query->whereDate('original_delivery_time', '<', $current)->where('status', '<', 3);
                break;
            case "delivered":
                $query->where('status', 4);
                break;
            case "completed":
                $query->where('status', 5);
                break;
            case "canceled":
                $query->where('status', 3);
                break;
            default:
                break;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return view('seller.services.orders.index', ['orders' => $orders, 'tab' => $tab]);
    }

    public function service_order_detail($id)
    {
        $order = ServiceOrder::where('order_id', $id)->whereHas('service',
            fn($query) => $query->where('user_id', Auth::id())
        )->with(['user', 'review'])->firstOrFail();

        $answers = OrderServiceRequirement::with('requirement')->where('order_id', $order->id)->get();

        $answers->each(function ($answer) {
            if ($answer->requirement->type == 1) {
                $attach_ids = explode(',', $answer->answer);
                $attaches = [];

                for ($i = 0; $i < count($attach_ids); $i++) {
                    $upload = Upload::findOrFail($attach_ids[$i]);
                    array_push($attaches, $upload);
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
                $upload = Upload::findOrFail($attach_ids[$i]);
                array_push($attaches, $upload);
            }

            $delivery->attaches = $attaches;
        });

        $buyer = User::with('uploads')->findOrFail($order->user_id);

        return view('seller.services.orders.detail', [
            'order' => $order,
            'answers' => $answers,
            'deliveries' => $deliveries,
            'buyer' => $buyer,
        ]);
    }

    public function service_order_deliver(DeliverRequest $request)
    {
        $order_id = $request->order_id;
        $message = $request->message;
        $attach = $request->attach;
        $attach_image = $request->attach_image;

        $order = ServiceOrder::findOrFail($order_id);
        $order->status = 4;
        $order->save();

        $delivery = new OrderServiceDelivery();
        $delivery->order_id = $order_id;
        $delivery->message = $message;
        $delivery->attachment = $attach;
        $delivery->attachment_featured = $attach_image;
        $delivery->save();

        /* send notfiication to seller */
        Notification::create([
            'status' => 0,
            'user_id' => $order->user->id,
            'thumb' => 0,
            'message' => $order->service->postauthor->full_name .' just delivered your service order #'. $order->order_id .'. View delivery.',
            'link' => '/services/order/' . $order->order_id
        ]);

        $buyeruser = User::find($order->user->id);
        $subject = $order->service->postauthor->full_name .' just delivered your service order #'. $order->order_id;
        $message = $order->service->postauthor->full_name .' just delivered your service order #'. $order->order_id .'. Please note your order will be automatically marked as complete by Jewelry CG after 3 days, so make sure to review it.'; 
        $link = '/services/order/' . $order->order_id; 
        Mail::to($buyeruser->email)->send(new NotificationMail($order->user->id, $subject, $message, $link));


        return redirect()->back()->with("success", "You have successfuly delivered the service!");
    }

    public function withdraw()
    {
        $seller = SellersProfile::where('user_id', auth()->id())->firstOrFail();
        $withdrawable = SellersWalletHistory::where('user_id', auth()->id())
            ->where('type', 'add')
            ->where('status', 1)
            ->whereDate('updated_at', '<', date('Y-m-d', strtotime(Carbon::today()->toDateString() . " -14 days")))
            ->select('amount')
            ->get()
            ->sum('amount') - SellersWalletHistory::where('user_id', auth()->id())
            ->where('type', 'withdraw')
            ->select('amount')
            ->get()
            ->sum('amount');
        $totalEarned = SellersWalletHistory::where('user_id', auth()->id())->where('type', 'add')->select('amount')->get()->sum('amount');
        
        $payment_details = SellerPaymentDetail::where('user_id', auth()->id())->first();
        if ($payment_details) {
            $payment_methods = SellerPaymentMethod::where('id', $payment_details->payment_method_id)->get();
        }
        else
        {
            $payment_details = null;
            $payment_methods = null;
        }

        return view('seller.withdraw', compact('seller', 'withdrawable', 'totalEarned', 'payment_methods', 'payment_details'));
    }

    public function withdraw_post(Request $request)
    {
        $amount = $request->input('amount') * 100;

        $seller_profile = SellersProfile::where('user_id', Auth::id())->firstOrFail();
        $withdrawable = SellersWalletHistory::where('user_id', Auth::id())
            ->where('type', 'add')
            ->where('status', 1)
            ->whereDate('updated_at', '<', date('Y-m-d', strtotime(Carbon::today()->toDateString() . " -14 days")))
            ->select('amount')
            ->get()
            ->sum('amount') - SellersWalletHistory::where('user_id', Auth::id())
            ->where('type', 'withdraw')
            ->select('amount')
            ->get()
            ->sum('amount');

        /*
        if ($seller_profile->wallet < $amount || $amount <= 0 || $amount > $withdrawable) {
            return redirect()->back()->with('error', 'Invalid withdrawal amount.');
        }
        */
        $errorMsg = '';
        if ($seller_profile->wallet < $amount) {
            $errorMsg = 'Insufficient funds in your wallet. Please check your balance and try again.';
        } elseif ($amount <= 0) {
            $errorMsg = 'Invalid withdrawal amount. The amount entered must be greater than zero.';
        } elseif ($amount > $withdrawable) {
            $errorMsg = 'The amount you entered to withdraw exceeds your amount available to withdraw. Please enter a lower amount.';
        }

        if (!empty($errorMsg)) {
            return redirect()->back()->with('error', $errorMsg);
        }

        // update seller wallet
        $seller_profile->wallet = $seller_profile->wallet - $amount;
        $seller_profile->save();

        // insert record of withdrawal
        $withdraw_history = new SellerWalletWithdrawal();
        $withdraw_history->user_id = Auth::id();
        $withdraw_history->amount = $amount;
        $withdraw_history->payment_method_name = $request->input('method');
        $withdraw_history->q1 = $request->input('question_1');
        $withdraw_history->q2 = $request->input('question_2');
        $withdraw_history->q3 = $request->input('question_3');
        $withdraw_history->q4 = $request->input('question_4');
        $withdraw_history->save();

        // insert history record
        $wallet_history = new SellersWalletHistory();
        $wallet_history->user_id = Auth::id();
        $wallet_history->amount = $amount;
        $wallet_history->type = "withdraw";
        $wallet_history->save();

        return redirect()->back()->with('success', 'Withdrawal is in progress');
    }


    public function withdraw_history()
    {
        $histories = SellerWalletWithdrawal::with('method')->where('user_id', Auth::id())->get();

        return view('seller.withdraw_history', compact('histories'));
    }

    public function seller_profile($username)
    {
        $seller = SellersProfile::withWhereHas('user', fn($query) => $query->where('username', $username))->with('user.uploads')->firstOrFail();


        if (Auth::user()->isBlocked($seller->user->id) == true) {
            return redirect()->route('services.all');
        }

        $products = Product::with(['uploads', 'product_category'])->where('vendor', $seller->user_id)->paginate(6, '*', 'product');
        $services = ServicePost::with(['uploads', 'categories.category'])->where('user_id', $seller->user_id)->paginate(6, '*', 'service');

       
        $rating = SellersProfile::withWhereHas('user', fn($query) => $query->where('username', $username))
        ->leftJoin('services', 'services.user_id', 'sellers_profile.user_id')
        ->leftJoin('orders_services', 'services.id', 'orders_services.service_id')
        ->leftJoin('service_reviews', function ($join) use ($seller) {
            $join->on('service_reviews.order_id', 'orders_services.id')
                ->where('service_reviews.user_id', '!=', $seller->user_id);
        })
        ->select(DB::raw('FORMAT(AVG(service_reviews.rating), 1) rating, COUNT(service_reviews.id) count'))
        ->firstOrFail();
      
        return view('seller_profile', compact('seller', 'products', 'services', 'rating'));
    }

    public function profile()
    {
        $seller = SellersProfile::withWhereHas('user', fn($query) => $query->where('id', Auth::id()))->with('user.uploads')->firstOrFail();
        $payment_methods = SellerPaymentMethod::all();

        return view('seller.profile', compact('seller', 'payment_methods'));
    }

    public function save_profile(Request $request)
    {
        $seller = SellersProfile::withWhereHas('user', fn($query) => $query->where('id', Auth::id()))->firstOrFail();

        $seller->slogan = $request->slogan;
        $seller->whatsapp = $request->whatsapp;
        $seller->business_name = $request->business_name;
        $seller->about = $request->about;
        $seller->default_payment_method = $request->method;
        $seller->save();

        if ($request->avatar) {
            $user = $seller->user;
            $user->avatar = $request->avatar;
            $user->save();
        }

        return redirect()->back()->with("success", "Seller profile successfully updated.");
    }

    public function payment_details()
    {
        $payment_methods = SellerPaymentMethod::all();
        $user_id = Auth::user()->id;
        $payment_details = SellerPaymentDetail::where('user_id', $user_id)->first();

        return view('seller.payment_details', compact('payment_methods', 'payment_details'));
    }


    public function save_payment_details(Request $request)
    {
        $user_id = Auth::user()->id;
        $payment_method_id = $request->payment_method_id;

        // Check if the user already has a payment detail record
        $p_detail = SellerPaymentDetail::where('user_id', $user_id)->first();

        if (!$p_detail) {
            // If the record doesn't exist, create a new one
            $p_detail = new SellerPaymentDetail();
            $p_detail->user_id = $user_id;
        }

        $p_detail->payment_method_id = $payment_method_id;

        // Retrieve the payment method
        $payment_method = SellerPaymentMethod::find($payment_method_id);

        // Set the values for the required question fields
        foreach ($payment_method->getAttributes() as $key => $value) {
            if (strpos($key, 'question_') === 0 && $request->has($key)) {
                $questionField = $key;
                $p_detail->$questionField = $request->$key;
            } elseif ($key !== 'id' && $key !== 'name') {
                // Clear the question field if not needed
                $questionField = $key;
                $p_detail->$questionField = null;
            }
        }

        $p_detail->save();

        return redirect()->back()->with("success", "Seller Payment Details successfully saved.");
    }


}
