<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Exceptions\CustomException;
use App\Models\AccountLevel4;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherSupplier;
use App\Models\VoucherType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Controllers\AccountSettingController;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    private $thumbWidth;
    private $thumbHeight;
    private $supplierThumbDirectory;
    public function __construct()
    {
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->supplierThumbDirectory = 'assets/media/suppliers/thumbs/';
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Supplier::filter()->dataTable();
        }
        return view('suppliers.index');
    }

    public function ledger(Request $request, Supplier $supplier)
    {
        if ($request->ajax()) {
            return Voucher::whereHas('supplier', function ($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })->where('type', '!=', 'void')->supplierLedgerDataTable();
        }
        throw new HttpException(404);
        // return view('suppliers.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $supplierThumbDirectory = $this->supplierThumbDirectory;
        return view('suppliers.create', compact('supplierThumbDirectory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $thumbnail = '';
            if ($request->file('thumbnail')) {
                $thumbnail = Helper::uploadfile([
                    'file' => $request->file('thumbnail'),
                    'path' =>  $this->supplierThumbDirectory,
                    'width' => $this->thumbWidth,
                    'height' => $this->thumbHeight,
                ]);
            }
            Supplier::create([
                'name' => $request->name,
                'status' => $request->status,
                'thumbnail' =>  $thumbnail,
                'phone_no' => $request->phone_no,
                'whatsapp_no' => $request->whatsapp_no,
                'cell_no' => $request->cell_no,
                'company_id' => auth()->user()->company_id
            ]);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Supplier Added',
                'redirect' => true,
                'url' => route('suppliers.index'),
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
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $totalStockProducts = StockHistory::groupBy('product_id')->count();
        return view('suppliers.show', compact('supplier', 'totalStockProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $supplierThumbDirectory = $this->supplierThumbDirectory;
        return view('suppliers.edit', compact('supplier', 'supplierThumbDirectory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        DB::beginTransaction();
        try {
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

            Supplier::where('id', $supplier->id)->update([
                'name' => $request->name,
                'status' => $request->status,
                'thumbnail' =>  $thumbnail,
                'phone_no' => $request->phone_no,
                'whatsapp_no' => $request->whatsapp_no,
                'cell_no' => $request->cell_no
            ]);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Supplier Updated',
                'redirect' => true,
                'url' => route('suppliers.index'),
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        Helper::deleteExcept([
            'files' => [$supplier->thumbnail],
            'exceptions' => [],
            'path' => $this->supplierThumbDirectory
        ]);
        $supplier->delete();
        return response([
            'success' => true,
            'message' => 'Supplier Deleted',
            'table_reload' => true,
        ]);
    }

    public function payAmountToSupplier($params): Voucher
    {
        $accountSettings = new  AccountSettingController();
        $asset_account = $accountSettings->getAccount("OSA");
        $liability_account = $accountSettings->getAccount("VPA");
        $date = Carbon::now()->format('Y-m-d');
        $amount = $params['amount'];
        $supplier = Supplier::where('uid', $params['supplier'])->first();
        if ($amount > $supplier->balance) {
            throw new CustomException('Amount cannot be less than supplier balance', 'warning');
        }
        $voucher_type = VoucherType::filter()->where('short', 'BPV')->first();
        $voucher_no = $voucher_type->short . '-' . ($voucher_type->vouchers->count() + 1);
        $voucher = Voucher::create([
            'voucher_no' => $voucher_no,
            'date' => $date,
            'voucher_type_id' => $voucher_type->id,
            'amount' => $amount,
            'type' => 'single',
            'description' => 'purchase order payment',
            'created_by' => auth()->user()->id
        ]);
        $voucher->refresh();
        VoucherSupplier::create([
            'voucher_id' => $voucher->id,
            'supplier_id' => $supplier->id
        ]);

        $last_transaction_key = 1;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $liability_account->id,
            'amount' => $amount,
            'type' => 'debit',
            'source' => 'supplier liability knockoff',
            'description' => 'Amount of ' . $amount . ' ' . config('app.currency') . ' is reduced from liability account'
        ]);
        $last_transaction_key = 2;
        Transaction::create([
            'transaction_no' => $voucher_no . '-' . $last_transaction_key,
            'voucher_id' => $voucher->id,
            'account_id' =>  $asset_account->id,
            'amount' => $amount,
            'type' => 'credit',
            'source' => 'supplier asset knockoff',
            'description' => 'Amount of ' . $amount . ' ' . config('app.currency') . ' is paid to ' . $supplier->name
        ]);

        AccountLevel4::where('id', $liability_account->id)->update([
            'balance' => $liability_account->balance -  $amount
        ]);
        AccountLevel4::where('id', $asset_account->id)->update([
            'balance' => $asset_account->balance -  $amount
        ]);
        Supplier::where('id', $supplier->id)->update([
            'balance' => $supplier->balance - $amount
        ]);
        return $voucher->refresh();
    }

    public function pay(Request $request, Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'amount' => 'required',
            ]);
            $supplierController = new SupplierController();
            $supplierController->payAmountToSupplier([
                'amount' => $request->amount,
                'supplier' => $supplier->uid
            ]);
            DB::commit();
            return response([
                'success' => true,
                'table_reload' => true,
                'message' => 'Payment Completed',
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
}
