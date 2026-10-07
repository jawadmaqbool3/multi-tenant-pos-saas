<?php

namespace Modules\Order\Services;

use Modules\Order\Repositories\OrderRepository;

class SaleService
{
    protected $order;

    public function __construct(OrderRepository $order)
    {
        $this->order = $order;
    }

    public function salesTable($request)
    {
        $sales = $this->order->query();
        if ($request->from) {
            $sales = $sales->filter()->whereDate('date', '>=', $request->from);
        }
        if ($request->to) {
            $sales = $sales->filter()->whereDate('date', '<=', $request->to);
        }
        return $sales->salesTable();
    }

    public function getOrdersForPrint($from, $to)
    {
        return $this->order->query()->filter()->whereDate('date', '>=', $from)->where('date', '<=', $to)->get();
    }
}
