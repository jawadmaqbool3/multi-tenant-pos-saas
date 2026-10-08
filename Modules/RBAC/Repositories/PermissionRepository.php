<?php

namespace Modules\RBAC\Repositories;

use App\Repositories\BaseRepository;

use Modules\RBAC\Models\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Permission());

        $this->dataTableAddColumns = [
            'action' => function ($permission) {
                $btns = [];
                if (auth()->user()->hasPermission('edit permission')) {
                    $btns[] = '<a data-bs-target="#edit_modal" data-bs-toggle="modal" data-base-url="' . route('permissions.update', $permission->uid) . '" data-modal_id="' . $permission->uid . '" data-modal_module="' . $permission->module_id . '" data-modal_name="' . $permission->name . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">'
                        . $this->editIconSvg() . '</a>';
                }
                if (auth()->user()->hasPermission('delete permission')) {
                    $btns[] = $this->deleteButton($permission->uid, $permission->name);
                }
                return $this->actionButtons($btns);
            },
        ];
    }
}
