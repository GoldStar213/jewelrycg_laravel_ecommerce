<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;

use Auth;
use App\Models\User;
use App\Models\Notification;
use App\Mail\NotificationMail;
use App\Models\ServicePost;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderServiceExtendDeliveryTime;

class ExtendServiceDeliveryController extends Controller
{
    public function extendDelivery(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'order_id' => 'required',
            'buyer_id' => 'required',
            'days' => 'required|integer',
            'message' => 'nullable',
        ]);

        $seller_id = Auth::id();
        $order = ServiceOrder::where('order_id', $validatedData['order_id'])->firstOrFail();

        // Check if a record already exists with status 0 or 1
        $existingRecord = OrderServiceExtendDeliveryTime::where('order_id', $validatedData['order_id'])
            ->whereIn('status', [0])
            ->first();

        if ($existingRecord) {
            return response()->json(['message' => 'Extend delivery request already exists.']);
        }

         // Add the seller_id to the validated data
        $validatedData['seller_id'] = $seller_id;

        // Create a new record in the database
        $extendDelivery = OrderServiceExtendDeliveryTime::create($validatedData);

        // Perform any other necessary actions

        /* Send notification to buyer */
        Notification::create([
            'status' => 0,
            'user_id' => $order->user_id,
            'thumb' => 0,
            'message' => 'Delivery time extension request for your service order #'. $order->order_id .'.',
            'link' => '/services/order/' . $order->order_id
        ]);

        $buyeruser = User::find($order->user_id);
        $sellerUser = User::find($seller_id);
        $subject = 'Delivery time extension request for your Jewelry CG order #'. $order->order_id . '';
        $message = $sellerUser->username . ' has requested to extend delivery time for your service order #'. $order->order_id . '.'; 
        $link = '/services/order/' . $order->order_id; 
        Mail::to($buyeruser->email)->send(new NotificationMail($order->user_id, $subject, $message, $link));

        return response()->json(['message' => 'Extend delivery request submitted successfully.']);
    }
    
    public function answerRequest(Request $request)
    {
        $validatedData = $request->validate([
            'extend_delivery_id' => 'required|integer',
            'action' => 'required|in:approve,decline',
        ]);

        $buyer_id = Auth::id();

        $extendDelivery = OrderServiceExtendDeliveryTime::find($validatedData['extend_delivery_id']);
        $order = ServiceOrder::where('order_id', $extendDelivery->order_id)->firstOrFail();
        $buyeruser = User::find($extendDelivery->buyer_id);
        $sellerUser = User::find($extendDelivery->seller_id);

        if (!$extendDelivery) {
            return response()->json(['message' => 'Extend delivery request not found.']);
        }

        if ($extendDelivery->buyer_id != $buyer_id) {
            return response()->json(['message' => 'You are not authorized to perform this action.']);
        }

        if ($validatedData['action'] === 'approve') {

            $extendDelivery->status = 1;
            $extendDelivery->save();

            $order->original_delivery_time = Date('y-m-d H:i:s', strtotime('+' . $extendDelivery->days . ' days'));
            $order->save();

            /* Send notification to seller */
            Notification::create([
                'status' => 0,
                'user_id' => $extendDelivery->seller_id,
                'thumb' => 0,
                'message' => $buyeruser->username . ' has accepted to extend delivery time for your service order #'. $order->order_id .'.',
                'link' => '/services/order/' . $order->order_id
            ]);

            $subject = 'Delivery time extension request accepted '. $order->order_id . '';
            $message = $buyeruser->username . ' has accepted to extend delivery time for your service order #'. $order->order_id . '.'; 
            $link = '/seller/order_detail/' . $order->order_id; 
            Mail::to($sellerUser->email)->send(new NotificationMail($sellerUser->id, $subject, $message, $link));

            return response()->json(['message' => 'Extend delivery request approved successfully.']);

        } elseif ($validatedData['action'] === 'decline') {

            $extendDelivery->status = 2;
            $extendDelivery->save();
            
            /* Send notification to seller */
            Notification::create([
                'status' => 0,
                'user_id' => $extendDelivery->seller_id,
                'thumb' => 0,
                'message' => $buyeruser->username . ' has declined to extend delivery time for your service order #'. $order->order_id .'.',
                'link' => '/services/order/' . $order->order_id
            ]);

            $subject = 'Delivery time extension request accepted '. $order->order_id . '';
            $message = $buyeruser->username . ' has declined to extend delivery time for your service order #'. $order->order_id . '.'; 
            $link = '/seller/order_detail/' . $order->order_id; 
            Mail::to($sellerUser->email)->send(new NotificationMail($sellerUser->id, $subject, $message, $link));

            return response()->json(['message' => 'Extend delivery request declined successfully.']);
        }

        return response()->json(['message' => 'Invalid action provided.']);
    }

}
