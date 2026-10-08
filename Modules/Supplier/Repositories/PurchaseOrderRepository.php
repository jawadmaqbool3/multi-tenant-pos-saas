<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;
use Carbon\Carbon;

use Modules\Supplier\Models\PurchaseOrder;

class PurchaseOrderRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new PurchaseOrder());

        $this->dataTableEditColumns = [
            'status' => function ($order) {
                return $this->dt->statusButton($order->status, $order->status == 'generated' ? 'btn-success' : 'btn-danger');
            },
            'created_at' => function ($order) {
                return Carbon::create($order->created_at)->format('d M, Y h:i A');
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($order) {
                $btns = [];
                if (auth()->user()->hasPermission('print purchase orders')) {
                    $btns[] = '<a title="print" target="_blank" href="' . route('purchase.order.print', $order->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3"><i class="fonticon-printer fs-3"></i></span>
                    </a>';
                }
                if (auth()->user()->hasPermission('refund purchase orders') && $order->status == "generated") {
                    $btns[] = '<a href="#" data-modal_id="' . $order->id . '" data-modal_name="' . $order->order_no . '" data-bs-toggle="modal" data-bs-target="#refund_order_modal" tabindex="0" title="Order Refund" data-base-url="' . route('purchase.order.refund', $order->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3"><i class="bi bi-box-arrow-left"></i></span>
                    </a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
