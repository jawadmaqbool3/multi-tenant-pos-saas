<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $service;

    public function __construct(OrderService $service)
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
            return $this->service->getOrders($request);
        }
        return view('sales_orders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sales_orders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $order = $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'redirect' => $request->saveAndPrint == "true",
                'url' => route('sales.order.print', $order->uid),
                'message' => 'New order is placed',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function print(Order $order)
    {
        return view('sales_orders.print', compact('order'));
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $this->service->cancel($id);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order reverted',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function markPaid(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $this->service->markPaid($order, $request->payment_method);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order marked as paid',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function refund($id)
    {
        DB::beginTransaction();
        try {
            $this->service->refund($id);
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
