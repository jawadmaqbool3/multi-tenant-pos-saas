<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\VoucherTypeRepository;

class DefaultVoucherTypeService
{
    protected $voucherTypes;
    protected $defaultVoucherTypes;

    public function __construct(VoucherTypeRepository $voucherTypes)
    {
        $this->voucherTypes = $voucherTypes;

        $this->defaultVoucherTypes = $this->getDefaultVoucherTypes();
    }

    public function initiateVouchers($company = null)
    {
        $companyId = $company ? $company->id : null;
        foreach ($this->defaultVoucherTypes as $key => $voucherType) {
            $exists = $this->voucherTypes->query()
                ->where('company_id', $companyId)
                ->where('short', $voucherType['short'])
                ->exists();
            if ($exists) {
                continue;
            }
            $voucherType['company_id'] = $companyId;
            $this->voucherTypes->create($voucherType);
        }
    }

    protected function getDefaultVoucherTypes()
    {
        return [
            [
                "short" => "BRV",
                "name" => "Bank Receipt Voucher",
                "credit_level" => "level4",
                "debit_level" => "level4",
                "description" => "BRV voucher is used to transfer amount from recievable to Bank(Invoice Payment Case)",
            ],
            [
                "short" => "CRV",
                "name" => "Cash Receipt Voucher",
                "credit_level" => "level4",
                "debit_level" => "level4",
                "description" => "CRV voucher is used to transfer amount from recievable to Cash in hand(Invoice Payment Case)",
            ],
            [
                "short" => "APV",
                "name" => "Account Payable Voucher",
                "credit_level" => "level4",
                "debit_level" => "level4",
                "description" => "APV voucher is used to mark liabilty and expense(Add Stock Case)",
            ],
            [
                "short" => "INV",
                "name" => "Invoice Voucher",
                "credit_level" => "all",
                "debit_level" => "all",
                "description" => "INV voucher is deals with Recievable == Revenue and Asset == Expense(Invoice Case)",
            ],
            [
                "short" => "BPV",
                "name" => "Bank Payment Voucher",
                "credit_level" => "all",
                "debit_level" => "all",
                "description" => "BPV voucher is deals with Payable == Asset (Supplier Payment Case)",
            ],
            [
                "short" => "SLV",
                "name" => "Salary Voucher",
                "credit_level" => "level4",
                "debit_level" => "level4",
                "description" => "SLV voucher records monthly payroll: salary expense, deductions, and the net payment to the office account.",
            ],
        ];
    }
}
