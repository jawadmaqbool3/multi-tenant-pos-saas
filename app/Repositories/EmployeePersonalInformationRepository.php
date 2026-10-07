<?php

namespace App\Repositories;

use App\Models\EmployeePersonalInformation;

class EmployeePersonalInformationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EmployeePersonalInformation());
    }
}
