<?php

namespace Modules\Supplier\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Supplier\Services\SupplierDashboardService;

class PendingPurchaseOrdersCard extends AbstractWidget
{
    protected $supplier;

    public function __construct(SupplierDashboardService $supplier)
    {
        $this->supplier = $supplier;
    }

    public function key(): string
    {
        return 'supplier.pending_po';
    }

    public function title(): string
    {
        return 'Open Purchase Orders';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard supplier pending orders';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 129;
    }

    protected function payload(): ?array
    {
        return $this->supplier->pendingOrdersCard();
    }
}
