<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameSuperadminRoleToAdmin extends Migration
{
    /**
     * Rename the platform role `superadmin` -> `admin` and the tenant owner
     * role `admin` -> `company`.
     *
     * Ordering matters: the tenant roles are renamed first so the newly
     * renamed platform role cannot collide with a company-scoped role. The
     * `roles.name` column has no unique constraint, so the intermediate state
     * is safe.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->where('name', 'admin')->whereNotNull('company_id')
            ->update(['name' => 'company']);

        $platform = DB::table('roles')->where('name', 'superadmin')->whereNull('company_id')->first();
        if ($platform) {
            $existing = DB::table('roles')->where('name', 'admin')->whereNull('company_id')->first();
            if ($existing) {
                $this->mergeRole($platform->id, $existing->id);
                DB::table('roles')->where('id', $platform->id)->delete();
            } else {
                DB::table('roles')->where('id', $platform->id)->update(['name' => 'admin']);
            }
        }

        // Legacy global `admin` (company_id null) was created by the old seeder
        // as a permission-less template alongside `superadmin`. Remove any such
        // duplicate, keeping the platform admin that actually holds permissions.
        $admins = DB::table('roles')->where('name', 'admin')->whereNull('company_id')->get();
        if ($admins->count() > 1) {
            $keeper = $admins->first(function ($role) {
                return DB::table('permission_roles')->where('role_id', $role->id)->exists();
            }) ?: $admins->first();
            foreach ($admins as $role) {
                if ($role->id === $keeper->id) {
                    continue;
                }
                $this->mergeRole($role->id, $keeper->id);
                DB::table('roles')->where('id', $role->id)->delete();
            }
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::table('roles')->where('name', 'admin')->whereNull('company_id')
            ->update(['name' => 'superadmin']);
        DB::table('roles')->where('name', 'company')->whereNotNull('company_id')
            ->update(['name' => 'admin']);
    }

    /**
     * Move user_roles and permission_roles from one role to another, dropping
     * duplicates.
     *
     * @param  int  $fromId  source role id
     * @param  int  $toId    destination role id
     * @return void
     */
    protected function mergeRole($fromId, $toId)
    {
        foreach (DB::table('user_roles')->where('role_id', $fromId)->get() as $mapping) {
            $exists = DB::table('user_roles')
                ->where('user_id', $mapping->user_id)
                ->where('user_type', $mapping->user_type)
                ->where('role_id', $toId)
                ->exists();
            if (!$exists) {
                DB::table('user_roles')->insert([
                    'user_id' => $mapping->user_id,
                    'user_type' => $mapping->user_type,
                    'role_id' => $toId,
                ]);
            }
            DB::table('user_roles')
                ->where('user_id', $mapping->user_id)
                ->where('user_type', $mapping->user_type)
                ->where('role_id', $fromId)
                ->delete();
        }

        foreach (DB::table('permission_roles')->where('role_id', $fromId)->get() as $mapping) {
            $exists = DB::table('permission_roles')
                ->where('role_id', $toId)
                ->where('permission_id', $mapping->permission_id)
                ->exists();
            if (!$exists) {
                DB::table('permission_roles')->insert([
                    'role_id' => $toId,
                    'permission_id' => $mapping->permission_id,
                ]);
            }
            DB::table('permission_roles')
                ->where('role_id', $fromId)
                ->where('permission_id', $mapping->permission_id)
                ->delete();
        }
    }
}