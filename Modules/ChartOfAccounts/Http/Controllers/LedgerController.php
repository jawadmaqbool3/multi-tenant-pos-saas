<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ChartOfAccounts\Services\LedgerService;

class LedgerController extends Controller
{
    protected $service;

    public function __construct(LedgerService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the ledger. AJAX requests return only the rendered table so the
     * page can refresh it without a full reload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $ledger = $request->account
                ? $this->service->getLedger($request->account, $request->from, $request->to)
                : null;

            $table = view('chartofaccounts::reports.ledger._table', compact('ledger'))->render();

            return response([
                'success' => true,
                'table' => $table,
            ]);
        }

        $accounts = $this->service->getAccounts();

        $selected = $request->account ?: 'all';
        $from = $request->from ?: now()->startOfMonth()->toDateString();
        $to = $request->to ?: now()->endOfMonth()->toDateString();

        return view('chartofaccounts::reports.ledger.index', compact('accounts', 'selected', 'from', 'to'));
    }
}
