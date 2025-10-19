<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'delivery';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'pincode',
        'total_amount',
        'cart_items',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'cart_items' => 'array'
    ];

    
}
