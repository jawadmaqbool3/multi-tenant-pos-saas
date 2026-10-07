<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    public function clearOptimize()
    {
        return Artisan::call('optimize:clear');
    }
}
