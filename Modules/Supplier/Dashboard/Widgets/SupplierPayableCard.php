<?php

namespace Modules\Supplier\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Supplier\Services\SupplierDashboardService;

class SupplierPayableCard extends AbstractWidget
{
    protected $supplier;

    public function __construct(SupplierDashboardService $supplier)
    {
        $this->supplier = $supplier;
    }

    public function key(): string
    {
        return 'supplier.payable';
    }

    public function title(): string
    {
        return 'Payable to Suppliers';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard supplier payable';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function order(): int
    {
        return 130;
    }

    protected function payload(): ?array
    {
        return $this->supplier->payableCard();
    }
}
