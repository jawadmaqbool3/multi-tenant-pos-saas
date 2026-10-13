<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Redemption;

class RedemptionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Redemption());
    }
}
