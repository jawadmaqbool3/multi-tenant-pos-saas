<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Setting;
use App\Services\DefaultService;
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

        // Provision every default role for this company and attach the shared
        // global permissions to each role (permissions are not copied per
        // company; they are global and immutable).
        app(RoleProvisioningService::class)->provisionForCompany($company->id);

        $companyRole = Role::firstOrCreate([
            'name' => 'company',
            'company_id' => $company->id,
        ], [
            'status' => 'active',
        ]);
        $companyRole->refresh();

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
