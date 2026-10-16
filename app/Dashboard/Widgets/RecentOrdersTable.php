<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class RecentOrdersTable extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'orders.recent';
    }

    public function title(): string
    {
        return 'Recent Orders';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard recent orders';
    }

    public function size(): string
    {
        return 'col-xl-4';
    }

    public function order(): int
    {
        return 80;
    }

    protected function payload(): ?array
    {
        return $this->home->recentOrders();
    }
}
