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
        Schema::create('stock_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('am_sku_id')->nullable();
            $table->string('shopify_sku_id')->nullable();
            $table->string('produt_name')->nullable();
            $table->string('shopify_available_qty')->nullable();
            $table->string('am_available_qty')->nullable();
            $table->string('shopify_barcode')->nullable();
            $table->string('upc_display')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reports');
    }
};
