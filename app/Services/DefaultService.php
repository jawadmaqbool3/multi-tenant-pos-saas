<?php

namespace App\Services;

class DefaultService
{
    protected $accounts;
    protected $vouchers;
    protected $accountSettings;

    public function __construct(
        DefaultAccountService $accounts,
        DefaultVoucherTypeService $vouchers,
        AccountSettingService $accountSettings
    ) {
        $this->accounts = $accounts;
        $this->vouchers = $vouchers;
        $this->accountSettings = $accountSettings;
    }

    public function initiateAccounts($company = null)
    {
        return $this->accounts->initiateAccounts($company);
    }

    public function initiateVouchers($company = null)
    {
        return $this->vouchers->initiateVouchers($company);
    }

    public function initiateAccountSettings($company = null)
    {
        return $this->accountSettings->initiateAccountSettings($company);
    }
}
