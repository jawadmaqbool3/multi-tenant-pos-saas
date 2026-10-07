<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\AccountLevel3;

class AccountLevel3Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel3());
    }
}
