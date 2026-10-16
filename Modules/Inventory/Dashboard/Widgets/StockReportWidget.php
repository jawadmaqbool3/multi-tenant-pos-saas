<?php

namespace Modules\Inventory\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;

class StockReportWidget extends AbstractWidget
{
    public function key(): string
    {
        return 'stock.report';
    }

    public function title(): string
    {
        return 'Stock Report';
    }

    public function type(): string
    {
        return 'custom';
    }

    public function permission(): string
    {
        return 'dashboard stock report';
    }

    public function module(): string
    {
        return 'Inventory';
    }

    public function size(): string
    {
        return 'col-xl-8';
    }

    public function order(): int
    {
        return 90;
    }

    public function view(): string
    {
        return 'inventory::dashboard.widgets.stock_report';
    }

    /**
     * Self-hydrating: the stock report keeps its own DataTables endpoint.
     */
    protected function payload(): ?array
    {
        return null;
    }
}
