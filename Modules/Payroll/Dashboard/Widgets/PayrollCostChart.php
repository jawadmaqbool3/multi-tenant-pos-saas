<?php

namespace Modules\Payroll\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Payroll\Services\PayrollDashboardService;

class PayrollCostChart extends AbstractWidget
{
    protected $payroll;

    public function __construct(PayrollDashboardService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function key(): string
    {
        return 'payroll.cost_chart';
    }

    public function title(): string
    {
        return 'Payroll Cost';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard payroll cost';
    }

    public function module(): string
    {
        return 'Payroll';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 119;
    }

    protected function payload(): ?array
    {
        return $this->payroll->costChart();
    }
}
