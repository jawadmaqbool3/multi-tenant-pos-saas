<?php

namespace Modules\Employee\Services;

use App\Core\Helper;
use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\PasswordMismatchException;
use App\Exceptions\RoleDoesNotExistException;
use App\Repositories\CompanyRepository;
use Modules\Billing\Services\PlanLimitService;
use Modules\RBAC\Services\RoleService;
use Modules\Employee\Repositories\AttendanceHistoryRepository;
use Modules\Employee\Repositories\EmployeePersonalInformationRepository;
use Modules\Employee\Repositories\UserRepository;
use Modules\Payroll\Repositories\PayrollRepository;
use Modules\Payroll\Repositories\PayslipRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $users;
    protected $roles;
    protected $companies;
    protected $employeeInfo;
    protected $attendance;
    protected $payrolls;
    protected $payslips;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $profileDirectory = 'assets/media/users/profile/';

    public function __construct(
        UserRepository $users,
        RoleService $roles,
        CompanyRepository $companies,
        EmployeePersonalInformationRepository $employeeInfo,
        AttendanceHistoryRepository $attendance,
        PayrollRepository $payrolls,
        PayslipRepository $payslips
    ) {
        $this->users = $users;
        $this->roles = $roles;
        $this->companies = $companies;
        $this->employeeInfo = $employeeInfo;
        $this->attendance = $attendance;
        $this->payrolls = $payrolls;
        $this->payslips = $payslips;
    }

    public function getUsers($filter = null)
    {
        return $this->users->dataTable($this->rosterQuery($filter));
    }

    /**
     * The employees roster for the Employees screen: same rows as
     * `getUsers()`, but the action column is narrowed to a single View button.
     *
     * @param  string|null  $filter
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeeTable($filter = null)
    {
        return $this->users->useEmployeeActions()->dataTable($this->rosterQuery($filter));
    }

    /**
     * Everything the employee detail screen renders: the user with their role,
     * personal information and salary, a per-day attendance presence series for
     * a month, that month's run (when the viewer may see payroll) and the
     * employee's payslip history.
     *
     * @param  \Modules\Employee\Models\User  $user
     * @param  string|null  $yearMonth  YYYY-MM, defaults to the current month
     * @return array
     */
    public function employeeDetail($user, $yearMonth = null)
    {
        $yearMonth = $yearMonth ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $yearMonth)) {
            $yearMonth = now()->format('Y-m');
        }
        $daysInMonth = \Illuminate\Support\Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth()->daysInMonth;
        $companyId = auth()->user()->company_id;

        $canViewPayroll = auth()->user()->hasPermission('view payroll');

        $info = $this->employeeInfo->forEmployee($user->id);

        $run = $canViewPayroll ? $this->payrolls->forPeriod($companyId, $yearMonth) : null;
        $payslips = $canViewPayroll ? $this->payslips->listForEmployee($user->id, $companyId) : collect();

        return [
            'user' => $user,
            'info' => $info,
            'salary' => $info ? (float) $info->salary : 0,
            'role' => $user->role,
            'canViewPayroll' => $canViewPayroll,
            'run' => $run,
            'payslips' => $payslips,
            'month' => $yearMonth,
            'daysInMonth' => $daysInMonth,
            'presence' => $this->attendance->presentDaysByDayForUser($user->id, $yearMonth, $daysInMonth),
        ];
    }

    /**
     * A single employee's payslip within a given run, for the detail screen.
     *
     * @param  \Modules\Employee\Models\User  $user
     * @param  \Modules\Payroll\Models\Payroll  $payroll
     * @return \Modules\Payroll\Models\Payslip|null
     */
    public function payslipInRun($user, $payroll)
    {
        return $this->payslips->forEmployeeInRun($user->id, $payroll->id);
    }

    /**
     * Lightweight uid/name list of the users on the given roster, for pickers.
     *
     * @param  string|null  $filter
     * @return \Illuminate\Support\Collection
     */
    public function employeeList($filter = null)
    {
        return $this->rosterQuery($filter)->orderBy('name')->get(['uid', 'name']);
    }

    /**
     * Base query for a user roster.
     *
     * The Employees screen lists the payroll roster; the attendance screen lists
     * the staff tracked by the attendance system. Anything else shows every
     * user.
     *
     * @param  string|null  $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function rosterQuery($filter = null)
    {
        $query = $this->users->query()->filter();

        $flag = $this->flagForFilter($filter);
        if ($flag) {
            $query->where($flag, true);
        }

        // HR manages the payroll roster: only payroll-enabled users are shown.
        if (auth()->user() && auth()->user()->hasRole('hr')) {
            $query->where('payroll_enabled', true);
        }

        return $query;
    }

    /**
     * Map a requested list filter to the boolean user column it narrows by.
     *
     * @param  string|null  $filter
     * @return string|null
     */
    protected function flagForFilter($filter)
    {
        $map = [
            'payroll' => 'payroll_enabled',
            'attendance' => 'attendance_enabled',
        ];

        return $map[$filter] ?? null;
    }

    public function getRoles()
    {
        return $this->roles->getRolesList();
    }

    public function store($request)
    {
        $role = $this->roles->getRoleByUid($request->role);
        if (!$role) {
            throw new RoleDoesNotExistException();
        }

        $payrollEnabled = $request->boolean('payroll_enabled');
        $attendanceEnabled = $request->boolean('attendance_enabled');

        if ($payrollEnabled || $attendanceEnabled) {
            app(PlanLimitService::class)->ensureWithinLimit('employees', $this->company());
        }

        $image = '';
        if ($request->file('image')) {
            $image = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
                'field' => 'image',
            ]);
        }
        $password = Hash::make($request->password);
        $user = $this->users->create([
            'name' => $request->name,
            'password' => $password,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image,
            'company_id' => auth()->user()->company_id,
            'payroll_enabled' => $payrollEnabled,
            'attendance_enabled' => $attendanceEnabled,
        ]);
        $user->refresh();
        $this->roles->assignUserRole($user->id, $role->id);

        if ($payrollEnabled) {
            $this->employeeInfo->create([
                'employee_id' => $user->id,
                'salary' => $request->salary,
            ]);
        }
    }

    public function update($request, $user)
    {
        if ($request->password && $request->password != $request->co_password) {
            throw new PasswordMismatchException();
        }
        $role = $this->roles->getRoleByUid($request->role);
        if (!$role) {
            throw new RoleDoesNotExistException();
        }
        if ($this->users->query()->where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            throw new EmailAlreadyTakenException();
        }

        $payrollEnabled = $request->boolean('payroll_enabled');
        $attendanceEnabled = $request->boolean('attendance_enabled');

        // Only enforce the plan limit when a previously unflagged user crosses
        // into a flagged state, so unrelated edits still save at/over the cap.
        if (!($user->payroll_enabled || $user->attendance_enabled) && ($payrollEnabled || $attendanceEnabled)) {
            app(PlanLimitService::class)->ensureWithinLimit('employees', $this->company());
        }

        $image = $user->image;
        if ($request->file('image')) {
            if ($user->image) {
                Helper::deleteExcept([
                    'files' => [$user->image],
                    'exceptions' => [],
                    'path' => $this->profileDirectory
                ]);
            }
            $image = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
                'field' => 'image',
            ]);
        }
        $password = $user->password;
        if ($request->password) {
            $password = Hash::make($request->password);
        }
        $this->users->query()->where('id', $user->id)->update([
            'name' => $request->name,
            'password' => $password,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image,
            'payroll_enabled' => $payrollEnabled,
            'attendance_enabled' => $attendanceEnabled,
        ]);
        $this->roles->assignUserRole($user->id, $role->id);

        if ($payrollEnabled) {
            $this->employeeInfo->query()->updateOrCreate(
                ['employee_id' => $user->id],
                ['salary' => $request->salary]
            );
        }
    }

    /**
     * Update the currently authenticated user's own profile (name, email,
     * password and avatar only). Role, status, payroll and attendance flags are
     * intentionally untouched.
     */
    public function updateProfile($request, $user)
    {
        if ($request->password && $request->password != $request->co_password) {
            throw new PasswordMismatchException();
        }
        if ($this->users->query()->where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            throw new EmailAlreadyTakenException();
        }

        $image = $user->image;
        if ($request->file('image')) {
            if ($user->image) {
                Helper::deleteExcept([
                    'files' => [$user->image],
                    'exceptions' => [],
                    'path' => $this->profileDirectory
                ]);
            }
            $image = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
                'field' => 'image',
            ]);
        }

        $password = $user->password;
        if ($request->password) {
            $password = Hash::make($request->password);
        }

        $this->users->query()->where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'image' => $image,
        ]);
    }

    public function getByCompanyId($companyId)
    {
        return $this->users->query()->where('company_id', $companyId)->get();
    }

    /**
     * The company the current user belongs to (null for platform accounts).
     *
     * @return \App\Models\Company|null
     */
    protected function company()
    {
        if (!auth()->user() || !auth()->user()->company_id) {
            return null;
        }

        return $this->companies->query()->where('id', auth()->user()->company_id)->first();
    }

    public function delete($user)
    {
        Helper::deleteExcept([
            'files' => [$user->image],
            'exceptions' => [],
            'path' => $this->profileDirectory
        ]);
        $user->delete();
    }
}
