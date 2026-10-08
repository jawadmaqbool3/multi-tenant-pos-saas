<?php

namespace Modules\Order\Repositories;

use App\Repositories\BaseRepository;
use Carbon\Carbon;

use Modules\Order\Models\Order;

class OrderRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Order());

        $this->dataTableEditColumns = [
            'status' => function ($order) {
                if ($order->status == 'paid') {
                    $color = 'btn-success';
                } elseif ($order->status == 'generated') {
                    $color = 'btn-warning';
                } else {
                    $color = 'btn-danger';
                }
                return $this->dt->statusButton($order->status, $color);
            },
            'created_at' => function ($order) {
                return (new Carbon($order->created_at))->format('d M, Y h:m A');
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($order) {
                $btns = [];
                $btns[] = $this->printButton(route('sales.order.print', $order->uid), 'Print Invoice');
                if (auth()->user()->hasPermission('cancel order') && $order->status == 'generated') {
                    $btns[] = $this->orderActionButton('cancel_order_modal', 'Cancel Order', route('order.cancel', $order->uid), 'bi bi-arrow-counterclockwise', $order);
                }
                if (auth()->user()->hasPermission('mark paid') && $order->status == 'generated') {
                    $btns[] = $this->orderActionButton('mark_paid_order_modal', 'Mark Paid', route('order.mark.paid', $order->uid), 'bi bi-check', $order);
                }
                if (auth()->user()->hasPermission('order refund') && $order->status == 'paid') {
                    $btns[] = $this->orderActionButton('refund_order_modal', 'Order Refund', route('order.refund', $order->uid), 'bi bi-box-arrow-left', $order);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    public function salesTable($query)
    {
        return $this->dataTable($query, [
            'editColumns' => [
                'date' => function ($order) {
                    return date('d M, Y', strtotime($order->date));
                },
            ],
            'addColumns' => [
                'action' => function ($order) {
                    return $this->printButton(route('order.print', $order->uid), 'Print Invoice');
                },
            ],
        ]);
    }

    protected function printButton($url, $title)
    {
        return '<a href="' . $url . '" data-bs-toggle="tooltip" tabindex="0" title="' . $title . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
            <span class="svg-icon svg-icon-3"><i class="fonticon-printer fs-3"></i></span>
        </a>';
    }

    protected function orderActionButton($modal, $title, $url, $icon, $order)
    {
        return '<a href="#" data-modal_id="' . $order->id . '" data-modal_name="' . $order->order_no . '" data-bs-toggle="modal" data-bs-target="#' . $modal . '" tabindex="0" title="' . $title . '" data-base-url="' . $url . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
            <span class="svg-icon svg-icon-3"><i class="' . $icon . '"></i></span>
        </a>';
    }
}
