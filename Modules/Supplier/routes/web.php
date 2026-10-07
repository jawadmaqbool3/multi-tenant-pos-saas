<?php

use Modules\Supplier\Http\Controllers\PurchaseOrderController;
use Modules\Supplier\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);

    Route::group(['prefix' => 'purchase-order'], function () {
        Route::post('refund/{purchaseOrder}', 'Modules\Supplier\Http\Controllers\PurchaseOrderController@refund')->name('purchase.order.refund');
    });

    Route::group(['prefix' => 'supplier'], function () {
        Route::post('pay/{supplier}', 'Modules\Supplier\Http\Controllers\SupplierController@pay')->name('supplier.pay');
        Route::get('ledger/{supplier}', 'Modules\Supplier\Http\Controllers\SupplierController@ledger')->name('supplier.ledger');
    });
});

Route::group(['prefix' => 'print'], function () {
    Route::get('/purchase-order/{purchaseOrder}', [
        'uses' => 'Modules\Supplier\Http\Controllers\PurchaseOrderController@print',
        'as' => 'purchase.order.print',
    ]);
});
