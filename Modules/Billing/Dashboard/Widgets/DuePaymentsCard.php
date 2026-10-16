<?php

namespace Modules\Billing\Dashboard\Widgets;

class DuePaymentsCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.due_payments';
    }

    public function title(): string
    {
        return 'Due Payments';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard due payments';
    }

    public function order(): int
    {
        return 210;
    }

    public function payload(): ?array
    {
        $due = $this->platform->duePayments();

        if ($due['count'] === 0) {
            return ['empty' => 'No overdue invoices.'];
        }

        return [
            'value' => $due['count'],
            'format' => 'number',
            'direction' => 'down',
            'inverse' => true,
            'secondary' => $due['total'],
            'secondary_format' => 'currency',
            'secondary_label' => 'outstanding',
        ];
    }
}
