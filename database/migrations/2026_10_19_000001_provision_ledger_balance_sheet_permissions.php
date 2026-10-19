<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RBAC\Support\DefaultRoles;

class ProvisionLedgerBalanceSheetPermissions extends Migration
{
    /**
     * Tenant permissions introduced with the Ledger and Balance Sheet reports.
     *
     * @var array<int, string>
     */
    protected $permissions = [
        'view ledger',
        'view balance sheet',
    ];

    /**
     * Provision the Ledger / Balance Sheet permissions under the existing
     * Accounts permission module, then grant them to every existing company's
     * owner role and re-sync the default sub-roles from DefaultRoles.
     * Permissions stay global (company_id = null).
     * Data migration: direct DB access is intentional.
     *
     * @return void
     */
    public function up()
    {
        $moduleId = $this->ensureModule();
        $permissionIds = $this->ensurePermissions($moduleId);

        foreach (DB::table('companies')->get() as $company) {
            $ownerRoleId = $this->ownerRoleId($company->id);
            if ($ownerRoleId) {
                $this->grantRole($ownerRoleId, $permissionIds);
            }
        }

        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $this->syncDefaultRoles($companyId);
        }

        $platformAdminRoleId = DB::table('roles')
            ->whereNull('company_id')
            ->where('name', 'admin')
            ->value('id');

        if ($platformAdminRoleId) {
            $this->grantRole($platformAdminRoleId, $permissionIds);
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        $module = DB::table('permission_modules')->where('name', 'Accounts')->first();

        if (!$module) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->permissions)
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
     * @return int
     */
    protected function ensureModule()
    {
        $existing = DB::table('permission_modules')->where('name', 'Accounts')->first();

        if ($existing) {
            if ($existing->is_platform) {
                DB::table('permission_modules')->where('id', $existing->id)->update(['is_platform' => false]);
            }

            return $existing->id;
        }

        return DB::table('permission_modules')->insertGetId([
            'name' => 'Accounts',
            'is_platform' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  int  $moduleId
     * @return array<int, int>
     */
    protected function ensurePermissions($moduleId)
    {
        $ids = [];

        foreach ($this->permissions as $name) {
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
