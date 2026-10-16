<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Setting;
use App\Services\DefaultService;
use Modules\Employee\Models\User;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\PermissionRole;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = User::where('email', 'superadmin@gmail.com')->first();

        $company = Company::firstOrCreate(
            ['created_by' => $superAdmin ? $superAdmin->id : 1],
            ['status' => 'active']
        );
        $company->refresh();

        $global = Setting::where('company_id', null)->first();
        Setting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'logo' => $global->logo ?? 'logo.svg',
                'name' => $global->name ?? 'POS',
                'email' => $global->email ?? null,
                'address' => $global->address ?? null,
                'url' => $global->url ?? null,
                'facebook' => $global->facebook ?? null,
                'instagram' => $global->instagram ?? null,
                'whatsapp' => $global->whatsapp ?? null,
                'contact' => $global->contact ?? null,
                'asidebar' => json_encode(app(\App\Services\AsideSettingService::class)->getTenantMenuLinks()),
            ]
        );

        $companyRole = Role::firstOrCreate([
            'name' => 'company',
            'company_id' => $company->id,
        ], [
            'status' => 'active',
        ]);
        $companyRole->refresh();

        foreach (Permission::where('company_id', null)->where('is_platform', false)->get() as $permission) {
            $companyPermission = Permission::firstOrCreate([
                'name' => $permission->name,
                'module_id' => $permission->module_id,
                'company_id' => $company->id,
            ], [
                'is_platform' => false,
            ]);
            $companyPermission->refresh();
            PermissionRole::firstOrCreate([
                'role_id' => $companyRole->id,
                'permission_id' => $companyPermission->id,
            ]);
        }

        // Provision the default tenant roles (employee, HR, accountant, sales
        // person, stock manager) with their permission subsets.
        foreach ((new RolePermissionSeeder())->tenantRoles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'company_id' => $company->id,
            ], [
                'status' => 'active',
            ]);
            $role->refresh();

            foreach ($permissionNames as $permissionName) {
                $permission = Permission::where('name', $permissionName)
                    ->where('company_id', $company->id)
                    ->first();

                if ($permission) {
                    PermissionRole::firstOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }

        $owner = User::where('email', 'admin@gmail.com')->first();
        if ($owner) {
            $owner->company_id = $company->id;
            $owner->save();

            UserRole::firstOrCreate([
                'user_id' => $owner->id,
                'role_id' => $companyRole->id,
            ]);
        }

        $defaults = app(DefaultService::class);
        $defaults->initiateAccounts($company);
        $defaults->initiateVouchers($company);
        $defaults->initiateAccountSettings($company);
    }
}
