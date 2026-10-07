<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\StockHistory;

class StockHistoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new StockHistory());
    }
}
