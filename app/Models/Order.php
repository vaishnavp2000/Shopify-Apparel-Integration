<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
   protected $fillable=[
        'am_order_id',
        'warehouse_id',
        'customer_id',
        'division_id',
        'arr_accnt',
        'currency_id',
        'shopify_order_name',
        'date',
        'date_start',
        'notes',
        'amount',
        'customer_name',
        'address_1',
        'address_2',
        'city',
        'postal_code',
        'country',
        'state',
        'phone',
        'email',
        'customer_po',
        'credit_status',
        'qty',
        'qty_cancelled',
        'qty_shipped',
        'ship_via',
        'amount_paid',
        'fulfillment_status',
        'balance',
        'pick_ticket_id',
        'shipment_id',
        'allocated',
        'shopify_order_id',
        'shopify_email',
        'shopify_customer_id',
        'shopify_customer_firstname',
        'shopify_customer_lastname',
        'shopify_shipping_address1',
        'shopify_shipping_address2',
        'shopify_shipping_city',
        'shopify_shipping_zip',
        'shopify_shipping_country',
        'shopify_shipping_provincecode',
        'shopify_shipping_phone',
        'shopify_shipping_notes',
        'shopify_shipping_total',
        'shopify_created_at',
        'shopify_fulfillment_status'
   ];
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'shopify_order_id','shopify_order_id');
    }
}
