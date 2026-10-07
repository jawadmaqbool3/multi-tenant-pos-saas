<?php

use Modules\Inventory\Http\Controllers\CategoryController;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\PromotionController;
use Modules\Inventory\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('stocks', StockController::class);
});

Route::group(['prefix' => 'print'], function () {
    Route::get('/stock/{type}', [
        'uses' => 'Modules\Inventory\Http\Controllers\StockController@print',
        'as' => 'print.stock',
    ]);
    Route::get('/barcode/{product}', [
        'uses' => 'Modules\Inventory\Http\Controllers\ProductController@print_barcode',
        'as' => 'print.barcode',
    ]);
});
