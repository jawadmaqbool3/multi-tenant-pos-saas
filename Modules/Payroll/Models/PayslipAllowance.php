<?php

namespace Modules\Payroll\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayslipAllowance extends UIDModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class, 'payslip_id', 'id');
    }

    public function allowance()
    {
        return $this->belongsTo(Allowance::class, 'allowance_id', 'id');
    }
}