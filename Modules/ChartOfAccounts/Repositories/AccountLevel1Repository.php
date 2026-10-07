<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\AccountLevel1;

class AccountLevel1Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel1());
    }
}
