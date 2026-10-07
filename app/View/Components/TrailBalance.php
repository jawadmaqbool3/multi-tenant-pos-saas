<?php

namespace App\View\Components;

use Modules\ChartOfAccounts\Models\Transaction;
use Illuminate\View\Component;

class TrailBalance extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $debit = Transaction::where('type', 'debit')->sum('amount');
        $credit = Transaction::where('type', 'credit')->sum('amount');
        $balance = $debit - $credit;
        return view('components.trail-balance', compact('balance'));
    }
}
