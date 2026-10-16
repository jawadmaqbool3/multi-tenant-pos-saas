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

    public function failedCount(): int
    {
        return $this->query()->where('status', 'failed')->count();
    }

    public function failedAmount(): float
    {
        return (float) $this->query()->where('status', 'failed')->sum('amount');
    }
}
