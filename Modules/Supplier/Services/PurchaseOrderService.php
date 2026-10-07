<?php

namespace Modules\Supplier\Services;

use App\Exceptions\NoProductsSelectedException;
use Modules\Supplier\Models\PurchaseOrder;
use Modules\ChartOfAccounts\Services\AccountSettingService;
use Modules\Supplier\Services\SupplierService;
use Modules\ChartOfAccounts\Services\VoucherService;
use Modules\Inventory\Services\ProductService;
use Modules\Supplier\Repositories\PurchaseOrderRepository;
use Modules\Inventory\Services\StockHistoryService;
use Modules\Supplier\Repositories\SupplierRepository;
use Modules\ChartOfAccounts\Services\TransactionService;
use Modules\Supplier\Repositories\VoucherSupplierRepository;

class PurchaseOrderService
{
    protected $purchaseOrders;
    protected $suppliers;
    protected $voucherService;
    protected $transactions;
    protected $voucherSuppliers;
    protected $products;
    protected $stockHistories;
    protected $accountSettings;

    public function __construct(
        PurchaseOrderRepository $purchaseOrders,
        SupplierRepository $suppliers,
        VoucherSupplierRepository $voucherSuppliers,
        TransactionService $transactions,
        ProductService $products,
        StockHistoryService $stockHistories,
        AccountSettingService $accountSettings,
        VoucherService $voucherService
    ) {
        $this->purchaseOrders = $purchaseOrders;
        $this->suppliers = $suppliers;
        $this->voucherService = $voucherService;
        $this->voucherSuppliers = $voucherSuppliers;
        $this->transactions = $transactions;
        $this->accountSettings = $accountSettings;
        $this->products = $products;
        $this->stockHistories = $stockHistories;
    }

    public function getPurchaseOrders($request)
    {
        if ($request->supplier) {
            return $this->purchaseOrders->query()->filter()->whereHas('supplier', function ($query) use ($request) {
                $query->where('uid', $request->supplier);
            })->with('supplier')->dataTable();
        }
        return $this->purchaseOrders->query()->filter()->with('supplier')->dataTable();
    }

    public function getActiveSuppliers()
    {
        return $this->suppliers->query()->filter()->where('status', 'active')->get();
    }

    public function store($request)
    {
        $purchase_order = $this->placePurchaseOrder($request->all());
        if ($request->isPaid) {
            app(SupplierService::class)->payAmountToSupplier([
                'amount' => $purchase_order->amount,
                'supplier' => $request->supplier
            ]);
        }
        return $purchase_order;
    }

    public function placePurchaseOrder($params): PurchaseOrder
    {
        if ($params["product"] == false || sizeof($params["product"]) == 0) {
            throw new NoProductsSelectedException();
        }
        $total_amount = 0;
        $last_transaction_key = 0;
        $date = date('Y-m-d');
        $voucher_type = $this->voucherService->getVoucherTypeByShort('APV');
        $accountSettings = $this->accountSettings;
        $inventory_account = $accountSettings->getAccount("PIA");
        $liability_account = $accountSettings->getAccount("VPA");
        $supplier = $this->suppliers->query()->where('uid', $params['supplier'])->first();
        $voucher_no = $this->voucherService->nextVoucherNo('APV');
        $voucher = $this->voucherService->createVoucher([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => 0,
            'type' => 'multiple',
            'description' => 'New Stock Added',
            'created_by' => auth()->user()->id
        ]);
        $voucher->refresh();
        $this->voucherSuppliers->create([
            'voucher_id' => $voucher->id,
            'supplier_id' => $supplier->id
        ]);
        $purchase_order = $this->purchaseOrders->create([
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
            $product = $this->products->getProductByUid($product);
            $amount = $quantity * $purchase;
            $this->transactions->create([
                'transaction_no' => $voucher_no . '-' . $last_transaction_key,
                'voucher_id' => $voucher->id,
                'account_id' => $inventory_account->id,
                'amount' => $amount,
                'type' => 'debit',
                'source' => 'stock added to inventory',
                'description' => $product->name . 'x' . $quantity . ' added to inventory',
            ]);
            $this->products->updateDetails($product->id, [
                'unit_price' => $retail,
                'purchase_price' => $purchase,
                'stock' => $product->stock + $quantity,
            ]);
            $this->stockHistories->create([
                'product_id' => $product->id,
                'purchase_order_id' => $purchase_order->id,
                'quantity' => $quantity,
                'purchase' => $purchase,
                'retail' => $retail,
            ]);
            $total_amount += $amount;
        }
        $last_transaction_key++;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' => $liability_account->id,
            'amount' => $total_amount,
            'type' => 'credit',
            'source' => 'stock added to inventory',
            'description' => 'Liability for new stock',
        ]);
        $this->accountSettings->setAccountBalance($liability_account->id, $liability_account->balance + $total_amount);
        $this->accountSettings->setAccountBalance($inventory_account->id, $inventory_account->balance + $total_amount);
        $this->voucherService->updateVoucher($voucher->id, ['amount' => $total_amount]);
        $this->suppliers->query()->where('id', $supplier->id)->update([
            'balance' => $supplier->balance + $total_amount
        ]);
        $this->purchaseOrders->query()->where('id', $purchase_order->id)->update([
            'amount' => $total_amount
        ]);
        return $purchase_order->refresh();
    }

    public function refundOrder($purchaseOrder)
    {
        $voucher = $purchaseOrder->voucher;
        $supplier = $purchaseOrder->supplier;
        foreach ($purchaseOrder->stockHistories as $stock) {
            $product = $this->products->getProductById($stock->product_id);
            $this->products->updateStock($product->id, $product->stock - $stock->quantity);
        }
        app(VoucherService::class)->voidVoucher($voucher);
        $this->suppliers->query()->where('id', $supplier->id)->update([
            'balance' => $supplier->balance - $purchaseOrder->amount
        ]);
        $this->purchaseOrders->query()->where('id', $purchaseOrder->id)->update([
            'status' => 'refunded'
        ]);
        return $purchaseOrder->refresh();
    }
}
