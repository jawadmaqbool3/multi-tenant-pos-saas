<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'plan.feature:payroll']], function () {
    Route::resource('payrolls', \Modules\Payroll\Http\Controllers\PayrollController::class)
        ->except(['edit', 'update'])->middleware('permission:view payroll');
    Route::resource('allowances', \Modules\Payroll\Http\Controllers\AllowanceController::class)
        ->except(['show'])->middleware('permission:view allowance');
    Route::resource('deductions', \Modules\Payroll\Http\Controllers\DeductionController::class)
        ->except(['show'])->middleware('permission:view deduction');

    Route::patch('payrolls/{payroll}/payslips/{payslip}', [\Modules\Payroll\Http\Controllers\PayrollController::class, 'updatePayslip'])
        ->name('payrolls.payslips.update')->middleware('permission:edit payroll');

    Route::patch('payrolls/{payroll}/status', [\Modules\Payroll\Http\Controllers\PayrollController::class, 'updateStatus'])
        ->name('payrolls.status.update')->middleware('permission:edit payroll');

    Route::get('print/payslip/{payslip}', [\Modules\Payroll\Http\Controllers\PayrollController::class, 'printPayslip'])
        ->name('payslip.print')->middleware('permission:view payroll');
});