<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
             $table->string('order_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->string('arr_accnt')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('shopify_order_name')->nullable();
            $table->date('date')->nullable();
            $table->date('date_start')->nullable();
            $table->string('notes')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('customer_name')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('customer_po')->nullable();
            $table->string('credit_status')->nullable();
            $table->integer('qty')->default(0);
            $table->integer('qty_cancelled')->default(0);
            $table->integer('qty_shipped')->default(0);
            $table->string('ship_via')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('fulfillment_status')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('pick_ticket_id')->nullable();
            $table->boolean('allocated')->default(0);
            $table->string('shopify_order_id')->nullable();
            $table->string('shopify_email')->nullable();
            $table->string('shopify_customer_id')->nullable();
            $table->string('shopify_customer_firstname')->nullable();
            $table->string('shopify_customer_lastname')->nullable();
            $table->string('shopify_shipping_address1')->nullable();
            $table->string('shopify_shipping_address2')->nullable();
            $table->string('shopify_shipping_city')->nullable();
            $table->string('shopify_shipping_zip')->nullable();
            $table->string('shopify_shipping_country')->nullable();
            $table->string('shopify_shipping_provincecode')->nullable();
            $table->string('shopify_shipping_phone')->nullable();
            $table->string('shopify_shipping_notes')->nullable();
            $table->string('shopify_shipping_total')->nullable();
            $table->date('shopify_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
