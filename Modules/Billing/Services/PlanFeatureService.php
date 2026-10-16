<?php

namespace Modules\Billing\Services;

use App\Models\Company;
use Modules\Billing\Models\Plan;
use Modules\Billing\Repositories\PlanFeatureRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\SubscriptionRepository;

class PlanFeatureService
{
    protected $subscriptions;
    protected $plans;
    protected $planFeatures;

    public function __construct(
        SubscriptionRepository $subscriptions,
        PlanRepository $plans,
        PlanFeatureRepository $planFeatures
    ) {
        $this->subscriptions = $subscriptions;
        $this->plans = $plans;
        $this->planFeatures = $planFeatures;
    }

    /**
     * Feature levers mapped to the permission modules that unlock them.
     *
     * @var array<string, array<int, string>>
     */
    const FEATURES = [
        'chart-of-accounts' => ['Accounts', 'Vouchers', 'Voucher Types'],
        'inventory' => ['Products', 'Categories', 'Purchase Orders'],
        'promotions' => ['Promotions'],
        'payroll' => ['Payroll'],
    ];

    /**
     * Resolve the plan behind a company's active subscription.
     *
     * @param  \App\Models\Company|null  $company
     * @return \Modules\Billing\Models\Plan|null
     */
    public function planForCompany(?Company $company): ?Plan
    {
        if (!$company) {
            return null;
        }

        $subscription = $this->subscriptions->query()
            ->where('company_id', $company->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('current_period_end')->orWhere('current_period_end', '>', now());
            })
            ->latest('id')
            ->first();

        if (!$subscription) {
            return null;
        }

        return $this->plans->query()->where('id', $subscription->plan_id)->first();
    }

    /**
     * Module names included in the company's plan. A null result means the
     * plan is unrestricted (platform account or no active plan).
     *
     * @param  \App\Models\Company|null  $company
     * @return array<int, string>|null
     */
    public function allowedModules(?Company $company): ?array
    {
        $plan = $this->planForCompany($company);

        if (!$plan) {
            return null;
        }

        return $this->planFeatures->moduleNamesForPlan($plan->id);
    }

    /**
     * Every module that belongs to a plan-gated feature.
     *
     * @return array<int, string>
     */
    public function gatedModules(): array
    {
        $modules = [];

        foreach (self::FEATURES as $featureModules) {
            $modules = array_merge($modules, $featureModules);
        }

        return array_values(array_unique($modules));
    }

    /**
     * @param  string  $module
     * @return bool
     */
    public function isModuleGated(string $module): bool
    {
        return in_array($module, $this->gatedModules(), true);
    }

    /**
     * @param  \App\Models\Company|null  $company
     * @param  string  $module
     * @return bool
     */
    public function companyAllowsModule(?Company $company, string $module): bool
    {
        $allowed = $this->allowedModules($company);

        if ($allowed === null) {
            return true;
        }

        return in_array($module, $allowed, true);
    }

    /**
     * A feature is available when all of its modules are included.
     *
     * @param  \App\Models\Company|null  $company
     * @param  string  $feature
     * @return bool
     */
    public function companyAllowsFeature(?Company $company, string $feature): bool
    {
        $modules = self::FEATURES[$feature] ?? [];

        if (empty($modules)) {
            return true;
        }

        foreach ($modules as $module) {
            if (!$this->companyAllowsModule($company, $module)) {
                return false;
            }
        }

        return true;
    }
}
