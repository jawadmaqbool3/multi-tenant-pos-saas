<?php

namespace Modules\Supplier\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Supplier\Models\PurchaseOrder;
use Modules\Supplier\Models\Supplier;

class SupplierServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'supplier');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Relation::morphMap([
            'supplier' => Supplier::class,
            'purchase_order' => PurchaseOrder::class,
        ]);

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Supplier/routes/web.php');
        });
    }
}
