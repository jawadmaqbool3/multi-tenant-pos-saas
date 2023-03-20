<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AccountLevel4;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherSupplier;
use App\Models\VoucherType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AccountSettingController;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->supplier) {
                return PurchaseOrder::filter()->whereHas('supplier', function ($query) use ($request) {
                    $query->where('uid', $request->supplier);
                })->with('supplier')->dataTable();
            }
            return PurchaseOrder::filter()->with('supplier')->dataTable();
        }
        return view('purchase_orders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::filter()->where('status', 'active')->get();
        return view('purchase_orders.create', compact('suppliers'));
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
            $this->validate($request, [
                'supplier' => 'required',
                'product' => 'required',
                'quantity' => 'required',
                'purchase' => 'required',
                'retail' => 'required',
            ]);
            $purchase_order =  $this->placePurchaseOrder($request->all());
            if ($request->isPaid) {
                $supplierController = new SupplierController();
                $supplierController->payAmountToSupplier([
                    'amount' => $purchase_order->amount,
                    'supplier' => $request->supplier
                ]);
            }
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'New Stock Added',
                'event' => [
                    'name' => 'reload'
                ]
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }


    public function placePurchaseOrder($params): PurchaseOrder
    {
        if ($params["product"] == false || sizeof($params["product"]) == 0) {
            throw new CustomException('Select some products first', 'error');
        }
        $total_amount = 0;
        $last_transaction_key = 0;
        $date = date('Y-m-d');
        $voucher_type = VoucherType::filter()->where('short', 'APV')->first();
        $accountSettings = new  AccountSettingController();
        $inventory_account = $accountSettings->getAccount("PIA");
        $liability_account = $accountSettings->getAccount("VPA");
        $supplier = Supplier::where('uid', $params['supplier'])->first();
        $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
        $voucher = Voucher::create([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => 0,
            'type' => 'multiple',
            'description' => 'New Stock Added',
            'created_by' => auth()->user()->id
        ]);
        $voucher->refresh();
        VoucherSupplier::create([
            'voucher_id' => $voucher->id,
            'supplier_id' => $supplier->id
        ]);
        $purchase_order = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'voucher_id' => $voucher->id,
            'company_id' => auth()->user()->company_id,
            'reference' => @$params["reference"]
        ]);
        $purchase_order->refresh();
        foreach ($params["product"] as $key => $product) {
            $last_transaction_key++;
            $quantity = $params["quantity"][$key];
            $purchase = $params["purchase"][$key];
            $retail = $params["retail"][$key];
            $product = Product::where('uid', $product)->first();
            $amount = $quantity * $purchase;
            Transaction::create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' =>  $inventory_account->id,
                'amount' => $amount,
                'type' => 'debit',
                'source' => 'stock added to inventory',
                'description' => $product->name . 'x' . $quantity . ' added to inventory',
            ]);
            Product::where('uid', $product->uid)->update([
                'unit_price' => $retail,
                'purchase_price' => $purchase,
                'stock' => $product->stock + $quantity,
            ]);
            StockHistory::create([
                'product_id' => $product->id,
                'purchase_order_id' => $purchase_order->id,
                'quantity' => $quantity,
                'purchase' => $purchase,
                'retail' => $retail,
            ]);
            $total_amount += $amount;
        }
        $last_transaction_key++;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $liability_account->id,
            'amount' => $total_amount,
            'type' => 'credit',
            'source' => 'stock added to inventory',
            'description' => 'Liability for new stock',
        ]);
        AccountLevel4::where('id', $liability_account->id)->update([
            'balance' => $liability_account->balance + $total_amount,
        ]);
        AccountLevel4::where('id', $inventory_account->id)->update([
            'balance' => $inventory_account->balance + $total_amount,
        ]);
        Voucher::where('id', $voucher->id)->update([
            'amount' => $total_amount
        ]);
        Supplier::where('id', $supplier->id)->update([
            'balance' => $supplier->balance + $total_amount
        ]);
        PurchaseOrder::where('id', $purchase_order->id)->update([
            'amount' => $total_amount
        ]);
        return $purchase_order->refresh();
    }


    public function print(PurchaseOrder $purchaseOrder)
    {
        return view('purchase_orders.print', compact('purchaseOrder'));
    }


    public function refund(PurchaseOrder $purchaseOrder, Request $request)
    {
        DB::beginTransaction();
        try {
            $this->refundOrder($purchaseOrder);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order Refunded',
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    public function refundOrder($purchaseOrder)
    {
        $voucher = $purchaseOrder->voucher;
        $supplier = $purchaseOrder->supplier;
        foreach ($purchaseOrder->stockHistories as $stock) {
            $product = Product::where('id', $stock->product_id)->first();
            Product::where('id', $product->id)->update([
                "stock" => $product->stock - $stock->quantity
            ]);
        }
        $voucherController = new VoucherController($voucher);
        $voucherController->voidVoucher();
        Supplier::where('id', $supplier->id)->update([
            'balance' => $supplier->balance - $purchaseOrder->amount
        ]);
        PurchaseOrder::where('id', $purchaseOrder->id)->update([
            'status' => 'refunded'
        ]);
        return $purchaseOrder->refresh();
    }
}
