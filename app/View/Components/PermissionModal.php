<?php

namespace App\View\Components;

use App\Models\PermissionModule;
use Illuminate\View\Component;

class PermissionModal extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    private $modules;
    private $operation;
    public function __construct($operation = null)
    {
        $this->operation = $operation;
        $this->modules = PermissionModule::get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $modules = $this->modules;
        $operation = $this->operation;
        return view('components.permission-modal', compact('modules', 'operation'));
    }
}
