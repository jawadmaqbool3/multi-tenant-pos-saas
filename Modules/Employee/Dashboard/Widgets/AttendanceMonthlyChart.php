<?php

namespace Modules\Employee\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Employee\Services\AttendanceDashboardService;

class AttendanceMonthlyChart extends AbstractWidget
{
    protected $attendance;

    public function __construct(AttendanceDashboardService $attendance)
    {
        $this->attendance = $attendance;
    }

    public function key(): string
    {
        return 'attendance.monthly';
    }

    public function title(): string
    {
        return 'Attendance This Month';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard attendance monthly';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 102;
    }

    protected function payload(): ?array
    {
        return $this->attendance->monthlyChart();
    }
}
