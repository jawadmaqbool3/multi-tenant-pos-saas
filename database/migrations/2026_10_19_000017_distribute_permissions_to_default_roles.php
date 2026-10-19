<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\RBAC\Support\DefaultRoles;

class DistributePermissionsToDefaultRoles extends Migration
{
    /**
     * Re-distribute the global tenant permissions across each company's default
     * sub-roles from the canonical DefaultRoles map. This closes the gaps where
     * a module's permissions (suppliers, promotions, customer/attendance
     * dashboard widgets) were not attached to their target role.
     *
     * Additive and idempotent: existing mappings are never removed; the owner
     * ('company') role already holds every tenant permission and is left as is.
     * Data migration: direct DB access is intentional.
     *
     * @return void
     */
    public function up()
    {
        $globalPermissionIds = DB::table('permissions')
            ->whereNull('company_id')
            ->where('is_platform', false)
            ->pluck('id', 'name');

        foreach (DB::table('companies')->pluck('id') as $companyId) {
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
    }

    /**
     * Purely additive distribution; nothing is removed on rollback.
     *
     * @return void
     */
    public function down()
    {
        //
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
}
