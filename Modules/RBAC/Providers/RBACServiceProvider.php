<?php

namespace Modules\RBAC\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;

class RBACServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'rbac');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Relation::morphMap([
            'role' => Role::class,
            'permission' => Permission::class,
            'user_role' => UserRole::class,
        ]);

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/RBAC/routes/web.php');
        });
    }
}
