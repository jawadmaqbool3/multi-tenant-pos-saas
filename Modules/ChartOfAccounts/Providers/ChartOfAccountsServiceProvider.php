<?php

namespace Modules\ChartOfAccounts\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ChartOfAccounts\Models\Voucher;

class ChartOfAccountsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'chartofaccounts');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Relation::morphMap([
            'voucher' => Voucher::class,
        ]);

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/ChartOfAccounts/routes/web.php');
        });
    }
}
