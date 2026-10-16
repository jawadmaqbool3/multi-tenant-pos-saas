<?php

namespace Modules\Payroll\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PayrollServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'payroll');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Payroll/routes/web.php');
        });
    }
}