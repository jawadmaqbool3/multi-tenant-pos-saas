<?php

namespace Modules\Order\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'order');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Order/routes/web.php');
        });
    }
}
