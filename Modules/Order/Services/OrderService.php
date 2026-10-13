<?php

namespace Modules\Order\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\NoProductsSelectedException;
use Modules\ChartOfAccounts\Services\AccountSettingService;
use Modules\ChartOfAccounts\Services\TransactionService;
use Modules\Order\Repositories\OrderProductRepository;
use Modules\Order\Repositories\OrderRepository;
use Modules\Inventory\Services\ProductService;
use Modules\ChartOfAccounts\Services\VoucherService;
use Carbon\Carbon;

class OrderService
{
    protected $orders;
    protected $orderProducts;
    protected $products;
    protected $transactions;
    protected $accountSettings;
    protected $voucherService;

    public function __construct(
        OrderRepository $orders,
        OrderProductRepository $orderProducts,
        ProductService $products,
        TransactionService $transactions,
        AccountSettingService $accountSettings,
        VoucherService $voucherService
    ) {
        $this->orders = $orders;
        $this->orderProducts = $orderProducts;
        $this->products = $products;
        $this->transactions = $transactions;
        $this->accountSettings = $accountSettings;
        $this->voucherService = $voucherService;
    }

    public function getOrders($request)
    {
        return $this->orders->dataTable($this->orders->query()->filter()->where('status', '!=', 'cancelled'));
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
            throw new NoProductsSelectedException();
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
        $voucher_type = $this->voucherService->getVoucherTypeByShort('INV');
        $voucher_no = $this->voucherService->nextVoucherNo('INV');
        $voucher = $this->voucherService->createVoucher([
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
            'inv_voucher_type' => 'voucher',
            'payment_method' => $payment_method,
            'company_id' => auth()->user()->company_id,
            'reference' => @$params["reference"]
        ]);
        $order->refresh();
        foreach ($params["product"] as $key => $product) {
            $last_transaction_key++;
            $quantity = $params["quantity"][$key];
            $price = $params["price"][$key];
            $product = $this->products->getProductByUid($product);
            $amount = $quantity * $price;
            $amount_ = $quantity * $product->purchase_price;
            if ($quantity > $product->stock) {
                throw new InsufficientStockException();
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
            $this->products->updateStockByUid($product->uid, $product->stock - $quantity);
            $this->orderProducts->create([
                'product_id' => $product->id,
                'product_type' => 'product',
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

        $this->accountSettings->setAccountBalance($recievable_account->id, $recievable_account->balance + $revenue_amount);
        $this->accountSettings->setAccountBalance($revenue_account->id, $revenue_account->balance + $revenue_amount);
        $this->accountSettings->setAccountBalance($inventory_account->id, $inventory_account->balance - $expense_amount);
        $this->accountSettings->setAccountBalance($expense_account->id, $expense_account->balance + $expense_amount);
        $this->voucherService->updateVoucher($voucher->id, ['amount' => $revenue_amount]);
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
            $this->products->updateStock($product->id, $product->stock + $orderProduct->quantity);
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
            $voucher_type = $this->voucherService->getVoucherTypeByShort('CRV');
            $voucher_no = $this->voucherService->nextVoucherNo('CRV');
            $voucher = $this->voucherService->createVoucher([
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
            $this->accountSettings->setAccountBalance($recievable_account->id, $recievable_account->balance - $total_amount);
            $this->accountSettings->setAccountBalance($office_account->id, $office_account->balance + $total_amount);
            $this->orders->query()->where('id', $order->id)->update([
                'status' => 'paid',
                'pay_voucher_id' => $voucher->id,
                'pay_voucher_type' => 'voucher'
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
            $this->products->updateStock($product->id, $product->stock + $orderProduct->quantity);
        }
        $this->orders->query()->where('id', $order->id)->update([
            'status' => 'refunded'
        ]);
        return $order->refresh();
    }
}
