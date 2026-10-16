<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RBAC\Support\DefaultRoles;

class ProvisionDashboardWidgetPermissions extends Migration
{
    /**
     * Tenant "Dashboard" permissions introduced with the dashboard widget system
     * (the existing migrated widgets reuse the already-seeded permissions).
     *
     * @var array<int, string>
     */
    protected $tenantPermissions = [
        'dashboard current plan',
        'dashboard subscription renewal',
        'dashboard subscription usage',
        'dashboard my invoices',
    ];

    /**
     * Platform "Billing" permissions introduced with the platform widgets.
     *
     * @var array<int, string>
     */
    protected $platformPermissions = [
        'dashboard stripe balance',
        'dashboard due payments',
        'dashboard active companies',
    ];

    /**
     * Data migration: direct DB access is intentional (mirrors the payroll
     * provisioning migration). Permissions stay global (company_id = null).
     *
     * @return void
     */
    public function up()
    {
        $tenantModuleId = $this->ensureModule('Dashboard', false);
        $tenantPermissionIds = $this->ensurePermissions($this->tenantPermissions, $tenantModuleId, false);

        $platformModuleId = $this->ensureModule('Billing', true);
        $platformPermissionIds = $this->ensurePermissions($this->platformPermissions, $platformModuleId, true);

        // Owner role of every company: all global tenant permissions (additive).
        foreach (DB::table('companies')->get() as $company) {
            $ownerRoleId = $this->ownerRoleId($company->id);
            if ($ownerRoleId) {
                $this->grantRole($ownerRoleId, $tenantPermissionIds);
            }
        }

        // Default sub-roles: re-attach their DefaultRoles subsets (additive,
        // idempotent). Unknown permission names are skipped by DefaultRoles match.
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $this->syncDefaultRoles($companyId);
        }

        // Platform 'admin' role: all global platform permissions (additive).
        $platformAdminRoleId = DB::table('roles')
            ->whereNull('company_id')
            ->where('name', 'admin')
            ->value('id');

        if ($platformAdminRoleId) {
            $this->grantRole($platformAdminRoleId, $platformPermissionIds);
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->removePermissions($this->tenantPermissions, 'Dashboard', false);
        $this->removePermissions($this->platformPermissions, 'Billing', true);
    }

    /**
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
     * @return array<int, int>
     */
    protected function ensurePermissions(array $names, $moduleId, $isPlatform)
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
                'is_platform' => $isPlatform,
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

    /**
     * @param  array<int, string>  $names
     * @return void
     */
    protected function removePermissions(array $names, $moduleName, $isPlatform)
    {
        $module = DB::table('permission_modules')
            ->where('name', $moduleName)
            ->where('is_platform', $isPlatform)
            ->first();

        if (!$module) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $names)
            ->where('module_id', $module->id)
            ->whereNull('company_id')
            ->pluck('id')
            ->all();

        if (!empty($permissionIds)) {
            DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }
}
