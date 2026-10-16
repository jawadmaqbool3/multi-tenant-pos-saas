<?php

namespace Modules\Payroll\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Payroll\Services\PayrollDashboardService;

class PayrollPaidMonthCard extends AbstractWidget
{
    protected $payroll;

    public function __construct(PayrollDashboardService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function key(): string
    {
        return 'payroll.paid_month';
    }

    public function title(): string
    {
        return 'Payroll Paid This Month';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard payroll paid month';
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
        return 118;
    }

    protected function payload(): ?array
    {
        return $this->payroll->paidMonthCard();
    }
}
