<?php

namespace Modules\Billing\Dashboard\Widgets;

class InvoicesTable extends BillingWidget
{
    public function key(): string
    {
        return 'billing.invoices';
    }

    public function title(): string
    {
        return 'Recent Invoices';
    }

    public function type(): string
    {
        return 'table';
    }

    public function permission(): string
    {
        return 'dashboard my invoices';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function order(): int
    {
        return 115;
    }

    public function visible($user): bool
    {
        $company = $this->billing->company();

        if (!$company) {
            return false;
        }

        return $this->billing->invoicesFor($company->id)->isNotEmpty();
    }

    protected function payload(): ?array
    {
        return $this->billing->invoices();
    }
}
