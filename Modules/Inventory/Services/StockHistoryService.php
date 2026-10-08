<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Repositories\StockHistoryRepository;

class StockHistoryService
{
    protected $stockHistories;

    public function __construct(StockHistoryRepository $stockHistories)
    {
        $this->stockHistories = $stockHistories;
    }

    public function create($data)
    {
        return $this->stockHistories->create($data);
    }

    public function groupedProductCount()
    {
        return $this->stockHistories->query()->groupBy('product_id')->count();
    }

    public function getByPurchaseOrder($purchaseOrderId)
    {
        return $this->stockHistories->query()->where('purchase_order_id', $purchaseOrderId)->get();
    }

    public function distinctProductCountForPurchaseOrders($purchaseOrderIds)
    {
        return $this->stockHistories->query()->whereIn('purchase_order_id', $purchaseOrderIds)
            ->distinct()->count('product_id');
    }
}
