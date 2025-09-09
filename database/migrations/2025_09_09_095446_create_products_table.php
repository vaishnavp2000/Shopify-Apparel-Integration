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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('size_range_id')->nullable();
            $table->tinyInteger('is_product')->nullable();
            $table->tinyInteger('is_component')->nullable();
            $table->string('style_number')->nullable();
            $table->string('price')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_handle')->nullable();
            $table->string('title')->nullable();
            $table->string('total_variants')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
