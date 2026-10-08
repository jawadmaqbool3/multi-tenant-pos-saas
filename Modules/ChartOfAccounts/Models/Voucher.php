<?php

namespace Modules\ChartOfAccounts\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UIDModel;

class Voucher extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    public function voucherType()
    {
        return $this->hasOne(VoucherType::class, 'id', 'voucher_type_id');
    }
    public function creator()
    {
        return $this->morphTo('creator', 'creator_type', 'created_by', 'id');
    }


    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        }
        return $query->whereHas('voucherType', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }









    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'voucher_id', 'id');
    }
}
