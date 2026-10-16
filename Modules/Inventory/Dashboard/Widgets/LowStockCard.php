<?php

namespace Modules\Inventory\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Inventory\Services\StockDashboardService;

class LowStockCard extends AbstractWidget
{
    protected $stock;

    public function __construct(StockDashboardService $stock)
    {
        $this->stock = $stock;
    }

    public function key(): string
    {
        return 'stock.low';
    }

    public function title(): string
    {
        return 'Low Stock';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard stock low';
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
        return 123;
    }

    protected function payload(): ?array
    {
        return $this->stock->lowCard();
    }
}
