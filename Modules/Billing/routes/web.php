<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PlanController;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('plans', PlanController::class)
        ->names('billing.plans')
        ->middleware('permission:view billing plan');
});