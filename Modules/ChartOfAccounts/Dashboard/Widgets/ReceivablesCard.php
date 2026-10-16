<?php

namespace Modules\ChartOfAccounts\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\ChartOfAccounts\Services\FinanceDashboardService;

class ReceivablesCard extends AbstractWidget
{
    protected $finance;

    public function __construct(FinanceDashboardService $finance)
    {
        $this->finance = $finance;
    }

    public function key(): string
    {
        return 'coa.receivables';
    }

    public function title(): string
    {
        return 'Receivables';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard receivables';
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
        return 127;
    }

    protected function payload(): ?array
    {
        return $this->finance->receivablesCard();
    }
}
