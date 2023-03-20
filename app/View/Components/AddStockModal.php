<?php

namespace App\View\Components;

use App\Models\Supplier;
use Illuminate\View\Component;

class AddStockModal extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    private $suppliers;
    public function __construct()
    {
        $this->suppliers = Supplier::where('status', 'active')->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $suppliers = $this->suppliers;
        return view('components.add-stock-modal', compact('suppliers'));
    }
}
