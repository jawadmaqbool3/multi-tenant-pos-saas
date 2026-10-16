<?php

namespace Modules\Payroll\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Payroll\Services\PayrollDashboardService;

class PayrollPendingCard extends AbstractWidget
{
    protected $payroll;

    public function __construct(PayrollDashboardService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function key(): string
    {
        return 'payroll.pending';
    }

    public function title(): string
    {
        return 'Draft Payroll Runs';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard payroll pending';
    }

    public function module(): string
    {
        return 'Payroll';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 116;
    }

    protected function payload(): ?array
    {
        return $this->payroll->pendingCard();
    }
}
