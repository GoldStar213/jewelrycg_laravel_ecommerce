<?php

namespace App\Jobs;

use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateServiceOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        // Fetch orders with extended_delivery_time older than 3 days or original_delivery_time older than 3 days if extended_delivery_time is not set or '0000-00-00 00:00:00'
        $orders = ServiceOrder::where(function ($query) {
            $query->where('extended_delivery_time', '<=', Carbon::now()->subDays(3))
                ->orWhere(function ($query) {
                    $query->where('extended_delivery_time', '=', '0000-00-00 00:00:00')
                        ->where('original_delivery_time', '<=', Carbon::now()->subDays(3));
                });
        })->get();

        // Update the status_payment of each order to 4
        foreach ($orders as $order) {
            if ($order->status == 4) {
                $order->status = 5;
                $order->save();
            }
        }
    }
}
