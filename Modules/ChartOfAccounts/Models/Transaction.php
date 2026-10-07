<?php

namespace Modules\ChartOfAccounts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function scopeFilter($query)
    {
            return $query->whereHas('voucher.voucherType', function ($query) {
                $query->where('company_id', auth()->user()->company_id);
            });
    }

    public function voucher()
    {
        return $this->hasMany(Voucher::class, 'id', 'voucher_id');
    }

    public function account()
    {
        return $this->hasOne(AccountLevel4::class, 'id', 'account_id');
    }
}
