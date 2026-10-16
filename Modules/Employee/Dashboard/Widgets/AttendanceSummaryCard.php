<?php

namespace Modules\Employee\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Employee\Services\AttendanceDashboardService;

class AttendanceSummaryCard extends AbstractWidget
{
    protected $attendance;

    public function __construct(AttendanceDashboardService $attendance)
    {
        $this->attendance = $attendance;
    }

    public function key(): string
    {
        return 'attendance.summary';
    }

    public function title(): string
    {
        return 'Attendance Today';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard attendance summary';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 101;
    }

    protected function payload(): ?array
    {
        return $this->attendance->summaryCard();
    }
}
