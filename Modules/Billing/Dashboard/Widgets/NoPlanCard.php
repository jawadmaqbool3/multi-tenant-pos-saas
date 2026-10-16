<?php

namespace Modules\Billing\Dashboard\Widgets;

class NoPlanCard extends BillingWidget
{
    public function key(): string
    {
        return 'billing.no_plan';
    }

    public function title(): string
    {
        return 'Subscription';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard current plan';
    }

    public function order(): int
    {
        return 110;
    }

    public function visible($user): bool
    {
        return !$this->billing->hasActiveSubscription();
    }

    protected function payload(): ?array
    {
        return [
            'empty' => 'No active plan',
            'link' => ['label' => 'Choose a plan', 'url' => route('billing.pricing')],
        ];
    }
}
