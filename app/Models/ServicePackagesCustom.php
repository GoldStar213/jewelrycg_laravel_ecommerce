<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackagesCustom extends Model
{
    use HasFactory;

    protected $table = 'service_packages_custom';

    protected $fillable = [
        "status",
        "service_id",
        "user_id",
        "name",
        "description",
        "price",
        "revisions",
        "delivery_time",
        "expiration_time",
        "requirements_status",
    ];

    public function service()
    {
        return $this->belongsTo(ServicePost::class, 'service_id', 'id');
    }
}
