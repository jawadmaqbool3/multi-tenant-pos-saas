<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\AccountLevel1Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel2Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel3Repository;
use Modules\ChartOfAccounts\Repositories\AccountLevel4Repository;

class DefaultAccountService
{
    protected $level1;
    protected $level2;
    protected $level3;
    protected $level4;
    protected $defaultAccounts;

    public function __construct(
        AccountLevel1Repository $level1,
        AccountLevel2Repository $level2,
        AccountLevel3Repository $level3,
        AccountLevel4Repository $level4
    ) {
        $this->level1 = $level1;
        $this->level2 = $level2;
        $this->level3 = $level3;
        $this->level4 = $level4;

        $this->defaultAccounts = $this->getDefaultAccounts();
    }

    public function initiateAccounts($company = null)
    {
        $companyId = $company ? $company->id : null;
        $alreadySeeded = $this->level1->query()->where('company_id', $companyId)->exists();
        if ($alreadySeeded) {
            return;
        }

        foreach ($this->defaultAccounts as $key => $account) {
            $level1 = $this->level1->create([
                "name" => $account['name'],
                "status" => $account['status'],
                "start_code" => $account['start_code'],
                "end_code" => $account['end_code'],
                "company_id" => $company ? $company->id : null,
            ]);
            $level1->refresh();
            if (@$account["level2"]) {
                foreach ($account["level2"] as $key => $level2) {
                    $_level2 = $this->level2->create([
                        "name" => $level2["name"],
                        "status" => $level2["status"],
                        "code" => $level2["code"],
                        "parent_id" => $level1->id,
                        "company_id" => $company ? $company->id : null,
                    ]);
                    $_level2->refresh();
                    if (@$level2["level3"]) {
                        foreach ($level2["level3"] as $key => $level3) {
                            $_level3 = $this->level3->create([
                                "name" => $level3["name"],
                                "status" => $level3["status"],
                                "code" => $level3["code"],
                                "parent_id" => $_level2->id,
                                "company_id" => $company ? $company->id : null,
                            ]);
                            $_level3->refresh();
                            if (@$level3["level4"]) {
                                foreach ($level3["level4"] as $key => $level4) {
                                    $this->level4->create([
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

    protected function getDefaultAccounts()
    {
        return [
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
    }
}
