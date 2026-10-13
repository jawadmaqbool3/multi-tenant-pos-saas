<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\ChartOfAccounts\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    protected $service;
    protected $voucher;

    public function __construct($voucher = null)
    {
        $this->voucher = $voucher;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Modules\ChartOfAccounts\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show($voucher = null)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Modules\ChartOfAccounts\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function edit($voucher = null)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $voucher = null)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($voucher = null)
    {
        //
    }


    public function print($voucher = null)
    {
        return view('chartofaccounts::vouchers.print', compact('voucher'));
    }

    public function voidVoucher()
    {
        return $this->service->voidVoucher($this->voucher);
    }
}
