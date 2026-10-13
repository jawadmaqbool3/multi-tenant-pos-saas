<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Customer;

class CustomerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Customer());
    }
}
