<?php

namespace Modules\Employee\Services;

use App\Core\Helper;
use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\PasswordMismatchException;
use Modules\Employee\Repositories\EmployeePersonalInformationRepository;
use Modules\RBAC\Services\RoleService;
use Modules\Employee\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    protected $users;
    protected $employeeInfo;
    protected $roles;
    protected $role;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $profileDirectory = 'assets/media/users/profile/';

    public function __construct(UserRepository $users, EmployeePersonalInformationRepository $employeeInfo, RoleService $roles)
    {
        $this->users = $users;
        $this->employeeInfo = $employeeInfo;
        $this->roles = $roles;
    }

    protected function role()
    {
        if (!$this->role) {
            $this->role = $this->roles->getRoleByName('employee');
            if ($this->role == false) {
                throw new Exception("Role doesn't exists");
            }
        }

        return $this->role;
    }

    public function getEmployees()
    {
        return $this->users->employeeTable($this->users->query()->filter()->whereHas('role', function ($query) {
            $query->where('id', $this->role()->id);
        }));
    }

    public function store($request)
    {
        app(\Modules\Billing\Services\PlanLimitService::class)
            ->ensureWithinLimit('employees', $this->company());

        $image = '';
        if ($request->file('image')) {
            $image = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
            ]);
        }
        $password = Hash::make($request->password);
        $employee = $this->users->create([
            'name' => $request->name,
            'company_id' => auth()->user()->company_id,
            'password' => $password,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image
        ]);
        $employee->refresh();
        $this->roles->assignUserRole($employee->id, $this->role()->id);
        $this->employeeInfo->create([
            'father_name' => $request->father_name,
            'contact' => $request->contact,
            'salary' => $request->salary,
            'designation' => $request->designation,
            'address' => $request->address,
            'employee_id' => $employee->id,
        ]);
    }

    public function update($request, $employee)
    {
        if ($request->password && $request->password != $request->co_password) {
            throw new PasswordMismatchException();
        }

        if ($this->users->query()->where('email', $request->email)->where('id', '!=', $employee->id)->exists()) {
            throw new EmailAlreadyTakenException();
        }
        $image = $employee->image;
        if ($request->file('image')) {
            if ($employee->image) {
                Helper::deleteExcept([
                    'files' => [$employee->image],
                    'exceptions' => [],
                    'path' => $this->profileDirectory
                ]);
            }
            $image = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
            ]);
        }
        $password = $employee->password;
        if ($request->password) {
            $password = Hash::make($request->password);
        }
        $this->users->query()->where('id', $employee->id)->update([
            'name' => $request->name,
            'password' => $password,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image
        ]);
        $this->roles->assignUserRole($employee->id, $this->role()->id);
        $this->employeeInfo->query()->where('employee_id', $employee->id)->update([
            'father_name' => $request->father_name,
            'contact' => $request->contact,
            'salary' => $request->salary,
            'designation' => $request->designation,
            'address' => $request->address,
        ]);
    }

    public function delete($employee)
    {
        Helper::deleteExcept([
            'files' => [$employee->image],
            'exceptions' => [],
            'path' => $this->profileDirectory
        ]);
        $employee->delete();
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

        return \App\Models\Company::where('id', auth()->user()->company_id)->first();
    }
}
