<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\AccountLevel2;

class AccountLevel2Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel2());
    }
}
