<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\PlanFeature;
use Modules\RBAC\Models\PermissionModule;

class AddPayrollModuleToProAndEnterprisePlans extends Migration
{
    /**
     * Payroll is a plan-gated module but no plan included it, which hid the
     * Accountant sidebar section and returned 403 on the payroll routes. The
     * feature belongs to the paid tiers, so attach it to Pro and Enterprise.
     *
     * @return void
     */
    public function up()
    {
        $module = PermissionModule::where('name', 'Payroll')->first();

        if (!$module) {
            return;
        }

        $plans = Plan::whereIn('name', ['Pro', 'Enterprise'])->get();

        foreach ($plans as $plan) {
            $exists = PlanFeature::where('plan_id', $plan->id)
                ->where('permission_module_id', $module->id)
                ->exists();

            if ($exists) {
                continue;
            }

            PlanFeature::create([
                'plan_id' => $plan->id,
                'permission_module_id' => $module->id,
            ]);
        }
    }

    /**
     * Not reversible — removing the module could silently lock companies out
     * of payroll. Adjust the plan features from Billing instead.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
