<?php

namespace Modules\Employee\Repositories;

use App\Repositories\BaseRepository;

use Modules\Employee\Models\EmployeePersonalInformation;

class EmployeePersonalInformationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EmployeePersonalInformation());
    }
}
