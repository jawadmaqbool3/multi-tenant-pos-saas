<?php

namespace Modules\Billing\Models;

use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends UIDModel
{
    protected $table = 'billing_customers';

    protected $guarded = ['id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}