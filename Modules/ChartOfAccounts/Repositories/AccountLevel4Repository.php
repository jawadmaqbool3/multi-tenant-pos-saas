<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\AccountLevel4;

class AccountLevel4Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel4());
    }
}
