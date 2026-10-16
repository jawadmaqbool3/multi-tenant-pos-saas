<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class MonthlyOrdersCard extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'orders.month';
    }

    public function title(): string
    {
        return 'Orders this month';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard monthly orders';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 40;
    }

    protected function payload(): ?array
    {
        return $this->home->monthlyOrders();
    }
}
