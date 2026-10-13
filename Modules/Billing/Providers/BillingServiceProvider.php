<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'billing');
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'billing');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Billing/routes/web.php');
        });
    }
}