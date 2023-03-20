<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountLevel4 extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function level3()
    {
        return $this->hasOne(AccountLevel3::class, 'id', 'parent_id');
    }

    public function calculatebalance()
    {
        return $this->balance;
    }
}
