<?php

namespace Modules\ChartOfAccounts\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;
use Yajra\DataTables\Facades\DataTables;

class Voucher extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    public function voucherType()
    {
        return $this->hasOne(VoucherType::class, 'id', 'voucher_type_id');
    }
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }


    public function scopeFilter($query)
    {
        return $query->whereHas('voucherType', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }


    public function scopeSupplierLedgerDataTable($vouchers)
    {
        return DataTables::of($vouchers)
            ->addIndexColumn()
            ->editColumn('date', function ($voucher) {
                return Carbon::createFromFormat('Y-m-d', $voucher->date)->format('d M, Y');
            })
            ->editColumn('description', function ($voucher) {
                return ucwords($voucher->description);
            })
            ->editColumn('amount', function ($voucher) {
                if ($voucher->voucherType->short == 'APV') {
                    return "<span class='text-danger'>" . $voucher->amount . "</span>";
                } elseif ($voucher->voucherType->short == 'BPV') {
                    return "<span class='text-success'>" . $voucher->amount . "</span>";
                }
            })
            ->rawColumns(['amount'])
            ->make(true);
    }


    public function scopeDataTable($query)
    {
        $vouchers = $query->with('creator');
        return DataTables::of($vouchers)
            ->addIndexColumn()
            ->editColumn('date', function ($voucher) {
                return Carbon::createFromFormat('Y-m-d', $voucher->date)->format('d M, Y');
            })
            ->addColumn('action', function ($voucher) {
                $btns = [];
                $btns[] = '<a href="' . route('print.voucher', $voucher->uid) . '"  class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                    <span class="svg-icon svg-icon-3">
                        <i class="bi bi-printer"></i>
                    </span>
                </a>';
                if (count($btns)) {
                    return implode($btns);
                } else {
                    return null;
                }
            })
            ->rawColumns(['action', 'image', 'status'])
            ->make(true);
    }









    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'voucher_id', 'id');
    }

    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            VoucherSupplier::class,
            'voucher_id',
            'id',
            'id',
            'supplier_id',
        );
    }
}
