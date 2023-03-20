<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AccountLevel4;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherType;
use Carbon\Carbon;
use Exception;
use AccountSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Order::filter()->where('status', '!=', 'cancelled')->dataTable();
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

            $order = $this->placeSaleOrder($request->all());
            if ($request->isPaid) {
                $this->paySaleOrder($order);
            }
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'redirect' => $request->saveAndPrint == "true",
                'url' => route('sales.order.print', $order->uid),
                'message' => 'New order is placed',
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

    public function placeSaleOrder($params): Order
    {
        if ($params["product"] == false || sizeof($params["product"]) == 0) {
            throw new CustomException('Select some products first', 'error');
        }
        $payment_method = null;
        if (isset($params['isPaid'])) {
            $payment_method = $params['payment_method'];
        }
        $revenue_amount = 0;
        $expense_amount = 0;
        $last_transaction_key = 0;
        $date = Carbon::now()->format('Y-m-d');
        $due = Carbon::now()->addDays(7)->format('Y-m-d');
        $accountSettings = new  AccountSettingController();
        $recievable_account = $accountSettings->getAccount("SRA");
        $revenue_account = $accountSettings->getAccount("SPRA");
        $inventory_account = $accountSettings->getAccount("PIA");
        $expense_account = $accountSettings->getAccount("PEA");
        $voucher_type = VoucherType::filter()->where('short', 'INV')->first();
        $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
        $voucher = Voucher::create([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => 0,
            'type' => 'multiple',
            'description' => 'On store invoice generated',
            'created_by' => auth()->user()->id,
        ]);
        $voucher->refresh();
        $order =  Order::create([
            'order_no' => $voucher_no,
            'status' => 'generated',
            'date' => $date,
            'due_date' => $due,
            'inv_voucher_id' => $voucher->id,
            'payment_method' => $payment_method,
            'company_id' => auth()->user()->company_id,
            'reference' => @$params["reference"]
        ]);
        $order->refresh();
        foreach ($params["product"] as $key => $product) {
            $last_transaction_key++;
            $quantity = $params["quantity"][$key];
            $price = $params["price"][$key];
            $product = Product::where('uid', $product)->first();
            $amount = $quantity * $price;
            $amount_ = $quantity * $product->purchase_price;
            if ($quantity > $product->stock) {
                throw new CustomException("Cannot order more than available stock", 'error');
            }
            Transaction::create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' =>  $recievable_account->id,
                'amount' => $amount,
                'type' => 'debit',
                'source' => 'instore invoice receivable',
                'description' =>  $product->name . 'x' . $quantity . ' is marked as recievable'
            ]);
            Product::where('uid', $product->uid)->update([
                'stock' => $product->stock  - $quantity,
            ]);
            OrderProduct::create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'price' => $price,
                'quantity' => $quantity,
            ]);
            $revenue_amount += $amount;
            $expense_amount += $amount_;
        }
        $last_transaction_key++;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $revenue_account->id,
            'amount' => $revenue_amount,
            'type' => 'credit',
            'source' => 'instore invoice revenue',
            'description' => 'Revenue against instore invoice',
        ]);
        $last_transaction_key++;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $inventory_account->id,
            'amount' => $expense_amount,
            'type' => 'credit',
            'source' => 'instore inventory credit',
            'description' => 'Products reserved for order',
        ]);
        $last_transaction_key++;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $expense_account->id,
            'amount' => $expense_amount,
            'type' => 'debit',
            'source' => 'instore invoice expense',
            'description' => 'Products purchase amount marked as expense',
        ]);

        AccountLevel4::where('id', $recievable_account->id)->update([
            'balance' => $recievable_account->balance + $revenue_amount,
        ]);
        AccountLevel4::where('id', $revenue_account->id)->update([
            'balance' => $revenue_account->balance + $revenue_amount,
        ]);
        AccountLevel4::where('id', $inventory_account->id)->update([
            'balance' => $inventory_account->balance - $expense_amount,
        ]);
        AccountLevel4::where('id', $expense_account->id)->update([
            'balance' => $expense_account->balance + $expense_amount,
        ]);
        Voucher::where('id', $voucher->id)->update([
            'amount' => $revenue_amount
        ]);
        Order::where('id', $order->id)->update([
            'amount' => $revenue_amount
        ]);
        return $order->refresh();
    }

    public function print(Order $order)
    {
        return view('sales_orders.print', compact('order'));
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::where('uid', $id)->with('invVoucher')->first();
            $this->cancelOrder($order);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order reverted',
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

    public function cancelOrder(Order $order): Order
    {
        $voucher = $order->invVoucher;
        $voucherController = new VoucherController($voucher);
        $voucherController->voidVoucher();
        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            Product::where('id', $product->id)->update([
                'stock' => $product->stock + $orderProduct->quantity
            ]);
        }
        Order::where('id', $order->id)->update([
            'status' => 'cancelled'
        ]);
        return $order->refresh();
    }

    public function paySaleOrder(Order $order): Order
    {

        $accountSettings = new  AccountSettingController();
        $recievable_account = $accountSettings->getAccount("SRA");
        $office_account = $accountSettings->getAccount("OSA");



        $date = Carbon::now()->format('Y-m-d');
        $total_amount =  $order->amount;
        if ($total_amount) {
            $voucher_type = VoucherType::filter()->where('short', 'CRV')->first();
            $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
            $voucher = Voucher::create([
                'voucher_no' => $voucher_no,
                'date' => $date,
                'voucher_type_id' => $voucher_type->id,
                'amount' => $total_amount,
                'type' => 'single',
                'description' => 'On store invoice payment reciept',
                'created_by' => auth()->user()->id
            ]);
            $voucher->refresh();
            $last_transaction_key = 0;
            $last_transaction_key++;
            Transaction::create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' =>  $office_account->id,
                'amount' => $total_amount,
                'type' => 'debit',
                'source' => 'instore invoice payment receipt',
                'description' =>  'Payment received against ' . $order->order_no
            ]);
            $last_transaction_key++;
            Transaction::create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' =>  $recievable_account->id,
                'amount' => $total_amount,
                'type' => 'credit',
                'source' => 'instore invoice credit recievable',
                'description' =>  'Amount deducted from recievable against ' . $order->order_no
            ]);
            AccountLevel4::where('id', $recievable_account->id)->update([
                'balance' => $recievable_account->balance - $total_amount,
            ]);
            AccountLevel4::where('id', $office_account->id)->update([
                'balance' => $office_account->balance + $total_amount,
            ]);
            Order::where('id', $order->id)->update([
                'status' => 'paid',
                'pay_voucher_id' => $voucher->id
            ]);
            return $order->refresh();
        }
    }
    public function markPaid(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $order =  $this->paySaleOrder($order);
            Order::where('id', $order->id)->update([
                'payment_method' => $request->payment_method
            ]);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Order marked as paid',
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

    public function refund($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::where('uid', $id)->with('invVoucher')->first();
            $this->refundOrder($order);
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

    public function refundOrder(Order $order)
    {
        $invVoucher = $order->invVoucher;
        $payVoucher = $order->payVoucher;
        $voucherController = new VoucherController($invVoucher);
        $voucherController->voidVoucher();
        $voucherController = new VoucherController($payVoucher);
        $voucherController->voidVoucher();
        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            Product::where('id', $product->id)->update([
                'stock' => $product->stock + $orderProduct->quantity
            ]);
        }
        Order::where('id', $order->id)->update([
            'status' => 'refunded'
        ]);
        return $order->refresh();
    }
}
