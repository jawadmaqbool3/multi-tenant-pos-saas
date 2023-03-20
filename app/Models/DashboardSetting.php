<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
