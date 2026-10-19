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

    /**
     * Personal information (and salary) row for a single employee.
     *
     * @param  int  $employeeId
     * @return \Modules\Employee\Models\EmployeePersonalInformation|null
     */
    public function forEmployee($employeeId)
    {
        return $this->query()->where('employee_id', $employeeId)->first();
    }
}
