<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;

use Modules\Employee\Models\User;

class UserRepository extends BaseRepository
{
    protected $dataTableWith = ['role'];
    protected $dataTableRawColumns = ['action', 'image', 'status', 'flags'];

    /**
     * Which actions the shared `action` column renders. `user` keeps the
     * edit/delete pair used by the Users screen; `employee` narrows it to a
     * single View button on the Employees screen.
     *
     * @var string
     */
    protected $actionMode = 'user';

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
                if ($this->actionMode === 'employee') {
                    $btns = [];
                    if (auth()->user()->hasPermission('view employee')) {
                        $btns[] = $this->dt->viewButton(route('employee.show', $user->uid));
                    }
                    return $this->dt->actionButtons($btns);
                }

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
     * Switch the action column to the Employees-screen variant (View only).
     * Safe to call on the injected instance for the current request.
     *
     * @return $this
     */
    public function useEmployeeActions()
    {
        $this->actionMode = 'employee';

        return $this;
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
     * Total user accounts in a company — the count that consumes the plan's
     * `users` limit.
     *
     * @param  int  $companyId
     * @return int
     */
    public function countForCompany(int $companyId): int
    {
        return $this->query()->where('company_id', $companyId)->count();
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
     * Active, payroll-enabled users of a company limited to the given uids —
     * the eligible subset for a payroll run.
     *
     * @param  int  $companyId
     * @param  array  $uids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function payrollEnabledByUids($companyId, array $uids)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('payroll_enabled', true)
            ->where('status', 'active')
            ->whereIn('uid', $uids)
            ->get();
    }

    /**
     * A single payroll-enabled, active user of a company, or null. Used by the
     * per-employee payroll generation flow.
     *
     * @param  int  $companyId
     * @param  int  $employeeId
     * @return \Modules\Employee\Models\User|null
     */
    public function findPayrollEnabled($companyId, $employeeId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('id', $employeeId)
            ->where('payroll_enabled', true)
            ->where('status', 'active')
            ->first();
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
