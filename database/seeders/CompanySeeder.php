<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Setting;
use App\Services\DefaultService;
use App\Support\DefaultAccounts;
use Modules\Employee\Models\User;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;
use Modules\RBAC\Services\RoleProvisioningService;
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
                'logo' => $global->logo ?? 'logo.jpeg',
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

        // Provision every default role for this company and attach the shared
        // global permissions to each role (permissions are not copied per
        // company; they are global and immutable).
        app(RoleProvisioningService::class)->provisionForCompany($company->id);

        // Assign each default account to its company role.
        foreach (DefaultAccounts::roleAccounts() as $email => $roleName) {
            $user = User::where('email', $email)->first();
            $role = Role::where('company_id', $company->id)->where('name', $roleName)->first();

            if (!$user || !$role) {
                continue;
            }

            $user->company_id = $company->id;
            $user->save();

            UserRole::firstOrCreate([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }

        $defaults = app(DefaultService::class);
        $defaults->initiateAccounts($company);
        $defaults->initiateVouchers($company);
        $defaults->initiateAccountSettings($company);
    }
}
