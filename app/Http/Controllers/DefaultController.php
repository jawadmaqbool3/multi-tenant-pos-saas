<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AccountLevel1;
use App\Models\AccountLevel2;
use App\Models\AccountLevel3;
use App\Models\AccountLevel4;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\VoucherType;

class DefaultController extends Controller
{
    public $defaultAccounts;
    public $defaultVoucherTypes;
    public $defaultAccountSettings;

    public function initiateAccounts(Company $company = null)
    {
        foreach ($this->defaultAccounts as $key => $account) {
            $level1 = AccountLevel1::create([
                "name" => $account['name'],
                "status" => $account['status'],
                "start_code" => $account['start_code'],
                "end_code" => $account['end_code'],
                "company_id" => $company ? $company->id : null,
            ]);
            $level1->refresh();
            if (@$account["level2"]) {
                foreach ($account["level2"] as $key => $level2) {
                    $_level2 = AccountLevel2::create([
                        "name" => $level2["name"],
                        "status" => $level2["status"],
                        "code" => $level2["code"],
                        "parent_id" => $level1->id,
                        "company_id" => $company ? $company->id : null,
                    ]);
                    $_level2->refresh();
                    if (@$level2["level3"]) {
                        foreach ($level2["level3"] as $key => $level3) {
                            $_level3 = AccountLevel3::create([
                                "name" => $level3["name"],
                                "status" => $level3["status"],
                                "code" => $level3["code"],
                                "parent_id" => $_level2->id,
                                "company_id" => $company ? $company->id : null,
                            ]);
                            $_level3->refresh();
                            if (@$level3["level4"]) {
                                foreach ($level3["level4"] as $key => $level4) {
                                    $_level3 = AccountLevel4::create([
                                        "name" => $level4["name"],
                                        "status" => $level4["status"],
                                        "code" => $level4["code"],
                                        "parent_id" => $_level3->id,
                                        "company_id" => $company ? $company->id : null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function initiateVouchers(Company $company = null)
    {
        foreach ($this->defaultVoucherTypes as $key => $voucherType) {
            $voucherType['company_id'] = $company ? $company->id : null;
            VoucherType::create($voucherType);
        }
    }

    public function initiateAccountSettings(Company $company = null)
    {
        foreach ($this->defaultAccountSettings as $key => $setting) {
            switch ($setting["level"]) {
                case 'level1':
                    $account =  new AccountLevel1();
                    break;
                case 'level2':
                    $account =  new AccountLevel2();
                    break;
                case 'level3':
                    $account =  new AccountLevel3();
                    break;
                case 'level4':
                    $account =  new AccountLevel4();
                    break;
            }
            $account = $account->where('company_id', $company ? $company->id : null)->where('name', $setting["account"])->first();
            AccountSetting::create([
                "name" => $setting["name"],
                "company_id" => $company ? $company->id : null,
                "short" => $setting["short"],
                "account_id" => $account->id,
                "level" => $setting["level"],
            ]);
        }
    }

    

    public function __construct()
    {
        $this->defaultAccounts =  [
            [
                "name" => "Asset",
                "status" => 'active',
                "start_code" => "000",
                "end_code" => "099",
                "level2" => [
                    [
                        "name" => "Current Asset",
                        "status" => "active",
                        "code" => "001",
                        "level3" =>
                        [
                            [
                                "name" => "Bank",
                                "status" => "active",
                                "code" => "001-001",
                                "level4" => [
                                    [
                                        "name" => "Online Gateway",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "001-001-001",
                                    ],
                                ]
                            ],
                            [
                                "name" => "Cash in Hand",
                                "status" => "active",
                                "code" => "001-002",
                                "level4" => [
                                    [
                                        "name" => "Office Account",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "001-002-001",
                                    ],
                                ]
                            ],

                        ]
                    ],
                    [
                        "name" => "Non Current Asset",
                        "status" => "active",
                        "code" => "002",
                        "level3" => [
                            [
                                "name" => "Recievables",
                                "status" => "active",
                                "code" => "002-001",
                                "level4" => [
                                    [
                                        "name" => "Payment Recievable",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "002-001-001",
                                    ],
                                ]
                            ],
                            [
                                "name" => "Inventory",
                                "status" => "active",
                                "code" => "002-002",
                                "level4" => [
                                    [
                                        "name" => "Products",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "002-002-001",
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            [
                "name" => "Liability",
                "status" => 'active',
                "start_code" => "100",
                "end_code" => "199",
                "level2" => [
                    [
                        "name" => "Current Liability",
                        "status" => "active",
                        "code" => "101",
                        "level3" =>
                        [
                            [
                                "id" => 5,
                                "name" => "Payables",
                                "status" => "active",
                                "parent_id" => 3,
                                "code" => "101-001",
                                "level4" => [
                                    [
                                        "name" => "Vendor Payable",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "101-001-001",
                                    ],
                                ]
                            ],
                        ]
                    ],
                    [
                        "name" => "Non Current Liability",
                        "status" => "active",
                        "code" => "102",
                    ],
                ]
            ],
            [
                "name" => "Equity",
                "status" => 'active',
                "start_code" => "200",
                "end_code" => "299",
            ],
            [
                "name" => "Revenue",
                "status" => 'active',
                "start_code" => "300",
                "end_code" => "399",
                "level2" => [
                    [
                        "name" => "Product Sales",
                        "status" => "active",
                        "code" => "301",
                        "level3" =>
                        [
                            [
                                "name" => "Web Chanel",
                                "status" => "active",
                                "code" => "301-001",
                                "level4" => [
                                    [
                                        "name" => "Others Payment Methods",
                                        "status" => "active",
                                        "code" => "301-001-001",
                                    ],
                                ]
                            ],
                            [
                                "name" => "Store",
                                "status" => "active",
                                "code" => "301-002",
                                "level4" => [
                                    [
                                        "name" => "Sales Person",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "302-002-001",
                                    ],

                                ]
                            ],
                        ]
                    ],
                ]
            ],
            [
                "name" => "Expense",
                "status" => 'active',
                "start_code" => "400",
                "end_code" => "499",
                "level2" =>
                [
                    [
                        "name" => "Administrator Expenses",
                        "status" => "active",
                        "code" => "401",
                        "level3" =>
                        [
                            [
                                "name" => "Purchase Expense",
                                "status" => "active",
                                "code" => "401-001",
                                "level4" => [
                                    [
                                        "name" => "Vendor Payments",
                                        "balance" => 0,
                                        "status" => "active",
                                        "code" => "401-001-001",
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ]

        ];
        $this->defaultVoucherTypes = [
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
        $this->defaultAccountSettings = [
            [
                "name" => "Sales Recievable Account",
                "short" => "SRA",
                "account" => "Payment Recievable",
                "level" => "level4",
            ],
            [
                "name" => "Sales Person Revenue Account",
                "short" => "SPRA",
                "account" => "Sales Person",
                "level" => "level4",
            ],
            [
                "name" => "Product Inventory Account",
                "short" => "PIA",
                "account" => "Products",
                "level" => "level4",
            ],
            [
                "name" => "Purchase Expense Account",
                "short" => "PEA",
                "account" => "Vendor Payments",
                "level" => "level4",
            ],
            [
                "name" => "Office Sale Account",
                "short" => "OSA",
                "account" => "Office Account",
                "level" => "level4",
            ],
            [
                "name" => "Vendor Payable Account",
                "short" => "VPA",
                "account" => "Vendor Payable",
                "level" => "level4",
            ],
            [
                "name" => "Supplier Payment Account",
                "short" => "SPA",
                "account" => "Office Account",
                "level" => "level4",
            ]
        ];
    }
}
