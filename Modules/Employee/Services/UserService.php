<?php

namespace Modules\Employee\Services;

use App\Core\Helper;
use App\Exceptions\EmailAlreadyTakenException;
use App\Exceptions\PasswordMismatchException;
use App\Exceptions\RoleDoesNotExistException;
use Modules\RBAC\Services\RoleService;
use Modules\Employee\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $users;
    protected $roles;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $profileDirectory = 'assets/media/users/profile/';

    public function __construct(UserRepository $users, RoleService $roles)
    {
        $this->users = $users;
        $this->roles = $roles;
    }

    public function getUsers()
    {
        return $this->users->query()->filter()->dataTable();
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
        $user = $this->users->create([
            'name' => $request->name,
            'password' => $password,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image,
            'company_id' => auth()->user()->company_id
        ]);
        $user->refresh();
        $this->roles->assignUserRole($user->id, $role->id);
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
            'image' => $image
        ]);
        $this->roles->assignUserRole($user->id, $role->id);
    }

    public function getByCompanyId($companyId)
    {
        return $this->users->query()->where('company_id', $companyId)->get();
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
