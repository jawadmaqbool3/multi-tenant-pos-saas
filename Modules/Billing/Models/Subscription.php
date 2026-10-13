<?php

namespace Modules\Billing\Models;

use App\Models\Company;
use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends UIDModel
{
    protected $table = 'billing_subscriptions';

    protected $guarded = ['id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'billing_customer_id', 'id');
    }
}