<?php

namespace Modules\ChartOfAccounts\Services;

use App\Exceptions\AccountSettingNotSetException;
use App\Exceptions\AccountSettingNotFoundException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\MissingFieldException;
use Modules\ChartOfAccounts\Repositories\AccountSettingRepository;
use Modules\ChartOfAccounts\Repositories\AccountLevel1Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel2Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel3Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel4Repository;

class AccountSettingService
{
    protected $setting;
    protected $level1;
    protected $level2;
    protected $level3;
    protected $level4;

    public function __construct(
        AccountSettingRepository $setting,
        AccountLevel1Repository $level1,
        AccountLevel2Repository $level2,
        AccountLevel3Repository $level3,
        AccountLevel4Repository $level4
    ) {
        $this->setting = $setting;
        $this->level1 = $level1;
        $this->level2 = $level2;
        $this->level3 = $level3;
        $this->level4 = $level4;
    }

    public function getAccountSettings($company = null)
    {
        if ($company) {
            return $this->setting->query()->where('company_id', $company->id)->get();
        }
        return $this->setting->query()->filter()->get();
    }

    public function getAccounts()
    {
        return $this->level1->query()->filter()->get();
    }

    public function update($request)
    {
        if (!is_array($request->settings) || count($request->settings) === 0) {
            throw new MissingFieldException('settings');
        }
        foreach ($request->settings as $key => $setting) {

            $_setting = $this->setting->query()->where('uid', $setting["setting"])->first();
            if (!$_setting) {
                throw new InvalidFieldException('settings');
            }

            $level = $setting["level"];
            $account = null;
            switch ($level) {
                case 'level1':
                    $account = $this->level1->query()->where('uid', $setting['account'])->first();
                    break;
                case 'level2':
                    $account = $this->level2->query()->where('uid', $setting['account'])->first();
                    break;
                case 'level3':
                    $account = $this->level3->query()->where('uid', $setting['account'])->first();
                    break;
                case 'level4':
                    $account = $this->level4->query()->where('uid', $setting['account'])->first();
                    break;
            }
            $this->setting->query()->where('id', $_setting->id)->update([
                "level" => $setting["level"],
                "account_id" => $account ? $account->id : null
            ]);
        }
    }

    public function initiateAccountSettings($company = null)
    {
        $companyId = $company ? $company->id : null;
        foreach ($this->getDefaultAccountSettings() as $setting) {
            $exists = $this->setting->query()
                ->where('company_id', $companyId)
                ->where('short', $setting['short'])
                ->exists();
            if ($exists) {
                continue;
            }
            $account = null;
            switch ($setting["level"]) {
                case 'level1':
                    $account = $this->level1->query()->where('company_id', $company ? $company->id : null)->where('name', $setting["account"])->first();
                    break;
                case 'level2':
                    $account = $this->level2->query()->where('company_id', $company ? $company->id : null)->where('name', $setting["account"])->first();
                    break;
                case 'level3':
                    $account = $this->level3->query()->where('company_id', $company ? $company->id : null)->where('name', $setting["account"])->first();
                    break;
                case 'level4':
                    $account = $this->level4->query()->where('company_id', $company ? $company->id : null)->where('name', $setting["account"])->first();
                    break;
            }
            $this->setting->create([
                "name" => $setting["name"],
                "company_id" => $companyId,
                "short" => $setting["short"],
                "account_id" => $account ? $account->id : null,
                "level" => $setting["level"],
            ]);
        }
    }

    protected function getDefaultAccountSettings()
    {
        return [
            ["name" => "Sales Recievable Account", "short" => "SRA", "account" => "Payment Recievable", "level" => "level4"],
            ["name" => "Sales Person Revenue Account", "short" => "SPRA", "account" => "Sales Person", "level" => "level4"],
            ["name" => "Product Inventory Account", "short" => "PIA", "account" => "Products", "level" => "level4"],
            ["name" => "Purchase Expense Account", "short" => "PEA", "account" => "Vendor Payments", "level" => "level4"],
            ["name" => "Office Sale Account", "short" => "OSA", "account" => "Office Account", "level" => "level4"],
            ["name" => "Vendor Payable Account", "short" => "VPA", "account" => "Vendor Payable", "level" => "level4"],
            ["name" => "Supplier Payment Account", "short" => "SPA", "account" => "Office Account", "level" => "level4"],
            ["name" => "Salary Expense Account", "short" => "SEA", "account" => "Salary Expense", "level" => "level4"],
            ["name" => "Salary Deductions Payable", "short" => "SDP", "account" => "Salary Deductions Payable", "level" => "level4"],
        ];
    }

    public function setAccountBalance($accountId, $balance)
    {
        return $this->level4->query()->where('id', $accountId)->update(['balance' => $balance]);
    }

    public function getAccount($short)
    {
        $setting = $this->setting->query()->where('company_id', auth()->user()->company_id)->where('short', $short)->first();
        if (!$setting) {
            throw new AccountSettingNotFoundException($short);
        }
        $account = $setting->account;
        if (!$account) {
            throw new AccountSettingNotSetException($setting->name);
        }
        return $account;
    }
}
