<?php

namespace Modules\Employee\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Employee\Models\User;

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

        Relation::morphMap([
            'user' => User::class,
        ]);

        $registry = $this->app->make(\App\Dashboard\DashboardRegistry::class);
        $registry->register(\Modules\Employee\Dashboard\Widgets\AttendanceWidget::class);
        $registry->register(\Modules\Employee\Dashboard\Widgets\AttendanceSummaryCard::class);
        $registry->register(\Modules\Employee\Dashboard\Widgets\AttendanceMonthlyChart::class);
        $registry->register(\Modules\Employee\Dashboard\Widgets\AttendanceAbsentTable::class);

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Employee/routes/web.php');
        });
    }
}
