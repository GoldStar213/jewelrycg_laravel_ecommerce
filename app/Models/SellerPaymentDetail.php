<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerPaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
    ];

    // Add the following property to map the table name explicitly
    protected $table = 'seller_payment_details';

    // Add the following property to disable the default timestamps
    public $timestamps = false;
}
