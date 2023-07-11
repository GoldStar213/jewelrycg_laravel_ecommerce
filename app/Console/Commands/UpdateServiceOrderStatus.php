<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceOrder;
use Carbon\Carbon;

class UpdateServiceOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:update_service_order_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto update service order status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = ServiceOrder::where(function ($query) {
            $query->where('extended_delivery_time', '<=', Carbon::now()->subDays(3))
                ->orWhere(function ($query) {
                    $query->where('extended_delivery_time', '=', '0000-00-00 00:00:00')
                        ->where('original_delivery_time', '<=', Carbon::now()->subDays(3));
                });
        })->get();

        // Update the status_payment of each order to 4
        foreach ($orders as $order) {
            $order->status = 5;
            $order->save();
        }

    }
}
