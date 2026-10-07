<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    protected $service;

    public function __construct(PurchaseOrderService $service = null)
    {
        $this->service = $service ?? app(PurchaseOrderService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getPurchaseOrders($request);
        }
        return view('purchase_orders.index');
    }

    public function create()
    {
        $suppliers = $this->service->getActiveSuppliers();
        return view('purchase_orders.create', compact('suppliers'));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'New Stock Added',
                'event' => [
                    'name' => 'reload'
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        return view('purchase_orders.print', compact('purchaseOrder'));
    }

    public function refund(PurchaseOrder $purchaseOrder, Request $request)
    {
        DB::beginTransaction();
        try {
            $this->service->refundOrder($purchaseOrder);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order Refunded',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
