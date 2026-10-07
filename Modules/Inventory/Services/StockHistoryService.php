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
}
