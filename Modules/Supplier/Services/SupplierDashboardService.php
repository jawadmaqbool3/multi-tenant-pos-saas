<?php

namespace Modules\Supplier\Services;

use Modules\Supplier\Repositories\PurchaseOrderRepository;
use Modules\Supplier\Repositories\SupplierRepository;

class SupplierDashboardService
{
    protected $purchaseOrders;
    protected $suppliers;

    public function __construct(PurchaseOrderRepository $purchaseOrders, SupplierRepository $suppliers)
    {
        $this->purchaseOrders = $purchaseOrders;
        $this->suppliers = $suppliers;
    }

    /**
     * Open (not yet refunded) purchase orders.
     */
    public function pendingOrdersCard(): array
    {
        return [
            'value' => $this->purchaseOrders->pendingCount($this->companyId()),
            'format' => 'number',
            'secondary_label' => 'Open purchase orders',
            'link' => ['label' => 'View orders', 'url' => route('purchase-orders.index')],
        ];
    }

    /**
     * Total amount payable to suppliers.
     */
    public function payableCard(): array
    {
        return [
            'value' => round($this->suppliers->payableTotal($this->companyId()), 2),
            'format' => 'currency',
            'secondary_label' => 'Outstanding payable',
            'link' => ['label' => 'View suppliers', 'url' => route('suppliers.index')],
        ];
    }

    /**
     * @return int|null
     */
    protected function companyId()
    {
        return auth()->user() ? auth()->user()->company_id : null;
    }
}
