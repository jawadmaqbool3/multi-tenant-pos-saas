<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvisionPayrollPermissions extends Migration
{
    /**
     * Tenant permissions introduced with the Payroll module.
     *
     * @var array<int, string>
     */
    protected $permissions = [
        'view payroll',
        'create payroll',
        'edit payroll',
        'delete payroll',
        'view allowance',
        'create allowance',
        'edit allowance',
        'delete allowance',
        'view deduction',
        'create deduction',
        'edit deduction',
        'delete deduction',
    ];

    /**
     * Provision the Payroll permission module + global template permissions,
     * then grant the global permissions to every existing company's owner role.
     * Permissions are global and are never copied per company.
     * Data migration: direct DB access is intentional.
     *
     * @return void
     */
    public function up()
    {
        $moduleId = $this->ensureTemplateModule();
        $templatePermissionIds = $this->ensureTemplatePermissions($moduleId);

        foreach (DB::table('companies')->get() as $company) {
            $ownerRoleId = $this->ownerRoleId($company->id);
            if ($ownerRoleId) {
                $this->grantRole($ownerRoleId, $templatePermissionIds);
            }
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        $module = DB::table('permission_modules')->where('name', 'Payroll')->where('is_platform', false)->first();
        if (!$module) {
            return;
        }

        $permissionIds = DB::table('permissions')->where('module_id', $module->id)->pluck('id')->all();

        if (!empty($permissionIds)) {
            DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }

        DB::table('permission_modules')->where('id', $module->id)->delete();
    }

    /**
     * @return int
     */
    protected function ensureTemplateModule()
    {
        $existing = DB::table('permission_modules')->where('name', 'Payroll')->first();
        if ($existing) {
            if ($existing->is_platform) {
                DB::table('permission_modules')->where('id', $existing->id)->update(['is_platform' => false]);
            }
            return $existing->id;
        }

        return DB::table('permission_modules')->insertGetId([
            'name' => 'Payroll',
            'is_platform' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  int  $moduleId
     * @return array<int, int>
     */
    protected function ensureTemplatePermissions($moduleId)
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
        $ownerUserId = DB::table('users')->where('company_id', $companyId)->orderBy('id')->value('id');
        if (!$ownerUserId) {
            return null;
        }

        return DB::table('user_roles')->where('user_id', $ownerUserId)->value('role_id');
    }
}
