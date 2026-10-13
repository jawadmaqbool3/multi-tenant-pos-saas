<?php

namespace Modules\Billing\Models;

use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends UIDModel
{
    protected $table = 'billing_payments';

    protected $guarded = ['id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}