<?php

namespace App\Repositories;

use App\Models\DashboardSetting;

class DashboardSettingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new DashboardSetting());
    }
}
