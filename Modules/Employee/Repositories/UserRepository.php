<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;

use Modules\Employee\Models\User;

class UserRepository extends BaseRepository
{
    protected $dataTableWith = ['role'];
    protected $dataTableRawColumns = ['action', 'image', 'status'];

    public function __construct()
    {
        parent::__construct(new User());

        $this->dataTableEditColumns = [
            'image' => function ($user) {
                return $this->dt->thumbnail('assets/media/users/profile', $user->image);
            },
            'status' => function ($user) {
                return $this->dt->statusButton($user->status, $user->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'role' => function ($user) {
                return ucwords($user->role->name);
            },
        ];
        $this->dataTableAddColumns = [
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

    public function employeeTable($query)
    {
        return $this->dataTable($query, [
            'addColumns' => [
                'action' => function ($user) {
                    $btns = [];
                    if (auth()->user()->hasPermission('edit employee')) {
                        $btns[] = $this->dt->editButton(route('employees.edit', $user->uid));
                    }
                    if (auth()->user()->hasPermission('delete employee')) {
                        $btns[] = $this->dt->deleteButton($user->uid, $user->name);
                    }
                    return $this->dt->actionButtons($btns);
                },
            ],
        ]);
    }
}
