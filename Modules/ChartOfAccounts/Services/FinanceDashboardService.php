<?php

namespace Modules\ChartOfAccounts\Services;

use Illuminate\Support\Carbon;
use Modules\ChartOfAccounts\Repositories\TransactionRepository;

class FinanceDashboardService
{
    protected $transactions;
    protected $accountSettings;

    public function __construct(TransactionRepository $transactions, AccountSettingService $accountSettings)
    {
        $this->transactions = $transactions;
        $this->accountSettings = $accountSettings;
    }

    /**
     * Revenue vs expense vs net profit for the last N months (default 12).
     *
     * @param  int  $months
     */
    public function revenueExpenseChart($months = 12): array
    {
        $companyId = $this->companyId();

        $window = [];
        $cursor = Carbon::now()->startOfMonth();
        for ($i = $months - 1; $i >= 0; $i--) {
            $window[] = $cursor->copy()->subMonths($i)->format('Y-m');
        }

        $map = [];
        foreach ($this->transactions->revenueExpenseByMonth($companyId, $months) as $row) {
            $map[$row->month] = $row;
        }

        $revenueSeries = [];
        $expenseSeries = [];
        $categories = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($window as $month) {
            $revenue = round((float) ($map[$month]->revenue ?? 0), 2);
            $expense = round((float) ($map[$month]->expense ?? 0), 2);

            $revenueSeries[] = $revenue;
            $expenseSeries[] = $expense;
            $categories[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $totalRevenue += $revenue;
            $totalExpense += $expense;
        }

        $net = $totalRevenue - $totalExpense;

        return [
            'chart_type' => 'bar',
            'series' => [
                ['name' => 'Revenue', 'data' => $revenueSeries],
                ['name' => 'Expense', 'data' => $expenseSeries],
            ],
            'categories' => $categories,
            'kpi' => [
                'value' => round($net, 2),
                'format' => 'currency',
                'label' => 'Net profit (' . $months . ' months)',
                'direction' => $net >= 0 ? 'positive' : 'negative',
            ],
            'legend' => [
                ['label' => 'Revenue', 'value' => round($totalRevenue, 2), 'format' => 'currency', 'color' => 'primary'],
                ['label' => 'Expense', 'value' => round($totalExpense, 2), 'format' => 'currency', 'color' => 'danger'],
            ],
        ];
    }

    /**
     * Customer receivables balance (Sales Receivable account).
     */
    public function receivablesCard(): array
    {
        return [
            'value' => round($this->accountBalance('SRA'), 2),
            'format' => 'currency',
            'secondary_label' => 'Sales receivable',
            'link' => ['label' => 'View accounts', 'url' => route('accounts.index')],
        ];
    }

    /**
     * Vendor payables balance (Vendor Payable account).
     */
    public function payablesCard(): array
    {
        return [
            'value' => round($this->accountBalance('VPA'), 2),
            'format' => 'currency',
            'secondary_label' => 'Vendor payable',
            'link' => ['label' => 'View accounts', 'url' => route('accounts.index')],
        ];
    }

    /**
     * Balance of a level-4 account resolved by its short setting code. A
     * missing or unset account setting resolves to zero rather than throwing.
     *
     * @param  string  $short
     * @return float
     */
    protected function accountBalance($short)
    {
        try {
            $account = $this->accountSettings->getAccount($short);

            return $account ? (float) $account->balance : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * @return int|null
     */
    protected function companyId()
    {
        return auth()->user() ? auth()->user()->company_id : null;
    }
}
