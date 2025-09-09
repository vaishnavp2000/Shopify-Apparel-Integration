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

    
    });
});

// Club
Route::prefix('club')->name('club.')->namespace('App\Http\Controllers\Club')->group(function () {
    Auth::routes(['register' => false]);
    Route::middleware(['auth:club'])->group(function () {
        Route::get('/', [App\Http\Controllers\Club\HomeController::class, 'index'])->name('dashboard');
       
        
    });
});
