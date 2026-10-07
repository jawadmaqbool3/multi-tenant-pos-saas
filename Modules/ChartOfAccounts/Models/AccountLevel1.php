<?php

namespace Modules\ChartOfAccounts\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountLevel1 extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
    public function level2()
    {
        return $this->hasMany(AccountLevel2::class, 'parent_id', 'id');
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function level2Active()
    {
        return $this->hasMany(AccountLevel2::class, 'parent_id', 'id')->active();
    }

    public function calculatebalance()
    {
        $level1 = $this;
        return AccountLevel4::whereHas('level3.level2.level1', function ($query) use ($level1) {
            $query->where('id', $level1->id);
        })->sum('balance');
    }
}
