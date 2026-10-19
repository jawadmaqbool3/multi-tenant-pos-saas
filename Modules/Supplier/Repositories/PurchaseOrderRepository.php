<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;
use App\Support\Icon;
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
                return $this->dt->statusBadge($order->status, $order->status == 'generated' ? 'btn-success' : 'btn-danger');
            },
            'created_at' => function ($order) {
                return Carbon::create($order->created_at)->format('d M, Y h:i A');
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($order) {
                $btns = [];
                if (auth()->user()->hasPermission('print purchase orders')) {
                    $btns[] = '<a title="print" target="_blank" href="' . route('purchase.order.print', $order->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">'
                        . Icon::svg('printer', 16) .
                        '</a>';
                }
                if (auth()->user()->hasPermission('refund purchase orders') && $order->status == "generated") {
                    $btns[] = '<a href="#" data-modal_id="' . $order->id . '" data-modal_name="' . $order->order_no . '" data-bs-toggle="modal" data-bs-target="#refund_order_modal" tabindex="0" title="Order Refund" data-base-url="' . route('purchase.order.refund', $order->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">'
                        . Icon::svg('box-arrow-left', 16) .
                        '</a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * Open (not yet refunded) purchase orders for a company.
     *
     * @param  int  $companyId
     * @return int
     */
    public function pendingCount($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'generated')
            ->count();
    }

    /**
     * Total purchase orders for a company — the count that consumes the plan's
     * `purchase_orders` limit.
     *
     * @param  int  $companyId
     * @return int
     */
    public function countForCompany($companyId)
    {
        return $this->query()->where('company_id', $companyId)->count();
    }
}
