<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AccountSelector extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    protected $accounts;
    protected $isRequired;
    protected $name;
    protected $class;
    protected $id;
    protected $accountLevel;
    protected $selectLevel;
    protected $selected;
    public function __construct($account, $accountLevel = null, $selectLevel = null,  $isRequired = true, $selected = null, $id = null, $name, $class = null)
    {
        $this->accounts = $account;
        $this->selected = $selected;
        $this->isRequired = $isRequired;
        $this->id = $id;
        $this->accountLevel = $accountLevel;
        $this->selectLevel = $selectLevel;
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $accounts = $this->accounts;
        $accountLevel = $this->accountLevel;
        $isRequired = $this->isRequired;
        $id = $this->id;
        $name = $this->name;
        $selectLevel = $this->selectLevel;
        $selected = $this->selected;
        $class = $this->class;
        return view('components.account-selector', compact(
            'accounts',
            'class',
            'isRequired',
            'id',
            'name',
            'accountLevel',
            'selected',
            'selectLevel'
        ));
    }
}
