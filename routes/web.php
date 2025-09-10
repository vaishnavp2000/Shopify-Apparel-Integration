<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth::routes();

Route::prefix('admin')->name('admin.')->namespace('App\Http\Controllers\Admin')->group(function () {
    Auth::routes(['register' => false]);
    Route::middleware(['auth:admin'])->group(function () {

        // HomeController 
        Route::get('/', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('dashboard');
        Route::resource('product', App\Http\Controllers\Admin\ProductController::class);
        Route::post('fetch-product', [App\Http\Controllers\Admin\ProductController::class, 'fetchProducts'])->name('fetch-product');
        Route::post('create-am-products', [App\Http\Controllers\Admin\ProductController::class, 'createAmProducts'])->name('create-am-products');

    
    });
});
