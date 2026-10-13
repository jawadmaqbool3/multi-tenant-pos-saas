<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Price;

class PriceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Price());
    }
}
