<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
     protected $fillable = [
        'am_order_id',
        'shopify_order_id',
        'payment_id',
        'customer_id',
        'payment_type',
        'amt_dr',
        'date',
    ];
}
