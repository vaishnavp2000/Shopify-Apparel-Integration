<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::prefix('admin')->name('admin.')->namespace('App\Http\Controllers\Admin')->group(function () {
    Auth::routes(['register' => false]);
    Route::middleware(['auth:admin'])->group(function () {

        // HomeController 
        Route::get('/', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('dashboard');
        Route::resource('product', App\Http\Controllers\Admin\ProductController::class);
        Route::post('fetch-product', [App\Http\Controllers\Admin\ProductController::class, 'fetchProducts'])->name('fetch-product');
        Route::post('create-am-products', [App\Http\Controllers\Admin\ProductController::class, 'createAmProducts'])->name('create-am-products');
        Route::resource('order', App\Http\Controllers\Admin\OrderController::class);
        Route::post('fetch-orders', [App\Http\Controllers\Admin\OrderController::class, 'fetchOrders'])->name('fetch-orders');
        Route::post('create-am-orders', [App\Http\Controllers\Admin\OrderController::class, 'createAmOrders'])->name('create-am-orders');
        Route::post('create-shipment', [App\Http\Controllers\Admin\OrderController::class, 'createShipment'])->name('create-shipment');
        Route::post('cancel-order', [App\Http\Controllers\Admin\OrderController::class, 'cancelOrder'])->name('cancel-order');
        Route::post('fulfill-order', [App\Http\Controllers\Admin\OrderController::class, 'fulfilOrder'])->name('fulfil-order');

        




    
    });
});
