<?php

namespace App\Dashboard\Providers;

use App\Dashboard\DashboardRegistry;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register the dashboard registry as a singleton so every module can
     * contribute widgets into the same instance.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DashboardRegistry::class, function () {
            return new DashboardRegistry();
        });
    }

    /**
     * Register the core (non-module) widgets.
     *
     * @return void
     */
    public function boot()
    {
        $registry = $this->app->make(DashboardRegistry::class);

        foreach ($this->coreWidgets() as $widget) {
            $registry->register($widget);
        }
    }

    /**
     * @return array<int, class-string>
     */
    protected function coreWidgets(): array
    {
        return [
            \App\Dashboard\Widgets\ProfitCard::class,
            \App\Dashboard\Widgets\AverageSalesChart::class,
            \App\Dashboard\Widgets\MonthlySalesCard::class,
            \App\Dashboard\Widgets\MonthlyOrdersCard::class,
            \App\Dashboard\Widgets\MonthlyCancelledOrdersCard::class,
            \App\Dashboard\Widgets\MonthlyRefundedOrdersCard::class,
            \App\Dashboard\Widgets\MonthlyCustomersCard::class,
            \App\Dashboard\Widgets\RecentOrdersTable::class,
        ];
    }
}
