<?php

namespace Modules\Billing\Dashboard\Widgets;

class UsageTable extends BillingWidget
{
    public function key(): string
    {
        return 'billing.usage';
    }

    public function title(): string
    {
        return 'Plan Usage';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard subscription usage';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-4';
    }

    public function order(): int
    {
        return 113;
    }

    public function visible($user): bool
    {
        return $this->billing->hasActiveSubscription();
    }

    protected function payload(): ?array
    {
        return $this->billing->usage();
    }
}
