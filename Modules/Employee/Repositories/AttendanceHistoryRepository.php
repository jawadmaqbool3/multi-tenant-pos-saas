<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;

use Modules\Employee\Models\AttendanceHistory;

class AttendanceHistoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AttendanceHistory());
    }
}
