<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Setting());
    }
}
