<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
   protected $fillable=['shopify_order_id','return_authorization_id','credit_memo_id','am_order_id'];
}
