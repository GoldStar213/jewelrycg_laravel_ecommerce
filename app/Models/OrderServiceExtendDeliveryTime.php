<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderServiceExtendDeliveryTime extends Model
{
    protected $table = 'order_service_extend_delivery_time';

    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'status',
        'days',
        'message',
    ];

    // Add any additional properties or methods as needed
}
