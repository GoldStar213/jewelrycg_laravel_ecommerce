<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlocks extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blocked_user_id'
    ];
}
