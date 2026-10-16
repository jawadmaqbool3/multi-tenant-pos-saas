<?php

namespace Modules\Billing\Dashboard\Widgets;

class NextInvoiceCard extends BillingWidget
{
    public function key(): string
    {
        return 'billing.next_invoice';
    }

    public function title(): string
    {
        return 'Next Invoice';
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
        return 114;
    }

    public function visible($user): bool
    {
        return $this->billing->nextInvoice() !== null;
    }

    protected function payload(): ?array
    {
        return $this->billing->nextInvoice();
    }
}
