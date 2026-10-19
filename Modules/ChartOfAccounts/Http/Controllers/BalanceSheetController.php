<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ChartOfAccounts\Services\BalanceSheetService;

class BalanceSheetController extends Controller
{
    protected $service;

    public function __construct(BalanceSheetService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the balance sheet. AJAX requests return only the rendered table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $asOf = $request->as_of ?: date('Y-m-d');
        $balanceSheet = $this->service->getBalanceSheet($asOf);

        if ($request->ajax()) {
            $table = view('chartofaccounts::reports.balance_sheet._table', compact('balanceSheet'))->render();

            return response([
                'success' => true,
                'table' => $table,
            ]);
        }

        return view('chartofaccounts::reports.balance_sheet.index', compact('balanceSheet', 'asOf'));
    }
}
