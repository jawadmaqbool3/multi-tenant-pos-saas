<?php

use Modules\Inventory\Http\Controllers\CategoryController;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\PromotionController;
use Modules\Inventory\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('categories', CategoryController::class)->middleware('permission:view category');
    Route::resource('products', ProductController::class)->middleware('permission:view product');
    Route::resource('promotions', PromotionController::class)->middleware('permission:view promotion');
    Route::resource('stocks', StockController::class)->middleware('permission:view stocks');
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
