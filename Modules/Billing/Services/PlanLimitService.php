<?php

namespace Modules\Billing\Services;

use App\Exceptions\PlanLimitExceededException;
use App\Models\Company;
use Modules\Employee\Repositories\UserRepository;
use Modules\Inventory\Repositories\ProductRepository;
use Modules\Inventory\Repositories\PromotionRepository;
use Modules\RBAC\Repositories\RoleRepository;
use Modules\RBAC\Repositories\UserRoleRepository;
use Modules\Supplier\Repositories\PurchaseOrderRepository;
use Modules\Supplier\Repositories\SupplierRepository;
use Modules\Billing\Repositories\PlanLimitRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\SubscriptionRepository;

class PlanLimitService
{
    /**
     * A limit value below zero (e.g. -1) means "unlimited".
     */
    const UNLIMITED = -1;

    protected $users;
    protected $roles;
    protected $userRoles;
    protected $subscriptions;
    protected $plans;
    protected $planLimits;
    protected $products;
    protected $suppliers;
    protected $promotions;
    protected $purchaseOrders;

    public function __construct(
        UserRepository $users,
        RoleRepository $roles,
        UserRoleRepository $userRoles,
        SubscriptionRepository $subscriptions,
        PlanRepository $plans,
        PlanLimitRepository $planLimits,
        ProductRepository $products,
        SupplierRepository $suppliers,
        PromotionRepository $promotions,
        PurchaseOrderRepository $purchaseOrders
    ) {
        $this->users = $users;
        $this->roles = $roles;
        $this->userRoles = $userRoles;
        $this->subscriptions = $subscriptions;
        $this->plans = $plans;
        $this->planLimits = $planLimits;
        $this->products = $products;
        $this->suppliers = $suppliers;
        $this->promotions = $promotions;
        $this->purchaseOrders = $purchaseOrders;
    }

    /**
     * Resolve the plan behind a company's active subscription.
     *
     * @param  \App\Models\Company|null  $company
     * @return \Modules\Billing\Models\Plan|null
     */
    public function planForCompany(?Company $company): ?\Modules\Billing\Models\Plan
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
     * The numeric limit for a resource key, or null when unlimited/not set.
     *
     * @param  string  $key
     * @param  \Modules\Billing\Models\Plan|null  $plan
     * @return int|null
     */
    public function limitFor(string $key, ?\Modules\Billing\Models\Plan $plan): ?int
    {
        if (!$plan) {
            return null;
        }

        $limit = $this->planLimits->query()->where('plan_id', $plan->id)->where('resource_key', $key)->first();

        if (!$limit) {
            return null;
        }

        $value = (int) $limit->limit_value;

        return $value < 0 ? null : $value;
    }

    /**
     * Current usage of a limited resource for the given company.
     *
     * @param  string  $key
     * @param  \App\Models\Company  $company
     * @return int
     */
    public function usage(string $key, Company $company): int
    {
        switch ($key) {
            case 'employees':
                return $this->employeesUsage($company);
            case 'users':
                return $this->users->countForCompany($company->id);
            case 'roles':
                return $this->rolesUsage($company);
            case 'products':
                return $this->products->countForCompany($company->id);
            case 'suppliers':
                return $this->suppliers->countForCompany($company->id);
            case 'promotions':
                return $this->promotions->countForCompany($company->id);
            case 'purchase_orders':
                return $this->purchaseOrders->countForCompany($company->id);
        }

        return 0;
    }

    /**
     * Throw when adding one more of the resource would exceed the plan limit.
     *
     * @param  string  $key
     * @param  \App\Models\Company|null  $company
     * @return void
     *
     * @throws \App\Exceptions\PlanLimitExceededException
     */
    public function ensureWithinLimit(string $key, ?Company $company): void
    {
        if (!$company) {
            return;
        }

        $plan = $this->planForCompany($company);

        if (!$plan) {
            return;
        }

        $limit = $this->limitFor($key, $plan);

        if ($limit === null) {
            return;
        }

        if ($this->usage($key, $company) >= $limit) {
            throw new PlanLimitExceededException($this->message($key, $limit));
        }
    }

    /**
     * Company users flagged for payroll and/or attendance (data access via the
     * Employee UserRepository).
     *
     * @param  \App\Models\Company  $company
     * @return int
     */
    protected function employeesUsage(Company $company): int
    {
        return $this->users->countFlaggedForCompany($company->id);
    }

    /**
     * Company-scoped roles, excluding the provisioned owner role.
     *
     * @param  \App\Models\Company  $company
     * @return int
     */
    protected function rolesUsage(Company $company): int
    {
        $query = $this->roles->query()->where('company_id', $company->id);

        $ownerRoleId = $this->ownerRoleId($company);

        if ($ownerRoleId) {
            $query->where('id', '!=', $ownerRoleId);
        }

        return $query->count();
    }

    /**
     * Role assigned to the company's earliest (owner) user.
     *
     * @param  \App\Models\Company  $company
     * @return int|null
     */
    protected function ownerRoleId(Company $company): ?int
    {
        $ownerUserId = $this->users->query()->where('company_id', $company->id)->orderBy('id')->value('id');

        if (!$ownerUserId) {
            return null;
        }

        return $this->userRoles->query()->where('user_id', $ownerUserId)->value('role_id');
    }

    /**
     * @param  string  $key
     * @param  int  $limit
     * @return string
     */
    protected function message(string $key, int $limit): string
    {
        $labels = [
            'employees' => 'employees',
            'roles' => 'roles',
        ];

        $label = $labels[$key] ?? str_replace('_', ' ', $key);

        return "Your current plan allows up to {$limit} {$label}. Upgrade your plan to add more.";
    }
}
