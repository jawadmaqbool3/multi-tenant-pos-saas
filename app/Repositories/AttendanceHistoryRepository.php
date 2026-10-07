<?php

namespace App\Repositories;

use App\Models\AttendanceHistory;

class AttendanceHistoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AttendanceHistory());
    }
}
