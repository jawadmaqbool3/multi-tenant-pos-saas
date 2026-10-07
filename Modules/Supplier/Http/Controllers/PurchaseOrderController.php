<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Supplier\Models\PurchaseOrder;
use Modules\Supplier\Http\Requests\StorePurchaseOrderRequest;
use Modules\Supplier\Services\PurchaseOrderService;
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
        return view('supplier::purchase_orders.index');
    }

    public function create()
    {
        $suppliers = $this->service->getActiveSuppliers();
        return view('supplier::purchase_orders.create', compact('suppliers'));
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
        return view('supplier::purchase_orders.print', compact('purchaseOrder'));
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
