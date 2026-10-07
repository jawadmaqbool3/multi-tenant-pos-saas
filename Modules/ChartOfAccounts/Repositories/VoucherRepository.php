<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\Voucher;

class VoucherRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Voucher());
    }
}
