<?php

use Illuminate\Database\Migrations\Migration;

class ProvisionPayrollCoa extends Migration
{
    /**
     * Backfill the payroll COA for existing companies (new companies get it via
     * CompanyService/seeders). Idempotent — each service firstOrCreates/skips
     * existing rows.
     *
     * @return void
     */
    public function up()
    {
        $defaultAccounts = app(\Modules\ChartOfAccounts\Services\DefaultAccountService::class);
        $accountSettings = app(\Modules\ChartOfAccounts\Services\AccountSettingService::class);
        $voucherTypes = app(\Modules\ChartOfAccounts\Services\DefaultVoucherTypeService::class);

        // Global/demo tree (company_id = null) first, then every company.
        $defaultAccounts->provisionPayrollAccounts(null);
        $accountSettings->initiateAccountSettings(null);
        $voucherTypes->initiateVouchers(null);

        foreach (\Illuminate\Support\Facades\DB::table('companies')->get() as $company) {
            $defaultAccounts->provisionPayrollAccounts($company);
            $accountSettings->initiateAccountSettings($company);
            $voucherTypes->initiateVouchers($company);
        }
    }

    /**
     * The payroll accounts/settings/voucher types are provisioning data with no
     * safe automatic reversal; leave them in place.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}