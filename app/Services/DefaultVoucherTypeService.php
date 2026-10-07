<?php

namespace App\Services;

use App\Repositories\VoucherTypeRepository;

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
        foreach ($this->defaultVoucherTypes as $key => $voucherType) {
            $voucherType['company_id'] = $company ? $company->id : null;
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
        ];
    }
}
