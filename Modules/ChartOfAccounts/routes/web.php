<?php

use Modules\ChartOfAccounts\Http\Controllers\AccountController;
use Modules\ChartOfAccounts\Http\Controllers\AccountSettingController;
use Modules\ChartOfAccounts\Http\Controllers\BalanceSheetController;
use Modules\ChartOfAccounts\Http\Controllers\LedgerController;
use Modules\ChartOfAccounts\Http\Controllers\VoucherController;
use Modules\ChartOfAccounts\Http\Controllers\VoucherTypeController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::group(['prefix' => 'vouchers', 'middleware' => ['plan.feature:chart-of-accounts']], function () {
        Route::get('/', [VoucherController::class, 'index'])
            ->middleware('permission:view voucher')
            ->name('vouchers.index');
        Route::get('create', [VoucherController::class, 'create'])
            ->middleware('permission:create voucher')
            ->name('vouchers.create');
        Route::post('/', [VoucherController::class, 'store'])
            ->middleware('permission:create voucher')
            ->name('vouchers.store');
        Route::post('void/{voucher}', [VoucherController::class, 'void'])
            ->middleware('permission:delete voucher')
            ->name('vouchers.void');
        Route::get('{voucher}/print', [VoucherController::class, 'print'])
            ->middleware('permission:view voucher')
            ->name('print.voucher');
    });
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

    Route::group(['prefix' => 'reports', 'middleware' => ['plan.feature:chart-of-accounts']], function () {
        Route::get('ledger', [LedgerController::class, 'index'])
            ->middleware('permission:view ledger')
            ->name('ledger.index');

        Route::get('balance-sheet', [BalanceSheetController::class, 'index'])
            ->middleware('permission:view balance sheet')
            ->name('balance-sheet.index');
    });
});
