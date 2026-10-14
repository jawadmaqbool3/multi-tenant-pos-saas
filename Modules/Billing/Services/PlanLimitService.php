<?php

namespace Modules\Billing\Services;

use App\Exceptions\PlanLimitExceededException;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Plan;
use Modules\Employee\Models\User;
use Modules\RBAC\Models\Role;

class PlanLimitService
{
    /**
     * A limit value below zero (e.g. -1) means "unlimited".
     */
    const UNLIMITED = -1;

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

        $subscription = $company->activeSubscription();

        if (!$subscription) {
            return null;
        }

        return $subscription->plan()->first();
    }

    /**
     * The numeric limit for a resource key, or null when unlimited/not set.
     *
     * @param  string  $key
     * @param  \Modules\Billing\Models\Plan|null  $plan
     * @return int|null
     */
    public function limitFor(string $key, ?Plan $plan): ?int
    {
        if (!$plan) {
            return null;
        }

        $limit = $plan->limits()->where('resource_key', $key)->first();

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
            case 'roles':
                return $this->rolesUsage($company);
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
     * Employees are company users assigned the "employee" role.
     *
     * @param  \App\Models\Company  $company
     * @return int
     */
    protected function employeesUsage(Company $company): int
    {
        return User::where('company_id', $company->id)
            ->whereHas('role', function ($query) {
                $query->where('name', 'employee');
            })
            ->count();
    }

    /**
     * Company-scoped roles, excluding the provisioned owner role.
     *
     * @param  \App\Models\Company  $company
     * @return int
     */
    protected function rolesUsage(Company $company): int
    {
        $query = Role::where('company_id', $company->id);

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
        $ownerUserId = User::where('company_id', $company->id)->orderBy('id')->value('id');

        if (!$ownerUserId) {
            return null;
        }

        return DB::table('user_roles')->where('user_id', $ownerUserId)->value('role_id');
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
