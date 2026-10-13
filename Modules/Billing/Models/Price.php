<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends UIDModel
{
    protected $table = 'billing_prices';

    protected $guarded = ['id'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
}