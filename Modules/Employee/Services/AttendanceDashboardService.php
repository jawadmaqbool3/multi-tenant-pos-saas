<?php

namespace Modules\Employee\Services;

use Carbon\Carbon;
use Modules\Employee\Repositories\AttendanceHistoryRepository;
use Modules\Employee\Repositories\UserRepository;

class AttendanceDashboardService
{
    protected $attendanceHistories;
    protected $users;

    public function __construct(AttendanceHistoryRepository $attendanceHistories, UserRepository $users)
    {
        $this->attendanceHistories = $attendanceHistories;
        $this->users = $users;
    }

    /**
     * Present / absent counts for the acting company today.
     */
    public function summaryCard(): array
    {
        $companyId = $this->companyId();

        return [
            'value' => $this->attendanceHistories->presentTodayCount($companyId),
            'format' => 'number',
            'secondary' => $this->users->absentTodayCount($companyId),
            'secondary_format' => 'number',
            'secondary_label' => 'Absent today',
        ];
    }

    /**
     * Per-day present headcount for a month (default: current month).
     *
     * @param  string|null  $yearMonth
     */
    public function monthlyChart($yearMonth = null): array
    {
        $companyId = $this->companyId();
        $yearMonth = $yearMonth ?: Carbon::now()->format('Y-m');

        $byDay = $this->attendanceHistories
            ->presentByDayForMonth($companyId, $yearMonth)
            ->pluck('present', 'day');

        $daysInMonth = Carbon::createFromFormat('Y-m', $yearMonth)->daysInMonth;

        $categories = [];
        $series = [];
        $total = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $value = (int) ($byDay[$day] ?? 0);
            $categories[] = (string) $day;
            $series[] = $value;
            $total += $value;
        }

        return [
            'chart_type' => 'bar',
            'series' => [['name' => 'Present', 'data' => $series]],
            'categories' => $categories,
            'kpi' => [
                'value' => $total,
                'format' => 'number',
                'label' => 'Present this month',
            ],
        ];
    }

    /**
     * Employees absent today.
     */
    public function absentTable(): array
    {
        $absent = $this->users->absentToday($this->companyId());

        return [
            'columns' => ['Employee', 'Status'],
            'rows' => $absent->map(function ($user) {
                return [
                    $user->name,
                    ['text' => 'Absent', 'badge' => 'danger'],
                ];
            })->values()->all(),
            'empty' => 'Everyone is present today.',
        ];
    }

    /**
     * @return int|null
     */
    protected function companyId()
    {
        return auth()->user() ? auth()->user()->company_id : null;
    }
}
