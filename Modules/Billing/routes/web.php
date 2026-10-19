<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\CouponController;
use Modules\Billing\Http\Controllers\CompanySubscriptionController;
use Modules\Billing\Http\Controllers\InvoiceController;
use Modules\Billing\Http\Controllers\PlanController;
use Modules\Billing\Http\Controllers\StripeWebhookController;
use Modules\Billing\Http\Controllers\SubscribeController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('subscribe', [SubscribeController::class, 'show'])->name('billing.subscribe');
    Route::get('pricing', [SubscribeController::class, 'pricing'])->name('billing.pricing');
    Route::post('subscribe/checkout', [SubscribeController::class, 'checkout'])->name('billing.subscribe.checkout');
    Route::get('subscribe/success', [SubscribeController::class, 'success'])->name('billing.subscribe.success');
    Route::get('subscribe/cancel', [SubscribeController::class, 'cancel'])->name('billing.subscribe.cancel');

    Route::get('subscription', [CompanySubscriptionController::class, 'index'])
        ->name('billing.subscription')
        ->middleware('permission:manage company subscription');

    Route::post('subscription/change-plan', [CompanySubscriptionController::class, 'changePlan'])
        ->name('billing.subscription.change-plan')
        ->middleware('permission:manage company subscription');

    Route::resource('plans', PlanController::class)
        ->names('billing.plans')
        ->middleware('permission:view billing plan');

    Route::resource('coupons', CouponController::class)
        ->except(['show'])
        ->names('billing.coupons')
        ->middleware('permission:view coupon');

    Route::get('invoices', [InvoiceController::class, 'index'])
        ->name('billing.invoices.index')
        ->middleware('permission:view billing invoice');
});

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle'])->name('billing.webhook');
