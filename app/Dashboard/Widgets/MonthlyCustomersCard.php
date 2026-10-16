<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class MonthlyCustomersCard extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'customers.month';
    }

    public function title(): string
    {
        return 'New Customers This Month';
    }

    public function type(): string
    {
        return 'card';
    }

    public function renderer(): string
    {
        return 'card_customers';
    }

    public function permission(): string
    {
        return 'dashboard monthly customers';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-6';
    }

    public function order(): int
    {
        return 70;
    }

    protected function payload(): ?array
    {
        return $this->home->monthlyCustomers();
    }
}
