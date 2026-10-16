<?php

namespace Modules\Payroll\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Payroll\Services\PayrollDashboardService;

class MyPayslipsTable extends AbstractWidget
{
    protected $payroll;

    public function __construct(PayrollDashboardService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function key(): string
    {
        return 'payroll.my_payslips';
    }

    public function title(): string
    {
        return 'My Payslips';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard my payslips';
    }

    public function module(): string
    {
        return 'Payroll';
    }

    public function scope(): string
    {
        return 'own';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 121;
    }

    protected function payload(): ?array
    {
        return $this->payroll->myPayslipsTable();
    }
}
