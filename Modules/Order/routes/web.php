<?php

use Modules\Order\Http\Controllers\OrderController;
use Modules\Order\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('sales', SaleController::class);
    Route::resource('orders', OrderController::class);

    Route::group(['prefix' => 'order'], function () {
        Route::post('cancel/{id}', 'Modules\Order\Http\Controllers\OrderController@cancel')->name('order.cancel');
        Route::post('mark/paid/{order}', 'Modules\Order\Http\Controllers\OrderController@markPaid')->name('order.mark.paid');
        Route::post('refund/{id}', 'Modules\Order\Http\Controllers\OrderController@refund')->name('order.refund');
    });
});

Route::group(['prefix' => 'print'], function () {
    Route::get('/sales-order/{order}', [
        'uses' => 'Modules\Order\Http\Controllers\OrderController@print',
        'as' => 'sales.order.print',
    ]);
    Route::get('/sales/{from}/{to}', [
        'uses' => 'Modules\Order\Http\Controllers\SaleController@print',
        'as' => 'print.sales',
    ]);
});
