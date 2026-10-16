<?php

namespace Modules\Employee\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;

class AttendanceWidget extends AbstractWidget
{
    public function key(): string
    {
        return 'attendance.mine';
    }

    public function title(): string
    {
        return 'Attendance Report';
    }

    public function type(): string
    {
        return 'custom';
    }

    public function permission(): string
    {
        return 'dashboard my attendance';
    }

    public function scope(): string
    {
        return 'own';
    }

    public function size(): string
    {
        return 'col-12';
    }

    public function order(): int
    {
        return 100;
    }

    /**
     * Only shown to users who have attendance enabled on their profile.
     */
    public function visible($user): bool
    {
        return (bool) ($user->attendance_enabled ?? false);
    }

    public function view(): string
    {
        return 'employee::dashboard.widgets.attendance';
    }

    /**
     * Self-hydrating: attendance history is fetched by its own endpoint.
     */
    protected function payload(): ?array
    {
        return null;
    }
}
