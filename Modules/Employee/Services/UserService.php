<?php

namespace Modules\Employee\Services;

use App\Core\Helper;
use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\PasswordMismatchException;
use App\Exceptions\RoleDoesNotExistException;
use App\Repositories\CompanyRepository;
use Modules\Billing\Services\PlanLimitService;
use Modules\RBAC\Services\RoleService;
use Modules\Employee\Repositories\EmployeePersonalInformationRepository;
use Modules\Employee\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $users;
    protected $roles;
    protected $companies;
    protected $employeeInfo;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $profileDirectory = 'assets/media/users/profile/';

    public function __construct(
        UserRepository $users,
        RoleService $roles,
        CompanyRepository $companies,
        EmployeePersonalInformationRepository $employeeInfo
    ) {
        $this->users = $users;
        $this->roles = $roles;
        $this->companies = $companies;
        $this->employeeInfo = $employeeInfo;
    }

    public function getUsers($filter = null)
    {
        return $this->users->dataTable($this->rosterQuery($filter));
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
