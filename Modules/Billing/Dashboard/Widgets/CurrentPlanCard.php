<?php

namespace Modules\Billing\Dashboard\Widgets;

class CurrentPlanCard extends BillingWidget
{
    public function key(): string
    {
        return 'billing.current_plan';
    }

    public function title(): string
    {
        return 'Current Plan';
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
        return 111;
    }

    public function visible($user): bool
    {
        return $this->billing->hasActiveSubscription();
    }

    protected function payload(): ?array
    {
        return $this->billing->currentPlan();
    }
}
