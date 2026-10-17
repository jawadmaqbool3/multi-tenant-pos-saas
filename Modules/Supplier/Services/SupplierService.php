<?php

namespace Modules\Supplier\Services;

use App\Core\Helper;
use App\Exceptions\InsufficientSupplierBalanceException;
use Modules\ChartOfAccounts\Services\AccountSettingService;
use Modules\ChartOfAccounts\Services\TransactionService;
use Modules\ChartOfAccounts\Services\VoucherService;
use Modules\Inventory\Services\StockHistoryService;
use Modules\Supplier\Repositories\SupplierRepository;
use Modules\Supplier\Repositories\VoucherSupplierRepository;
use Carbon\Carbon;

class SupplierService
{
    protected $suppliers;
    protected $stockHistories;
    protected $voucherSuppliers;
    protected $transactions;
    protected $voucherService;
    protected $accountSettings;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $supplierThumbDirectory = 'assets/media/suppliers/thumbs/';

    public function __construct(
        SupplierRepository $suppliers,
        StockHistoryService $stockHistories,
        VoucherSupplierRepository $voucherSuppliers,
        TransactionService $transactions,
        AccountSettingService $accountSettings,
        VoucherService $voucherService
    ) {
        $this->suppliers = $suppliers;
        $this->stockHistories = $stockHistories;
        $this->voucherSuppliers = $voucherSuppliers;
        $this->transactions = $transactions;
        $this->accountSettings = $accountSettings;
        $this->voucherService = $voucherService;
    }

    public function getSuppliers()
    {
        return $this->suppliers->dataTable($this->suppliers->query()->filter());
    }

    public function getLedger($supplier)
    {
        $voucherIds = $this->voucherSuppliers->query()->where('supplier_id', $supplier->id)->pluck('voucher_id');
        return $this->voucherService->getVouchersByIds($voucherIds);
    }

    public function getTotalStockProducts()
    {
        return $this->stockHistories->groupedProductCount();
    }

    public function getSupplierStockProductCount(Supplier $supplier)
    {
        $purchaseOrderIds = $supplier->purchaseOrders()->pluck('id');
        return $this->stockHistories->distinctProductCountForPurchaseOrders($purchaseOrderIds);
    }

    public function store($request)
    {
        $thumbnail = '';
        if ($request->file('thumbnail')) {
            $thumbnail = Helper::uploadfile([
                'file' => $request->file('thumbnail'),
                'path' =>  $this->supplierThumbDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
                'field' => 'thumbnail',
            ]);
        }
        return $this->suppliers->create([
            'name' => $request->name,
            'status' => $request->status,
            'thumbnail' =>  $thumbnail,
            'phone_no' => $request->phone_no,
            'whatsapp_no' => $request->whatsapp_no,
            'cell_no' => $request->cell_no,
            'company_id' => auth()->user()->company_id
        ]);
    }

    public function update($request, $supplier)
    {
        $thumbnail = $supplier->thumbnail;
        if ($request->file('thumbnail')) {
            Helper::deleteExcept([
                'files' => [$supplier->thumbnail],
                'exceptions' => [],
                'path' => $this->supplierThumbDirectory
            ]);
            $thumbnail = Helper::uploadfile([
                'file' => $request->file('thumbnail'),
                'path' => $this->supplierThumbDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
                'field' => 'thumbnail',
            ]);
        }
        return $this->suppliers->query()->where('id', $supplier->id)->update([
            'name' => $request->name,
            'status' => $request->status,
            'thumbnail' =>  $thumbnail,
            'phone_no' => $request->phone_no,
            'whatsapp_no' => $request->whatsapp_no,
            'cell_no' => $request->cell_no
        ]);
    }

    public function delete($supplier)
    {
        Helper::deleteExcept([
            'files' => [$supplier->thumbnail],
            'exceptions' => [],
            'path' => $this->supplierThumbDirectory
        ]);
        return $this->suppliers->delete($supplier->id);
    }

    public function payAmountToSupplier($params)
    {
        $asset_account = $this->accountSettings->getAccount("OSA");
        $liability_account = $this->accountSettings->getAccount("VPA");
        $date = Carbon::now()->format('Y-m-d');
        $amount = $params['amount'];
        $supplier = $this->suppliers->query()->where('uid', $params['supplier'])->first();
        if ($amount > $supplier->balance) {
            throw new InsufficientSupplierBalanceException();
        }
        $voucher_type = $this->voucherService->getVoucherTypeByShort('BPV');
        $voucher_no = $this->voucherService->nextVoucherNo('BPV');
        $voucher = $this->voucherService->createVoucher([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => $amount,
            'type' => 'single',
            'description' => 'purchase order payment',
            'created_by' => auth()->user()->id
        ]);
        $voucher->refresh();
        $this->voucherSuppliers->create([
            'voucher_id' => $voucher->id,
            'supplier_id' => $supplier->id
        ]);

        $last_transaction_key = 1;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $liability_account->id,
            'amount' => $amount,
            'type' => 'debit',
            'source' => 'supplier liability knockoff',
            'description' => 'Amount of ' . $amount . ' ' . config('app.currency') . ' is reduced from liability account'
        ]);
        $last_transaction_key = 2;
        $this->transactions->create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $asset_account->id,
            'amount' => $amount,
            'type' => 'credit',
            'source' => 'supplier asset knockoff',
            'description' => 'Amount of ' . $amount . ' ' . config('app.currency') . ' is paid to ' . $supplier->name
        ]);

        $this->accountSettings->setAccountBalance($liability_account->id, $liability_account->balance - $amount);
        $this->accountSettings->setAccountBalance($asset_account->id, $asset_account->balance - $amount);
        $this->suppliers->query()->where('id', $supplier->id)->update([
            'balance' => $supplier->balance - $amount
        ]);
        return $voucher->refresh();
    }
}
