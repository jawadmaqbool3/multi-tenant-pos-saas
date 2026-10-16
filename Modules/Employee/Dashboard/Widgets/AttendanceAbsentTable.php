<?php

namespace Modules\Employee\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Employee\Services\AttendanceDashboardService;

class AttendanceAbsentTable extends AbstractWidget
{
    protected $attendance;

    public function __construct(AttendanceDashboardService $attendance)
    {
        $this->attendance = $attendance;
    }

    public function key(): string
    {
        return 'attendance.absent_today';
    }

    public function title(): string
    {
        return 'Absent Today';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard attendance absent today';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 103;
    }

    protected function payload(): ?array
    {
        return $this->attendance->absentTable();
    }
}
