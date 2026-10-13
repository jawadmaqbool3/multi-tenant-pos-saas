<?php

namespace Modules\Billing\Models;

use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends UIDModel
{
    protected $table = 'billing_invoices';

    protected $guarded = ['id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }
}