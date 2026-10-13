<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Signup;

class SignupRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Signup());
    }
}
