<?php

namespace Modules\Payroll\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employee\Models\User;

class Payroll extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'period' => 'date',
        'status' => 'string',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_base' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'payroll_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}