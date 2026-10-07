<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Company());
    }
}
