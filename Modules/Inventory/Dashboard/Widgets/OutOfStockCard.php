<?php

namespace Modules\Inventory\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Inventory\Services\StockDashboardService;

class OutOfStockCard extends AbstractWidget
{
    protected $stock;

    public function __construct(StockDashboardService $stock)
    {
        $this->stock = $stock;
    }

    public function key(): string
    {
        return 'stock.out';
    }

    public function title(): string
    {
        return 'Out of Stock';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard stock out';
    }

    public function module(): string
    {
        return 'Products';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 124;
    }

    protected function payload(): ?array
    {
        return $this->stock->outCard();
    }
}
