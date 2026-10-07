<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Blade::directive('can', function ($name) {
            return "<?php if (@auth()->user()->hasPermission($name)) { ?>";
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
