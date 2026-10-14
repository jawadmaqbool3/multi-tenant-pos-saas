<?php

namespace Modules\Billing\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Repositories\PlanFeatureRepository;
use Modules\Billing\Repositories\PlanLimitRepository;
use Modules\Billing\Repositories\PlanPermissionRepository;
use Modules\Billing\Repositories\PlanRepository;
use Modules\Billing\Repositories\PriceRepository;
use Modules\RBAC\Repositories\PermissionModuleRepository;
use Modules\RBAC\Repositories\PermissionRepository;

class PlanService
{
    /** @var array<string, string> */
    protected $limitKeys = [
        'products' => 'Products',
        'users' => 'Users',
        'employees' => 'Employees',
        'roles' => 'Roles',
        'suppliers' => 'Suppliers',
        'promotions' => 'Promotions',
        'purchase_orders' => 'Purchase Orders',
    ];

    protected $plans;
    protected $prices;
    protected $features;
    protected $planPermissions;
    protected $permissions;
    protected $limits;
    protected $modules;
    protected $stripe;
    protected $rawStripe;

    public function __construct(
        PlanRepository $plans,
        PriceRepository $prices,
        PlanFeatureRepository $features,
        PlanPermissionRepository $planPermissions,
        PlanLimitRepository $limits,
        PermissionRepository $permissions,
        PermissionModuleRepository $modules,
        StripeSyncService $stripe,
        StripeService $rawStripe
    ) {
        $this->plans = $plans;
        $this->prices = $prices;
        $this->features = $features;
        $this->planPermissions = $planPermissions;
        $this->limits = $limits;
        $this->permissions = $permissions;
        $this->modules = $modules;
        $this->stripe = $stripe;
        $this->rawStripe = $rawStripe;
    }

    public function getPlans()
    {
        return $this->plans->dataTable($this->plans->query());
    }

    public function getLimitKeys()
    {
        return $this->limitKeys;
    }

    /**
     * View data for the create/edit form.
     *
     * @param  mixed  $plan
     * @return array
     */
    public function getFormData($plan = null)
    {
        $modules = $this->modules->query()->where('is_platform', 0)->orderBy('id')->get();
        $permissions = $this->permissions->query()
            ->where('is_platform', 0)
            ->whereNull('company_id')
            ->orderBy('module_id')
            ->get();

        $selectedModules = [];
        $selectedPermissions = [];
        $selectedPrices = [];
        $limits = [];

        if ($plan) {
            $plan->loadMissing(["features", "permissions", "prices", "limits"]);
            $selectedModules = $plan->features->pluck('permission_module_id')->all();
            $selectedPermissions = $plan->permissions->pluck('permission_id')->all();
            $selectedPrices = $plan->prices;
            $limits = $plan->limits->pluck('limit_value', 'resource_key')->all();
        }

        return [
            'modules' => $modules,
            'permissions' => $permissions,
            'plan' => $plan,
            'selectedModules' => $selectedModules,
            'selectedPermissions' => $selectedPermissions,
            'selectedPrices' => $selectedPrices,
            'limits' => $limits,
            'limitKeys' => $this->limitKeys,
            'currency' => config('billing.currency'),
        ];
    }

