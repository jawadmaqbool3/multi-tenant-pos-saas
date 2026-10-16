<?php

namespace Modules\ChartOfAccounts\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\ChartOfAccounts\Services\FinanceDashboardService;

class RevenueExpenseChart extends AbstractWidget
{
    protected $finance;

    public function __construct(FinanceDashboardService $finance)
    {
        $this->finance = $finance;
    }

    public function key(): string
    {
        return 'coa.revenue_expense_chart';
    }

    public function title(): string
    {
        return 'Revenue vs Expense';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard revenue expense';
    }

    public function module(): string
    {
        return 'Accounts';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 126;
    }

    protected function payload(): ?array
    {
        return $this->finance->revenueExpenseChart();
    }
}
