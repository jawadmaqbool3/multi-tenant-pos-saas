<?php

namespace Modules\Payroll\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allowance extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function payslipItems()
    {
        return $this->hasMany(PayslipAllowance::class, 'allowance_id', 'id');
    }
}