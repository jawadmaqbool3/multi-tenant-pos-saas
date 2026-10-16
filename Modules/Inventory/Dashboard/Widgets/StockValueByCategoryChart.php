<?php

namespace Modules\Inventory\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Inventory\Services\StockDashboardService;

class StockValueByCategoryChart extends AbstractWidget
{
    protected $stock;

    public function __construct(StockDashboardService $stock)
    {
        $this->stock = $stock;
    }

    public function key(): string
    {
        return 'stock.value_by_category';
    }

    public function title(): string
    {
        return 'Stock Value by Category';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard stock value';
    }

    public function module(): string
    {
        return 'Products';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 125;
    }

    protected function payload(): ?array
    {
        return $this->stock->valueByCategoryChart();
    }
}
