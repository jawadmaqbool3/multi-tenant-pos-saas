<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Voucher;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\PaySupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    protected $service;
    private $thumbWidth;
    private $thumbHeight;
    private $supplierThumbDirectory;
    public function __construct(SupplierService $service = null)
    {
        $this->service = $service ?: app(SupplierService::class);
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->supplierThumbDirectory = 'assets/media/suppliers/thumbs/';
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getSuppliers();
        }
        return view('suppliers.index');
    }

    public function ledger(Request $request, Supplier $supplier)
    {
        if ($request->ajax()) {
            return $this->service->getLedger($supplier);
        }
        throw new HttpException(404);
        // return view('suppliers.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $supplierThumbDirectory = $this->supplierThumbDirectory;
        return view('suppliers.create', compact('supplierThumbDirectory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSupplierRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Supplier Added',
                'redirect' => true,
                'url' => route('suppliers.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $totalStockProducts = $this->service->getTotalStockProducts();
        return view('suppliers.show', compact('supplier', 'totalStockProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $supplierThumbDirectory = $this->supplierThumbDirectory;
        return view('suppliers.edit', compact('supplier', 'supplierThumbDirectory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $supplier);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Supplier Updated',
                'redirect' => true,
                'url' => route('suppliers.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        $this->service->delete($supplier);
        return response([
            'success' => true,
            'message' => 'Supplier Deleted',
            'table_reload' => true,
        ]);
    }

    public function payAmountToSupplier($params): Voucher
    {
        return $this->service->payAmountToSupplier($params);
    }

    public function pay(PaySupplierRequest $request, Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            $this->service->payAmountToSupplier([
                'amount' => $request->amount,
                'supplier' => $supplier->uid
            ]);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Payment Completed',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
