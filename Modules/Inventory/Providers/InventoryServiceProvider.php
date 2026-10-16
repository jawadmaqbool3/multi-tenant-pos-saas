<?php

namespace Modules\Inventory\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Models\Product;

class InventoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'inventory');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Relation::morphMap([
            'product' => Product::class,
        ]);

        $this->app->make(\App\Dashboard\DashboardRegistry::class)
            ->register(\Modules\Inventory\Dashboard\Widgets\StockReportWidget::class);

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Inventory/routes/web.php');
        });
    }
}
