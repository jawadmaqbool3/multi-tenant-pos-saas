<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'billing');
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'billing');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::group(['middleware' => ['web']], function () {
            require base_path('Modules/Billing/routes/web.php');
        });

        $registry = $this->app->make(\App\Dashboard\DashboardRegistry::class);
        foreach ($this->dashboardWidgets() as $widget) {
            $registry->register($widget);
        }
        foreach ($this->platformDashboardWidgets() as $widget) {
            $registry->register($widget);
        }
    }

    /**
     * Tenant subscription widgets (billing.*).
     *
     * @return array<int, class-string>
     */
    protected function dashboardWidgets(): array
    {
        return [
            \Modules\Billing\Dashboard\Widgets\NoPlanCard::class,
            \Modules\Billing\Dashboard\Widgets\CurrentPlanCard::class,
            \Modules\Billing\Dashboard\Widgets\RenewalCard::class,
            \Modules\Billing\Dashboard\Widgets\UsageTable::class,
            \Modules\Billing\Dashboard\Widgets\NextInvoiceCard::class,
            \Modules\Billing\Dashboard\Widgets\InvoicesTable::class,
        ];
    }

    /**
     * Platform (SaaS owner) widgets (platform.*).
     *
     * @return array<int, class-string>
     */
    protected function platformDashboardWidgets(): array
    {
        return [
            \Modules\Billing\Dashboard\Widgets\CompaniesCard::class,
            \Modules\Billing\Dashboard\Widgets\ActiveCompaniesCard::class,
            \Modules\Billing\Dashboard\Widgets\InactiveCompaniesCard::class,
            \Modules\Billing\Dashboard\Widgets\NewCompaniesCard::class,
            \Modules\Billing\Dashboard\Widgets\TenantsCard::class,
            \Modules\Billing\Dashboard\Widgets\RecentCompaniesTable::class,
            \Modules\Billing\Dashboard\Widgets\StripeBalanceCard::class,
            \Modules\Billing\Dashboard\Widgets\MrrCard::class,
            \Modules\Billing\Dashboard\Widgets\ArpaCard::class,
            \Modules\Billing\Dashboard\Widgets\RevenueChart::class,
            \Modules\Billing\Dashboard\Widgets\DuePaymentsCard::class,
            \Modules\Billing\Dashboard\Widgets\PastDueSubscriptionsCard::class,
            \Modules\Billing\Dashboard\Widgets\FailedPaymentsCard::class,
            \Modules\Billing\Dashboard\Widgets\DueInvoicesTable::class,
        ];
    }
}