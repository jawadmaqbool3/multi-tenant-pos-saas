<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Order;
use App\Repositories\AccountLevel4Repository;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\VoucherRepository;
use App\Repositories\VoucherTypeRepository;
use Carbon\Carbon;

class OrderService
{
    protected $orders;
    protected $orderProducts;
    protected $products;
    protected $transactions;
    protected $vouchers;
    protected $voucherTypes;
    protected $accountLevel4s;
    protected $accountSettings;
    protected $voucherService;

    public function __construct(
        OrderRepository $orders,
        OrderProductRepository $orderProducts,
        ProductRepository $products,
        TransactionRepository $transactions,
        VoucherRepository $vouchers,
        VoucherTypeRepository $voucherTypes,
        AccountLevel4Repository $accountLevel4s,
        AccountSettingService $accountSettings,
        VoucherService $voucherService
    ) {
        $this->orders = $orders;
        $this->orderProducts = $orderProducts;
        $this->products = $products;
        $this->transactions = $transactions;
        $this->vouchers = $vouchers;
        $this->voucherTypes = $voucherTypes;
        $this->accountLevel4s = $accountLevel4s;
        $this->accountSettings = $accountSettings;
        $this->voucherService = $voucherService;
    }

    public function getOrders($request)
    {
        return $this->orders->query()->filter()->where('status', '!=', 'cancelled')->dataTable();
    }

    public function store($request)
    {
        $order = $this->placeSaleOrder($request->all());
        if ($request->isPaid) {
            $this->paySaleOrder($order);
        }
        return $order;
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
        $recievable_account = $this->accountSettings->getAccount("SRA");
        $revenue_account = $this->accountSettings->getAccount("SPRA");
        $inventory_account = $this->accountSettings->getAccount("PIA");
        $expense_account = $this->accountSettings->getAccount("PEA");
        $voucher_type = $this->voucherTypes->query()->filter()->where('short', 'INV')->first();
        $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
        $voucher = $this->vouchers->create([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => 0,
            'type' => 'multiple',
            'description' => 'On store invoice generated',
            'created_by' => auth()->user()->id,
        ]);
        $voucher->refresh();
        $order = $this->orders->create([
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
            $product = $this->products->query()->where('uid', $product)->first();
            $amount = $quantity * $price;
            $amount_ = $quantity * $product->purchase_price;
            if ($quantity > $product->stock) {
                throw new CustomException("Cannot order more than available stock", 'error');
            }
            $this->transactions->create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' => $recievable_account->id,
                'amount' => $amount,
                'type' => 'debit',
                'source' => 'instore invoice receivable',
                'description' => $product->name . 'x' . $quantity . ' is marked as recievable'
            ]);
            $this->products->query()->where('uid', $product->uid)->update([
                'stock' => $product->stock - $quantity,
            ]);
            $this->orderProducts->create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'price' => $price,
                'quantity' => $quantity,
            ]);
            $revenue_amount += $amount;
            $expense_amount += $amount_;
        }
        $last_transaction_key++;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' => $revenue_account->id,
            'amount' => $revenue_amount,
            'type' => 'credit',
            'source' => 'instore invoice revenue',
            'description' => 'Revenue against instore invoice',
        ]);
        $last_transaction_key++;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' => $inventory_account->id,
            'amount' => $expense_amount,
            'type' => 'credit',
            'source' => 'instore inventory credit',
            'description' => 'Products reserved for order',
        ]);
        $last_transaction_key++;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' => $expense_account->id,
            'amount' => $expense_amount,
            'type' => 'debit',
            'source' => 'instore invoice expense',
            'description' => 'Products purchase amount marked as expense',
        ]);

        $this->accountLevel4s->query()->where('id', $recievable_account->id)->update([
            'balance' => $recievable_account->balance + $revenue_amount,
        ]);
        $this->accountLevel4s->query()->where('id', $revenue_account->id)->update([
            'balance' => $revenue_account->balance + $revenue_amount,
        ]);
        $this->accountLevel4s->query()->where('id', $inventory_account->id)->update([
            'balance' => $inventory_account->balance - $expense_amount,
        ]);
        $this->accountLevel4s->query()->where('id', $expense_account->id)->update([
            'balance' => $expense_account->balance + $expense_amount,
        ]);
        $this->vouchers->query()->where('id', $voucher->id)->update([
            'amount' => $revenue_amount
        ]);
        $this->orders->query()->where('id', $order->id)->update([
            'amount' => $revenue_amount
        ]);
        return $order->refresh();
    }

    public function cancel($id)
    {
        $order = $this->orders->query()->where('uid', $id)->with('invVoucher')->first();
        return $this->cancelOrder($order);
    }

    public function cancelOrder(Order $order): Order
    {
        $voucher = $order->invVoucher;
        $this->voucherService->voidVoucher($voucher);
        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $this->products->query()->where('id', $product->id)->update([
                'stock' => $product->stock + $orderProduct->quantity
            ]);
        }
        $this->orders->query()->where('id', $order->id)->update([
            'status' => 'cancelled'
        ]);
        return $order->refresh();
    }

    public function markPaid($order, $paymentMethod)
    {
        $order = $this->paySaleOrder($order);
        $this->orders->query()->where('id', $order->id)->update([
            'payment_method' => $paymentMethod
        ]);
        return $order;
    }

    public function paySaleOrder(Order $order): Order
    {
        $recievable_account = $this->accountSettings->getAccount("SRA");
        $office_account = $this->accountSettings->getAccount("OSA");

        $date = Carbon::now()->format('Y-m-d');
        $total_amount = $order->amount;
        if ($total_amount) {
            $voucher_type = $this->voucherTypes->query()->filter()->where('short', 'CRV')->first();
            $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
            $voucher = $this->vouchers->create([
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
            $this->transactions->create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' => $office_account->id,
                'amount' => $total_amount,
                'type' => 'debit',
                'source' => 'instore invoice payment receipt',
                'description' => 'Payment received against ' . $order->order_no
            ]);
            $last_transaction_key++;
            $this->transactions->create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' => $recievable_account->id,
                'amount' => $total_amount,
                'type' => 'credit',
                'source' => 'instore invoice credit recievable',
                'description' => 'Amount deducted from recievable against ' . $order->order_no
            ]);
            $this->accountLevel4s->query()->where('id', $recievable_account->id)->update([
                'balance' => $recievable_account->balance - $total_amount,
            ]);
            $this->accountLevel4s->query()->where('id', $office_account->id)->update([
                'balance' => $office_account->balance + $total_amount,
            ]);
            $this->orders->query()->where('id', $order->id)->update([
                'status' => 'paid',
                'pay_voucher_id' => $voucher->id
            ]);
            return $order->refresh();
        }
    }

    public function refund($id)
    {
        $order = $this->orders->query()->where('uid', $id)->with('invVoucher')->first();
        return $this->refundOrder($order);
    }

    public function refundOrder(Order $order)
    {
        $invVoucher = $order->invVoucher;
        $payVoucher = $order->payVoucher;
        $this->voucherService->voidVoucher($invVoucher);
        $this->voucherService->voidVoucher($payVoucher);
        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $this->products->query()->where('id', $product->id)->update([
                'stock' => $product->stock + $orderProduct->quantity
            ]);
        }
        $this->orders->query()->where('id', $order->id)->update([
            'status' => 'refunded'
        ]);
        return $order->refresh();
    }
}
