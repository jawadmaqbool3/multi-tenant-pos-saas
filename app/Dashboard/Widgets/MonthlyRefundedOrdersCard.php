<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class MonthlyRefundedOrdersCard extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'orders.refunded_month';
    }

    public function title(): string
    {
        return 'Refunded Orders this month';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard monthly refunded orders';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 60;
    }

    protected function payload(): ?array
    {
        return $this->home->monthlyRefundedOrders();
    }
}
