<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\AccountSetting;

class AccountSettingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountSetting());
    }
}
