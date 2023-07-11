<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellersWalletHistory;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mail;
use App\Models\Notification;

class OrderController extends Controller
{

    public function index()
    {
        $orders = Order::getBasedOnUser();
        $orders->transform(fn($i) => $i->formatPrice());
        return view('backend.orders.list', compact('orders'));
    }

    public function pending()
    {
        $orders = Order::select('orders.*')
            ->join('order_items', 'orders.order_id', '=', 'order_items.order_id')
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->where('orders.user_id', '<>', 0);
            })
            ->where('order_items.status_fulfillment', '=', 1)
            ->where('orders.status_payment', '=', 2)
            ->groupBy('orders.order_id')
            ->paginate(10);

        $orders->transform(function ($item) {
            $item->price = number_format($item->price, 2); // Format the total price
            return $item;
        });

        return view('backend.orders.pending', compact('orders'));
    }


    public function pending_badge()
    {
        $count = $orders = Order::select('orders.*')
        ->join('order_items', 'orders.order_id', '=', 'order_items.order_id')
        ->leftJoin('users', function ($join) {
            $join->on('users.id', '=', 'orders.user_id')
                 ->where('orders.user_id', '<>', 0);
        })
        ->where('order_items.status_fulfillment', '=', 1)
        ->where('orders.status_payment', '=', 2)
        ->groupBy('orders.order_id')
            ->get()
            ->toArray();

        return count($count);
    }

    // Save tracking number if set
    public function status_tracking_set(Request $request, $id)
    {
        $orderItem = OrderItem::findOrFail($id);
        $orderId = $orderItem->order_id;


        $orderItem->status_tracking = $request->status;
        $orderItem->save();

        return $orderItem;
    }


    public function update(Request $request, $id)
    {
        $orderItem = OrderItem::where('id', $id)->first();
        $order = Order::where('order_id', $orderItem->order_id)->first();

        // Update status_tracking and status_fulfillment
        if ($request->status != 2) {
            $orderItem->status_tracking = "";
        }
        $orderItem->status_fulfillment = $request->status;

        $orderItem->save();

        //if ($request->status == 3) {
            if ($order->user_id != 0) {
                try {
                    // Send notification
                    Notification::create([
                        'status' => 0,
                        'user_id' => $order->user_id,
                        'thumb' => 0,
                        'message' => $orderItem->product_name . ' ' . $orderItem->product_variant_name . ' order status has been updated. View details.',
                        'link' => '/orders/' . $order->order_id
                    ]);

                    // send email
                    Mail::to($order->email)->send(new OrderStatusChangedMail($order));
                } catch (Exception $exception) {
                    Log::error('Error sending notification: ' . $exception->getMessage());
                    // Provide a user-friendly error message
                    return back()->with('error', 'Failed to send notification. Please try again later.');
                }
            }
        //}

        return back()->with('success', 'Order item status updated successfully.');
    }

    public function mark_as_canceled(Request $request)
    {
        $order = Order::where('id', $request->order_id)->firstOrFail();

        $order->status_payment = 3; // canceled
        $order->save();

        // set seller balance
        $amount = 0;
        foreach ($order->items as $orderItem) {
            $seller = $orderItem->product->user->seller;
            if ($seller) {
                /* Mark seller wallet history status to 2 */
                $seller_wallet_history = SellersWalletHistory::where([
                    'user_id' => $seller->user_id,
                    'order_id' => $order->id,
                ])->first();

                $seller_wallet_history->status = 2;
                $seller_wallet_history->save();

                $seller->wallet = $seller->wallet - $seller_wallet_history->amount;
                $seller->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function mark_as_chargeback(Request $request)
    {
        $order = Order::where('id', $request->order_id)->firstOrFail();

        $order->status_payment = 3; // chargeback
        $order->save();

        // set seller balance
        $amount = 0;
        foreach ($order->items as $orderItem) {
            $seller = $orderItem->product->user->seller;
            if ($seller) {
                /* Mark seller wallet history status to 3 */
                $seller_wallet_history = SellersWalletHistory::where([
                    'user_id' => $seller->user_id,
                    'order_id' => $order->id,
                ])->first();

                $seller_wallet_history->status = 3;
                $seller_wallet_history->save();

                $seller->wallet = $seller->wallet - $seller_wallet_history->amount;
                $seller->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

        public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('backend.orders.show')->with('order', Order::find($id));
    }

    public function edit($id)
    {
        //
    }
    
    public function destroy($id)
    {
        //
    }
}
