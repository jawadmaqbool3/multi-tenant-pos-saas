<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\VoucherType;

class VoucherTypeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VoucherType());
    }
}
