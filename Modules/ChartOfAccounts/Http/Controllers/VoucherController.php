<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\ChartOfAccounts\Models\Voucher;
use Modules\ChartOfAccounts\Services\ChatOfAccountService;
use Modules\ChartOfAccounts\Services\VoucherService;
use Modules\ChartOfAccounts\Http\Requests\StoreVoucherRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = app(VoucherService::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getVouchers();
        }
        return view('chartofaccounts::vouchers.index');
    }

    /**
     * Show the manual voucher creation form.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = app(ChatOfAccountService::class)->getAccounts();
        $voucherTypes = $this->service->getVoucherTypesForSelect();

        return view('chartofaccounts::vouchers.create', compact('accounts', 'voucherTypes'));
    }

    /**
     * Store a manually created voucher with its journal lines.
     *
     * @param  \Modules\ChartOfAccounts\Http\Requests\StoreVoucherRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVoucherRequest $request)
    {
        DB::transaction(function () use ($request) {
            $this->service->createManualVoucher($request->validated());
        });

        return response()->json([
            'success' => true,
            'message' => 'Voucher created successfully.',
            'redirect' => true,
            'url' => route('vouchers.index'),
        ]);
    }

    /**
     * Void a voucher (reverse its balance impact).
     *
     * @param  \Modules\ChartOfAccounts\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function void(Voucher $voucher)
    {
        $voided = false;
        DB::transaction(function () use ($voucher, &$voided) {
            $voided = $this->service->voidVoucher($voucher);
        });

        if (!$voided) {
            return response()->json([
                'error' => true,
                'message' => 'This voucher cannot be voided.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher voided successfully.',
            'table_reload' => true,
        ]);
    }

    /**
     * Print a voucher with its journal lines.
     *
     * @param  \Modules\ChartOfAccounts\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function print(Voucher $voucher)
    {
        $voucher->load(['transactions.account', 'voucherType', 'creator']);

        return view('chartofaccounts::vouchers.print', compact('voucher'));
    }
}
