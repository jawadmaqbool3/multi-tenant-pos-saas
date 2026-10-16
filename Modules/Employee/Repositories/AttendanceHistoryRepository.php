<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use Modules\Employee\Models\AttendanceHistory;

class AttendanceHistoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AttendanceHistory());
    }

    /**
     * Number of distinct employees who clocked in today within a company.
     * `attendance_histories` stores the numeric `users.id`, so the company
     * filter is applied through a sub-query.
     *
     * @param  int  $companyId
     * @return int
     */
    public function presentTodayCount($companyId)
    {
        return $this->query()
            ->where('type', 'clockin')
            ->whereDate('time', now()->toDateString())
            ->whereIn('user_id', $this->companyUserIds($companyId))
            ->distinct()
            ->count('user_id');
    }

    /**
     * Present headcount for every day of a month (YYYY-MM), ordered by day.
     * Used by the monthly attendance chart.
     *
     * @param  int  $companyId
     * @param  string  $yearMonth
     * @return \Illuminate\Support\Collection
     */
    public function presentByDayForMonth($companyId, $yearMonth)
    {
        [$year, $month] = explode('-', $yearMonth);

        return $this->query()
            ->selectRaw('DAY(time) as day, COUNT(DISTINCT user_id) as present')
            ->where('type', 'clockin')
            ->whereYear('time', (int) $year)
            ->whereMonth('time', (int) $month)
            ->whereIn('user_id', $this->companyUserIds($companyId))
            ->groupBy(DB::raw('DAY(time)'))
            ->orderBy('day')
            ->get();
    }

    /**
     * @param  int  $companyId
     * @return \Closure
     */
    protected function companyUserIds($companyId)
    {
        return function ($query) use ($companyId) {
            $query->select('id')->from('users')->where('company_id', $companyId);
        };
    }
}
