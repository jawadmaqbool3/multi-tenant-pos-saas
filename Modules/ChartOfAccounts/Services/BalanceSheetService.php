<?php

namespace Modules\ChartOfAccounts\Services;

use Modules\ChartOfAccounts\Repositories\TransactionRepository;

class BalanceSheetService
{
    /**
     * The Level 1 account types a balance sheet presents.
     *
     * @var array<int, string>
     */
    const SECTIONS = ['Asset', 'Liability', 'Equity'];

    protected $transactions;
    protected $accounts;
    protected $vouchers;

    public function __construct(
        TransactionRepository $transactions,
        ChatOfAccountService $accounts,
        VoucherService $vouchers
    ) {
        $this->transactions = $transactions;
        $this->accounts = $accounts;
        $this->vouchers = $vouchers;
    }

    /**
     * Build a point-in-time balance sheet as of the given date. Balances are
     * derived from posted transactions (signed by the account's Level 1 type)
     * rather than the live `balance` column, so any past date is accurate.
     *
     * @param  string|null  $asOf
     * @return array
     */
    public function getBalanceSheet($asOf = null)
    {
        $companyId = optional(auth()->user())->company_id;
        $totals = $this->transactions->accountTotals($companyId, $asOf);

        $groups = [
            'Asset' => [],
            'Liability' => [],
            'Equity' => [],
            'Revenue' => [],
            'Expense' => [],
        ];

        foreach ($this->accounts->getAccounts() as $level1) {
            if (!array_key_exists($level1->name, $groups)) {
                continue;
            }

            $this->applyBalances($level1, $level1->name, $totals);
            $groups[$level1->name][] = $level1;
        }

        $assets = collect($groups['Asset'])->sum('report_balance');
        $liabilities = collect($groups['Liability'])->sum('report_balance');
        $equity = collect($groups['Equity'])->sum('report_balance');
        $revenue = collect($groups['Revenue'])->sum('report_balance');
        $expense = collect($groups['Expense'])->sum('report_balance');

        $netProfit = $revenue - $expense;
        $liabilitiesAndEquity = $liabilities + $equity + $netProfit;

        return [
            'as_of' => $asOf,
            'assets' => $groups['Asset'],
            'liabilities' => $groups['Liability'],
            'equity' => $groups['Equity'],
            'assets_total' => $assets,
            'liabilities_total' => $liabilities,
            'equity_total' => $equity,
            'revenue_total' => $revenue,
            'expense_total' => $expense,
            'net_profit' => $netProfit,
            'liabilities_and_equity_total' => $liabilitiesAndEquity,
            'difference' => $assets - $liabilitiesAndEquity,
        ];
    }

    /**
     * Stamp a `report_balance` on every node of a Level 1 subtree, rolling the
     * Level 4 totals up through Level 3 and Level 2.
     *
     * @param  \Modules\ChartOfAccounts\Models\AccountLevel1  $level1
     * @param  string  $type
     * @param  \Illuminate\Support\Collection  $totals
     * @return void
     */
    protected function applyBalances($level1, $type, $totals)
    {
        foreach ($level1->level2 as $level2) {
            foreach ($level2->level3 as $level3) {
                foreach ($level3->level4 as $level4) {
                    $debit = isset($totals[$level4->id]) ? (float) $totals[$level4->id]->debit : 0.0;
                    $credit = isset($totals[$level4->id]) ? (float) $totals[$level4->id]->credit : 0.0;

                    $level4->report_balance = $this->vouchers->signedAmount($type, 'debit', $debit)
                        + $this->vouchers->signedAmount($type, 'credit', $credit);
                }

                $level3->report_balance = $level3->level4->sum('report_balance');
            }

            $level2->report_balance = $level2->level3->sum('report_balance');
        }

        $level1->report_balance = $level1->level2->sum('report_balance');
    }
}
