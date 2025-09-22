<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReport extends Model
{
    protected $fillable=['am_sku_id','shopify_sku_id','produt_name','shopify_available_qty','am_available_qty','shopify_barcode','upc_display'];
}
