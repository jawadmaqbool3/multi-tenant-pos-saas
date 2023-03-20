<?php

namespace App\View\Components;

use App\Models\Role;
use Illuminate\View\Component;

class RoleSelector extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    protected $roles;
    protected $company;
    public function __construct($company = null)
    {
        $this->company = $company;
        $this->roles = Role::filter()->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $company = $this->company;
        $roles = $this->roles;
        return view('components.role-selector', compact('roles', 'company'));
    }
}
