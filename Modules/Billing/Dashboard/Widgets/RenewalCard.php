<?php

namespace Modules\Billing\Dashboard\Widgets;

class RenewalCard extends BillingWidget
{
    public function key(): string
    {
        return 'billing.renewal';
    }

    public function title(): string
    {
        return 'Subscription Renewal';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard subscription renewal';
    }

    public function order(): int
    {
        return 112;
    }

    public function visible($user): bool
    {
        return $this->billing->hasActiveSubscription();
    }

    protected function payload(): ?array
    {
        return $this->billing->renewal();
    }
}
