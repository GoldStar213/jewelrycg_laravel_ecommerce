<?php

namespace App\Mail;

use App\Models\User;
use App\Models\ServiceOrder;
use App\Models\ServicePost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ShippingOption;
use Gloudemans\Shoppingcart\Facades\Cart;

class ServiceOrderPlacedNotifySellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private ServiceOrder $order)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->service = ServicePost::where('id', $this->order->service_id)->first();
        $this->user = User::find($this->service->user_id);

        return $this->subject('You received a service order on JewelryCG #' . $this->order->order_id)
            ->view('emails.serviceorders.sellernotifyplaced')
            ->with([
                'orderID' => $this->order->order_id,
                'service_name' => $this->order->service_name,
                'package_name' => $this->order->package_name,
                'package_description' => $this->order->package_description,
                'package_price' => number_format($this->order->package_price / 100, 2),
                'package_delivery_time' => $this->order->package_delivery_time,
                'revisions' => $this->order->revisions,
                'original_delivery_time' => $this->order->original_delivery_time,
                'username' => $this->user->username,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
            ]);
    }
}
