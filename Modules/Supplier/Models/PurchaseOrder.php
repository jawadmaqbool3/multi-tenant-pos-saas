<?php

namespace Modules\Supplier\Models;

use App\Models\UIDModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrder extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->order_no = 'ODR-' . Carbon::now()->format('ymd') . str_pad((PurchaseOrder::count() + 1), 4, 0, STR_PAD_LEFT);
            $model->user_id  = auth()->user()->id;
        });
    }
    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }
    public function scopeDataTable($query)
    {
        $orders = $query;
        return DataTables::of($orders)
            ->addIndexColumn()
            ->editColumn('status', function ($order) {
                $text = ucwords($order->status);
                if ($order->status == 'generated') {
                    $color = 'btn-success';
                } else {
                    $color = 'btn-danger';
                }
                return '<button class="btn btn-sm ' . $color . '">' . $text . '</button>';
            })
            ->addColumn('action', function ($order) {
                $btns = [];
                if (auth()->user()->hasPermission('print purchase orders')) {
                    $btns[] = '<a title="print" target="_blank" href="' . route('purchase.order.print', $order->uid) . '"  class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                    <span class="svg-icon svg-icon-3">
                    <i class="fonticon-printer fs-3"></i>
                    </span>
                    </a>';
                }
                if (auth()->user()->hasPermission('refund purchase orders') && $order->status == "generated") {
                    $btns[] = '<a href="#" data-modal_id="' . $order->id . '" data-modal_name="' . $order->order_no . '"  data-bs-toggle="modal" data-bs-target="#refund_order_modal" data-bs-toggle="tooltip" tabindex="0" title="Order Refund" data-base-url="' . route('purchase.order.refund', $order->uid) . '"  class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
            <span class="svg-icon svg-icon-3">
            <i class="bi bi-box-arrow-left"></i>
            </span>
        </a>';
                }
                if (count($btns)) {
                    return implode($btns);
                } else {
                    return null;
                }
            })
            ->editColumn('created_at', function ($order) {
                return Carbon::create($order->created_at)->format('d M, Y h:i A');
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }


    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function voucher()
    {
        return $this->hasOne(Voucher::class, 'id', 'voucher_id');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'purchase_order_id', 'id');
    }
}
