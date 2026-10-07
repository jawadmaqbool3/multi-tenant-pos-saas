<?php

namespace App\View\Components;

use App\Models\Permission;
use Illuminate\View\Component;

class PermissionSelector extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    protected $permissions;
    protected $role;
    protected $company;
    public function __construct($role = null, $company = null)
    {
        $this->company = $company;
        $this->role = $role;
        if ($role) {
            $this->permissions = Permission::where('company_id', $role->company_id)->get();
        } else {
            $this->permissions = Permission::filter()->get();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $role = $this->role;
        $company = $this->company;
        $permissions = $this->permissions;
        return view('components.permission-selector', compact('permissions', 'role', 'company'));
    }
}
