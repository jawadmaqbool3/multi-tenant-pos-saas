<?php

namespace Modules\ChartOfAccounts\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\ChartOfAccounts\Services\FinanceDashboardService;

class PayablesCard extends AbstractWidget
{
    protected $finance;

    public function __construct(FinanceDashboardService $finance)
    {
        $this->finance = $finance;
    }

    public function key(): string
    {
        return 'coa.payables';
    }

    public function title(): string
    {
        return 'Payables';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard payables';
    }

    public function module(): string
    {
        return 'Accounts';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 128;
    }

    protected function payload(): ?array
    {
        return $this->finance->payablesCard();
    }
}
