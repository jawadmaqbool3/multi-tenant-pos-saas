<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Subscription;

class SubscriptionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Subscription());
    }
}
