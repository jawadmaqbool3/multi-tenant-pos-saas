<?php

namespace App\Services;

use App\Repositories\StockHistoryRepository;

class StockHistoryService
{
    protected $stockHistory;

    public function __construct(StockHistoryRepository $stockHistory)
    {
        $this->stockHistory = $stockHistory;
    }
}
