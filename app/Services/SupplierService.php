<?php

namespace App\Services;

use App\Core\Helper;
use App\Exceptions\InsufficientSupplierBalanceException;
use App\Models\Voucher;
use App\Repositories\AccountLevel4Repository;
use App\Repositories\StockHistoryRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\VoucherRepository;
use App\Repositories\VoucherSupplierRepository;
use App\Repositories\VoucherTypeRepository;
use Carbon\Carbon;

class SupplierService
{
    protected $suppliers;
    protected $stockHistories;
    protected $vouchers;
    protected $voucherTypes;
    protected $voucherSuppliers;
    protected $transactions;
    protected $accountLevel4;
    protected $accountSettings;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $supplierThumbDirectory = 'assets/media/suppliers/thumbs/';

    public function __construct(
        SupplierRepository $suppliers,
        StockHistoryRepository $stockHistories,
        VoucherRepository $vouchers,
        VoucherTypeRepository $voucherTypes,
        VoucherSupplierRepository $voucherSuppliers,
        TransactionRepository $transactions,
        AccountLevel4Repository $accountLevel4,
        AccountSettingService $accountSettings
    ) {
        $this->suppliers = $suppliers;
        $this->stockHistories = $stockHistories;
        $this->vouchers = $vouchers;
        $this->voucherTypes = $voucherTypes;
        $this->voucherSuppliers = $voucherSuppliers;
        $this->transactions = $transactions;
        $this->accountLevel4 = $accountLevel4;
        $this->accountSettings = $accountSettings;
    }

    public function getSuppliers()
    {
        return $this->suppliers->query()->filter()->dataTable();
    }

    public function getLedger($supplier)
    {
        return $this->vouchers->query()->whereHas('supplier', function ($query) use ($supplier) {
            $query->where('supplier_id', $supplier->id);
        })->where('type', '!=', 'void')->supplierLedgerDataTable();
    }

    public function getTotalStockProducts()
    {
        return $this->stockHistories->query()->groupBy('product_id')->count();
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
        if ($request->thumbnail) {
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

    public function payAmountToSupplier($params): Voucher
    {
        $asset_account = $this->accountSettings->getAccount("OSA");
        $liability_account = $this->accountSettings->getAccount("VPA");
        $date = Carbon::now()->format('Y-m-d');
        $amount = $params['amount'];
        $supplier = $this->suppliers->query()->where('uid', $params['supplier'])->first();
        if ($amount > $supplier->balance) {
            throw new InsufficientSupplierBalanceException();
        }
        $voucher_type = $this->voucherTypes->query()->filter()->where('short', 'BPV')->first();
        $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
        $voucher = $this->vouchers->create([
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

        $this->accountLevel4->query()->where('id', $liability_account->id)->update([
            'balance' => $liability_account->balance -  $amount
        ]);
        $this->accountLevel4->query()->where('id', $asset_account->id)->update([
            'balance' => $asset_account->balance -  $amount
        ]);
        $this->suppliers->query()->where('id', $supplier->id)->update([
            'balance' => $supplier->balance - $amount
        ]);
        return $voucher->refresh();
    }
}
