<?php

use Modules\ChartOfAccounts\Http\Controllers\AccountController;
use Modules\ChartOfAccounts\Http\Controllers\AccountSettingController;
use Modules\ChartOfAccounts\Http\Controllers\VoucherController;
use Modules\ChartOfAccounts\Http\Controllers\VoucherTypeController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('vouchers', VoucherController::class)->middleware(['permission:view voucher', 'plan.feature:chart-of-accounts']);
    Route::resource('voucher-types', VoucherTypeController::class)->middleware(['permission:view voucher type', 'plan.feature:chart-of-accounts']);

    Route::group(['prefix' => 'accounts', 'middleware' => ['permission:view account', 'plan.feature:chart-of-accounts']], function () {
        Route::get('/', 'Modules\ChartOfAccounts\Http\Controllers\AccountController@index')->name('accounts.index');
        Route::post('/store', 'Modules\ChartOfAccounts\Http\Controllers\AccountController@store')->name('accounts.store');
        Route::post('/update/{account}', 'Modules\ChartOfAccounts\Http\Controllers\AccountController@update')->name('accounts.update');
        Route::post('/delete/{account}', 'Modules\ChartOfAccounts\Http\Controllers\AccountController@delete')->name('accounts.delete');
    });

    Route::group(['prefix' => 'settings', 'middleware' => ['permission:account settings', 'plan.feature:chart-of-accounts']], function () {
        Route::get('accounts/{company?}', 'Modules\ChartOfAccounts\Http\Controllers\AccountSettingController@index')->name('account.settings');
        Route::post('accounts/update/{company?}', 'Modules\ChartOfAccounts\Http\Controllers\AccountSettingController@update')->name('account.settings.update');
    });
});

Route::group(['prefix' => 'print'], function () {
    Route::get('/print/{voucher}', [
        'uses' => 'Modules\ChartOfAccounts\Http\Controllers\VoucherController@print',
        'as' => 'print.voucher',
    ]);
});
