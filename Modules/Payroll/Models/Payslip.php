<?php

namespace Modules\Payroll\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payslip extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(\Modules\Employee\Models\User::class, 'employee_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'id');
    }

    public function allowanceItems()
    {
        return $this->hasMany(PayslipAllowance::class, 'payslip_id', 'id');
    }

    public function deductionItems()
    {
        return $this->hasMany(PayslipDeduction::class, 'payslip_id', 'id');
    }
}