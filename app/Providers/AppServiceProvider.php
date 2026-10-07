<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    protected $setting;
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Blade::directive('can', function ($name) {
            return "<?php if (@auth()->user()->hasPermission($name)) { ?>";
        });
        Blade::directive('setting', function ($name) {
            return "<?php echo auth()->user()->setting()->$name ?>";
        });
        Blade::directive('endcan', function () {
            return "<?php } ?>";
        });

        Blade::directive('role', function ($name) {
            return "<?php if (@auth()->user()->hasRole($name)) { ?>";
        });
        Blade::directive('endrole', function () {
            return "<?php } ?>";
        });
    }
}
