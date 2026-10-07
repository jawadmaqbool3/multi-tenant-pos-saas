<?php

namespace App\Services;

use App\Core\Helper;
use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\PasswordMismatchException;
use App\Repositories\EmployeePersonalInformationRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserRoleRepository;
use Exception;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    protected $users;
    protected $userRoles;
    protected $employeeInfo;
    protected $roles;
    protected $role;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $profileDirectory = 'assets/media/users/profile/';

    public function __construct(UserRepository $users, UserRoleRepository $userRoles, EmployeePersonalInformationRepository $employeeInfo, RoleRepository $roles)
    {
        $this->users = $users;
        $this->userRoles = $userRoles;
        $this->employeeInfo = $employeeInfo;
        $this->roles = $roles;
        $this->role = $this->roles->query()->where('name', 'employee')->filter()->first();
        if ($this->role == false) {
            throw new Exception("Role doesn't exists");
        }
    }

    public function getEmployees()
    {
        return $this->users->query()->filter()->whereHas('role', function ($query) {
            $query->where('id', $this->role->id);
        })->employeeTable();
    }

    public function store($request)
    {
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
        $this->userRoles->create([
            'role_id' => $this->role->id,
            'user_id' => $employee->id
        ]);
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
        $this->userRoles->query()->where('user_id', $employee->id)->delete();
        $this->userRoles->create([
            'role_id' => $this->role->id,
            'user_id' => $employee->id
        ]);
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
}