    /**
     * Persist a new plan. Local DB writes run first inside a transaction,
     * then the Stripe sync runs outside of it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Modules\Billing\Models\Plan
     */
    public function store(Request $request)
    {
        $plan = DB::transaction(function () use ($request) {
            $plan = $this->plans->create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'status' => $request->status,
                'is_public' => $request->boolean('is_public'),
                'sort' => $request->sort ?: 100,
            ]);
            $plan->refresh();

            $this->savePrices($plan, $request->input('prices', []));
            $this->saveFeatures($plan, $request->input('modules', []));
            $this->savePlanPermissions($plan, $request->input('permissions', []));
            $this->saveLimits($plan, $request->input('limits', []));

            return $plan;
        });

        $this->stripe->syncPlan($plan->refresh());

        return $plan->refresh();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return \Modules\Billing\Models\Plan
     */
    public function update(Request $request, $plan)
    {
        DB::transaction(function () use ($request, $plan) {
            $this->plans->update($plan->uid, [
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'status' => $request->status,
                'is_public' => $request->boolean('is_public'),
                'sort' => $request->sort ?: 100,
            ]);
            $plan->refresh();

            // Replace prices and junctions wholesale.
            $plan->prices()->delete();
            $plan->features()->delete();
            $plan->permissions()->delete();
            $plan->limits()->delete();

            $this->savePrices($plan, $request->input('prices', []));
            $this->saveFeatures($plan, $request->input('modules', []));
            $this->savePlanPermissions($plan, $request->input('permissions', []));
            $this->saveLimits($plan, $request->input('limits', []));
        });

        $this->stripe->syncPlan($plan->refresh());

        return $plan->refresh();
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return bool|null
     */
    public function delete($plan)
    {
        $deleted = DB::transaction(function () use ($plan) {
            $plan->prices()->delete();
            $plan->features()->delete();
            $plan->permissions()->delete();
            $plan->limits()->delete();

            return $this->plans->delete($plan->uid);
        });

        $this->archivedStripeProduct($plan->stripe_product_id);

        return $deleted;
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @param  array  $prices
     * @return void
     */
    protected function savePrices($plan, array $prices)
    {
        foreach ($prices as $row) {
            $unitAmount = isset($row['unit_amount']) && $row['unit_amount'] !== '' && $row['unit_amount'] !== null
                ? (int) $row['unit_amount']
                : 0;
            if ($unitAmount < 0) {
                continue;
            }

            $this->prices->create([
                'plan_id' => $plan->id,
                'interval' => $row['interval'] ?? 'month',
                'interval_count' => 1,
                'unit_amount' => $unitAmount,
                'currency' => $row['currency'] ?? config('billing.currency'),
                'active' => !empty($row['active']),
            ]);
        }
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @param  array  $moduleIds
     * @return void
     */
    protected function saveFeatures($plan, array $moduleIds)
    {
        foreach ($moduleIds as $moduleId) {
            if ((int) $moduleId <= 0) {
                continue;
            }
            $this->features->create([
                'plan_id' => $plan->id,
                'permission_module_id' => (int) $moduleId,
            ]);
        }
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @param  array  $permissionUids
     * @return void
     */
    protected function savePlanPermissions($plan, array $permissionUids)
    {
        $permissionIds = $this->permissions->query()
            ->whereIn('uid', $permissionUids)
            ->where('is_platform', 0)
            ->pluck('id')
            ->all();

        foreach ($permissionIds as $permissionId) {
            $this->planPermissions->create([
                'plan_id' => $plan->id,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @param  array  $input
     * @return void
     */
    protected function saveLimits($plan, array $input)
    {
        foreach ($this->limitKeys as $key => $label) {
            $value = $input[$key] ?? null;
            if ($value === '' || $value === null) {
                continue;
            }
            $int = (int) $value;
            if ($int >= 0 || $int === -1) {
                $this->limits->create([
                    'plan_id' => $plan->id,
                    'resource_key' => $key,
                    'limit_value' => $int,
                ]);
            }
        }
    }

    /**
     * Deactivate the Stripe product backing a deleted plan. The product is
     * kept in Stripe so historical subscriptions still resolve.
     *
     * @return void
     */
    protected function archivedStripeProduct($stripeProductId)
    {
        if (!$stripeProductId) {
            return;
        }
        try {
            $this->rawStripe->request(function () use ($stripeProductId) {
                $this->rawStripe->client()->products->update($stripeProductId, ['active' => false]);
            });
        } catch (\Throwable $e) {
            // Local record is already gone; Stripe archiving is best-effort.
        }
    }
}