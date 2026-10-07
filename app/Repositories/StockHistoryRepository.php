<?php

namespace App\Repositories;

use App\Models\StockHistory;

class StockHistoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new StockHistory());
    }
}
