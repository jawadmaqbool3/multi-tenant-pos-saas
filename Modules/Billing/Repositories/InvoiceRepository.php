<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Invoice());
    }
}
