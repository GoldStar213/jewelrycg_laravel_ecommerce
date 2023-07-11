<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'service_id', 'order_id', 'rating', 'review', 'review_attachment', 'review_attachment_id'];

    public function order()
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

