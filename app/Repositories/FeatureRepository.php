<?php

namespace App\Repositories;

use App\Models\Feature;

class FeatureRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Feature());
    }
}
