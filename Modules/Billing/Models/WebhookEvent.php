<?php

namespace Modules\Billing\Models;

use App\Models\UIDModel;

class WebhookEvent extends UIDModel
{
    protected $table = 'billing_webhook_events';

    protected $guarded = ['id'];

    public const STATUS_RECEIVED = 'received';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';
}