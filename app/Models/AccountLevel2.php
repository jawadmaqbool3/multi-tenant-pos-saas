<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountLevel2 extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
    public function level1()
    {
        return $this->hasOne(AccountLevel1::class,  'id', 'parent_id');
    }
    public function level3()
    {
        return $this->hasMany(AccountLevel3::class, 'parent_id', 'id');
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function level3Active()
    {
        return $this->hasMany(AccountLevel3::class, 'parent_id', 'id')->active();
    }

    public function calculatebalance()
    {
        $level2 = $this;
        return AccountLevel4::whereHas('level3.level2', function ($query) use ($level2) {
            $query->where('id', $level2->id);
        })->sum('balance');
    }
}
