<?php

namespace Modules\Payroll\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Payroll\Services\PayrollDashboardService;

class PayrollRecentTable extends AbstractWidget
{
    protected $payroll;

    public function __construct(PayrollDashboardService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function key(): string
    {
        return 'payroll.recent';
    }

    public function title(): string
    {
        return 'Recent Payroll Runs';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard payroll recent';
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
        return 120;
    }

    protected function payload(): ?array
    {
        return $this->payroll->recentTable();
    }
}
