<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;

use Modules\Employee\Models\User;

class UserRepository extends BaseRepository
{
    protected $dataTableWith = ['role'];
    protected $dataTableRawColumns = ['action', 'image', 'status', 'flags'];

    public function __construct()
    {
        parent::__construct(new User());

        $this->dataTableEditColumns = [
            'image' => function ($user) {
                return $this->dt->thumbnail('assets/media/users/profile', $user->image);
            },
            'status' => function ($user) {
                return $this->dt->statusBadge($user->status, $user->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'role' => function ($user) {
                return ucwords($user->role->name);
            },
        ];
        $this->dataTableAddColumns = [
            'flags' => function ($user) {
                $badges = [];
                if ($user->payroll_enabled) {
                    $badges[] = '<span class="badge badge-light-primary me-1">Payroll</span>';
                }
                if ($user->attendance_enabled) {
                    $badges[] = '<span class="badge badge-light-success">Attendance</span>';
                }
                return $badges ? implode(' ', $badges) : '<span class="text-muted">—</span>';
            },
            'action' => function ($user) {
                $btns = [];
                if (auth()->user()->hasPermission('edit user')) {
                    $btns[] = $this->dt->editButton(route('users.edit', $user->uid));
                }
                if (auth()->user()->hasPermission('delete user')) {
                    $btns[] = $this->dt->deleteButton($user->uid, $user->name);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * Users flagged for payroll and/or attendance in a company — the count
     * that consumes the plan's `employees` limit.
     *
     * @param  int  $companyId
     * @return int
     */
    public function countFlaggedForCompany(int $companyId): int
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where(fn ($q) => $q->where('payroll_enabled', true)->orWhere('attendance_enabled', true))
            ->count();
    }

    /**
     * Active, payroll-enabled users of a company — the eligible set for a
     * payroll run. Reads through this repository so services never query `User`
     * directly (architecture constraint).
     *
     * @param  int  $companyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function payrollEnabledForCompany(int $companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('payroll_enabled', true)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Active, attendance-enabled employees of a company who have not clocked in
     * today. Uses `id` directly because `filter()` would also exclude the acting
     * user and would not count attendance-disabled staff.
     *
     * @param  int  $companyId
     * @return int
     */
    public function absentTodayCount($companyId)
    {
        return $this->absentTodayQuery($companyId)->count();
    }

    /**
     * The absent employees themselves (name + image) for the "absent today" list.
     *
     * @param  int  $companyId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function absentToday($companyId, $limit = 5)
    {
        return $this->absentTodayQuery($companyId)
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'image']);
    }

    /**
     * @param  int  $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function absentTodayQuery($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('attendance_enabled', true)
            ->where('status', 'active')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')
                    ->from('attendance_histories')
                    ->where('type', 'clockin')
                    ->whereDate('time', now()->toDateString());
            });
    }
}
