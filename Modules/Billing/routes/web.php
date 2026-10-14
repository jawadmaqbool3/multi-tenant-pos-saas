<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PlanController;
use Modules\Billing\Http\Controllers\StripeWebhookController;
use Modules\Billing\Http\Controllers\SubscribeController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('subscribe', [SubscribeController::class, 'show'])->name('billing.subscribe');
    Route::post('subscribe/checkout', [SubscribeController::class, 'checkout'])->name('billing.subscribe.checkout');
    Route::get('subscribe/success', [SubscribeController::class, 'success'])->name('billing.subscribe.success');
    Route::get('subscribe/cancel', [SubscribeController::class, 'cancel'])->name('billing.subscribe.cancel');

    Route::resource('plans', PlanController::class)
        ->names('billing.plans')
        ->middleware('permission:view billing plan');
});

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle'])->name('billing.webhook');
