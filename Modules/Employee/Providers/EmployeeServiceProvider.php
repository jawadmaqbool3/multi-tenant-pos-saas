<?php

namespace Modules\Employee\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EmployeeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'employee');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Employee/routes/web.php');
        });
    }
}
