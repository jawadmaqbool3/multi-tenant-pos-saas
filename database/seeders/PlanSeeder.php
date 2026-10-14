<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Plan;
use Modules\RBAC\Models\PermissionModule;

class PlanSeeder extends Seeder
{
    /**
     * Resource limit value that means "unlimited".
     */
    const UNLIMITED = -1;

    /**
     * Chart of accounts modules.
     *
     * @var array<int, string>
     */
    protected $accountingModules = ['Accounts', 'Vouchers', 'Voucher Types'];

    /**
     * Inventory modules.
     *
     * @var array<int, string>
     */
    protected $inventoryModules = ['Products', 'Categories', 'Purchase Orders'];

    /**
     * Promotion modules.
     *
     * @var array<int, string>
     */
    protected $promotionModules = ['Promotions'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plan::where('slug', 'starter')
            ->update(['is_public' => false, 'status' => 'inactive']);

        $this->upsertPlan('basic', [
            'name' => 'Basic',
            'description' => 'For small shops getting started.',
            'sort' => 1,
            'price' => 19,
            'modules' => $this->inventoryModules,
            'limits' => [
                'employees' => 5,
                'roles' => 2,
            ],
        ]);

        $this->upsertPlan('pro', [
            'name' => 'Pro',
            'description' => 'For growing businesses that need accounting and promotions.',
            'sort' => 2,
            'price' => 49,
            'modules' => array_merge($this->inventoryModules, $this->accountingModules, $this->promotionModules),
            'limits' => [
                'employees' => 25,
                'roles' => 5,
            ],
        ]);

        $this->upsertPlan('enterprise', [
            'name' => 'Enterprise',
            'description' => 'Unlimited staff, roles and every module.',
            'sort' => 3,
            'price' => 99,
            'modules' => array_merge($this->inventoryModules, $this->accountingModules, $this->promotionModules),
            'limits' => [
                'employees' => self::UNLIMITED,
                'roles' => self::UNLIMITED,
            ],
        ]);
    }

    /**
     * Create or refresh a plan, its monthly price, feature modules and limits.
     *
     * @param  string  $slug
     * @param  array  $data
     * @return \Modules\Billing\Models\Plan
     */
    protected function upsertPlan($slug, array $data)
    {
        $plan = Plan::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => 'active',
                'is_public' => true,
                'sort' => $data['sort'],
            ]
        );
        $plan->refresh();

        // Rebuild the bundle wholesale so re-seeding stays idempotent.
        $plan->prices()->delete();
        $plan->features()->delete();
        $plan->permissions()->delete();
        $plan->limits()->delete();

        $plan->prices()->create([
            'interval' => 'month',
            'interval_count' => 1,
            'unit_amount' => (int) $data['price'] * 100,
            'currency' => config('billing.currency', 'usd'),
            'active' => true,
        ]);

        foreach (array_unique($data['modules']) as $moduleName) {
            $moduleId = PermissionModule::where('name', $moduleName)
                ->where('is_platform', false)
                ->value('id');

            if ($moduleId) {
                $plan->features()->create([
                    'permission_module_id' => $moduleId,
                ]);
            }
        }

        foreach ($data['limits'] as $key => $value) {
            $plan->limits()->create([
                'resource_key' => $key,
                'limit_value' => (int) $value,
            ]);
        }

        return $plan->refresh();
    }
}
