<?php

namespace App\Mail;

use App\Models\User;
use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ShippingOption;
use Gloudemans\Shoppingcart\Facades\Cart;

class ServiceOrderPlacedMail extends Mailable
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
        $this->user = User::find($this->order->user_id);
        return $this->subject('Your JewelryCG.com service order #' . $this->order->order_id)
            ->view('emails.serviceorders.placed')
            ->with([
                'orderID' => $this->order->order_id,
                'service_name' => $this->order->service_name,
                'package_name' => $this->order->package_name,
                'package_description' => $this->order->package_description,
                'package_price' => number_format($this->order->package_price / 100, 2),
                'service_fee' => number_format(($this->order->package_price * 0.055) / 100, 2),
                'total_price' => number_format((($this->order->package_price * 0.055) / 100) + ($this->order->package_price / 100), 2),
                'package_delivery_time' => $this->order->package_delivery_time,
                'revisions' => $this->order->revisions,
                'original_delivery_time' => $this->order->original_delivery_time,
                'username' => $this->user->username,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
            ]);
    }
}
