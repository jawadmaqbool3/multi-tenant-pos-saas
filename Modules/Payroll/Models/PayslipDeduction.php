<?php

namespace Modules\Payroll\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayslipDeduction extends UIDModel
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

    public function deduction()
    {
        return $this->belongsTo(Deduction::class, 'deduction_id', 'id');
    }
}