<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\ChartOfAccounts\Models\VoucherType;
use Modules\ChartOfAccounts\Http\Requests\StoreVoucherTypeRequest;
use Modules\ChartOfAccounts\Http\Requests\UpdateVoucherTypeRequest;
use Modules\ChartOfAccounts\Services\VoucherTypeService;
use Illuminate\Http\Request;

class VoucherTypeController extends Controller
{
    protected $service;

    public function __construct(VoucherTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getVoucherTypes();
        }
        $accounts = $this->service->getAccounts();
        return view('chartofaccounts::voucher_types.index', compact('accounts'));
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
    public function store(StoreVoucherTypeRequest $request)
    {
        $this->service->store($request);
        return response([
            'success' => true,
            'message' => 'New Voucher Type Added',
            'table_reload' => true,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVoucherTypeRequest $request, VoucherType $voucherType)
    {
        $this->service->update($request, $voucherType);
        return response([
            'success' => true,
            'message' => 'New Voucher Type Added',
            'table_reload' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(VoucherType $voucherType)
    {
        $this->service->delete($voucherType);
        return response([
            'success' => true,
            'message' => 'Voucher Type Deleted',
            'table_reload' => true,
        ]);
    }
}
