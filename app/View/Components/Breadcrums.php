<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Breadcrums extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $links;
    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.breadcrums');
    }
}
