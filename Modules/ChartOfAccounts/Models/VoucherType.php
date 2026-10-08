<?php

namespace Modules\ChartOfAccounts\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoucherType extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }


    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'voucher_type_id', 'id');
    }
}
