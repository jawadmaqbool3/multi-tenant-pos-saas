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
        $superAdmin = User::where('email', 'admin@gmail.com')->first();

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
                'asidebar' => $global->asidebar ?? '[]',
            ]
        );

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'company_id' => $company->id,
        ]);
        $adminRole->refresh();

        foreach (Permission::where('company_id', null)->get() as $permission) {
            $companyPermission = Permission::firstOrCreate([
                'name' => $permission->name,
                'module_id' => $permission->module_id,
                'company_id' => $company->id,
            ]);
            $companyPermission->refresh();
            PermissionRole::firstOrCreate([
                'role_id' => $adminRole->id,
                'permission_id' => $companyPermission->id,
            ]);
        }

        $owner = User::where('email', 'user@gmail.com')->first();
        if ($owner) {
            $owner->company_id = $company->id;
            $owner->save();

            UserRole::firstOrCreate([
                'user_id' => $owner->id,
                'role_id' => $adminRole->id,
            ]);
        }

        $defaults = app(DefaultService::class);
        $defaults->initiateAccounts($company);
        $defaults->initiateVouchers($company);
        $defaults->initiateAccountSettings($company);
    }
}
