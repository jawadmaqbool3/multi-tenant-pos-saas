<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RBAC\Support\DefaultRoles;

class ProvisionModuleDashboardPermissions extends Migration
{
    /**
     * Tenant "Dashboard" permissions introduced with the Phase 2 module widgets
     * (attendance, payroll, inventory, finance, supplier). The existing widgets
     * reuse the already-seeded permissions.
     *
     * @var array<int, string>
     */
    protected $tenantPermissions = [
        // attendance
        'dashboard attendance summary',
        'dashboard attendance monthly',
        'dashboard attendance absent today',
        'dashboard my attendance',
        // payroll
        'dashboard payroll pending',
        'dashboard payroll due',
        'dashboard payroll paid month',
        'dashboard payroll cost',
        'dashboard payroll recent',
        'dashboard my payslips',
        // inventory
        'dashboard stock low',
        'dashboard stock out',
        'dashboard stock value',
        // finance
        'dashboard revenue expense',
        'dashboard receivables',
        'dashboard payables',
        // supplier
        'dashboard supplier pending orders',
        'dashboard supplier payable',
    ];

    /**
     * Data migration: direct DB access is intentional (mirrors the payroll and
     * dashboard provisioning migrations). Permissions stay global
     * (company_id = null).
     *
     * @return void
     */
    public function up()
    {
        $moduleId = $this->ensureModule('Dashboard', false);
        $permissionIds = $this->ensurePermissions($this->tenantPermissions, $moduleId);

        // Owner role of every company: all global tenant permissions (additive).
        foreach (DB::table('companies')->get() as $company) {
            $ownerRoleId = $this->ownerRoleId($company->id);
            if ($ownerRoleId) {
                $this->grantRole($ownerRoleId, $permissionIds);
            }
        }

        // Default sub-roles: re-attach their DefaultRoles subsets (additive,
        // idempotent). Unknown permission names are skipped by DefaultRoles match.
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $this->syncDefaultRoles($companyId);
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        $module = DB::table('permission_modules')
            ->where('name', 'Dashboard')
            ->where('is_platform', false)
            ->first();

        if (!$module) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->tenantPermissions)
            ->where('module_id', $module->id)
            ->whereNull('company_id')
            ->pluck('id')
            ->all();

        if (!empty($permissionIds)) {
            DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    /**
     * @param  string  $name
     * @param  bool  $isPlatform
     * @return int
     */
    protected function ensureModule($name, $isPlatform)
    {
        $existing = DB::table('permission_modules')
            ->where('name', $name)
            ->where('is_platform', $isPlatform)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        return DB::table('permission_modules')->insertGetId([
            'name' => $name,
            'is_platform' => $isPlatform,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<int, string>  $names
     * @param  int  $moduleId
     * @return array<int, int>
     */
    protected function ensurePermissions(array $names, $moduleId)
    {
        $ids = [];

        foreach ($names as $name) {
            $existing = DB::table('permissions')
                ->where('name', $name)
                ->where('module_id', $moduleId)
                ->whereNull('company_id')
                ->first();

            if ($existing) {
                $ids[] = $existing->id;
                continue;
            }

            $ids[] = DB::table('permissions')->insertGetId([
                'name' => $name,
                'module_id' => $moduleId,
                'company_id' => null,
                'is_platform' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'uid' => (string) Str::uuid(),
            ]);
        }

        return $ids;
    }

    /**
     * Additive re-sync of a company's default sub-roles from DefaultRoles.
     *
     * @param  int  $companyId
     * @return void
     */
    protected function syncDefaultRoles($companyId)
    {
        $globalPermissionIds = DB::table('permissions')
            ->whereNull('company_id')
            ->where('is_platform', false)
            ->pluck('id', 'name');

        foreach (DefaultRoles::roles() as $roleName => $permissionNames) {
            $roleId = DB::table('roles')
                ->where('company_id', $companyId)
                ->where('name', $roleName)
                ->value('id');

            if (!$roleId) {
                continue;
            }

            $toAttach = [];
            foreach ($permissionNames as $permissionName) {
                if (isset($globalPermissionIds[$permissionName])) {
                    $toAttach[] = $globalPermissionIds[$permissionName];
                }
            }

            $this->grantRole($roleId, $toAttach);
        }
    }

    /**
     * Attach permissions to a role, skipping existing mappings.
     *
     * @param  int  $roleId
     * @param  array<int, int>  $permissionIds
     * @return void
     */
    protected function grantRole($roleId, array $permissionIds)
    {
        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('permission_roles')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {
                DB::table('permission_roles')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    /**
     * Role of the company's earliest (owner) user.
     *
     * @param  int  $companyId
     * @return int|null
     */
    protected function ownerRoleId($companyId)
    {
        $ownerUserId = DB::table('users')
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->value('id');

        if (!$ownerUserId) {
            return null;
        }

        return DB::table('user_roles')->where('user_id', $ownerUserId)->value('role_id');
    }
}
