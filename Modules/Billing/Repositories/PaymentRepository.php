<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Payment;

class PaymentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payment());
    }
}
