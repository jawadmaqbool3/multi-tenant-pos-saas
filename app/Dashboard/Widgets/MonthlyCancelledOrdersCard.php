<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class MonthlyCancelledOrdersCard extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'orders.cancelled_month';
    }

    public function title(): string
    {
        return 'Cancelled Orders this month';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard monthly cancelled orders';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 50;
    }

    protected function payload(): ?array
    {
        return $this->home->monthlyCancelledOrders();
    }
}
