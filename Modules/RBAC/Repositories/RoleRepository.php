<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;

use Modules\RBAC\Models\Role;

class RoleRepository extends BaseRepository
{
    protected $dataTableWith = ['company.setting'];
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Role());

        $this->dataTableEditColumns = [
            'status' => function ($role) {
                return $this->dt->statusBadge($role->status, $role->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'company.setting.name' => function ($role) {
                return @$role->company->setting->name;
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($role) {
                $btns = [];
                if (auth()->user()->hasPermission('edit role')) {
                    $btns[] = $this->dt->editButton(route('roles.edit', $role->uid));
                }
                if (auth()->user()->hasPermission('delete role')) {
                    $btns[] = $this->dt->deleteButton($role->uid, $role->name);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
