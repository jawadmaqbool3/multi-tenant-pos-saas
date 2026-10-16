<?php

namespace Modules\Billing\Dashboard\Widgets;

class FailedPaymentsCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.failed_payments';
    }

    public function title(): string
    {
        return 'Failed Payments';
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
        return 212;
    }

    public function payload(): ?array
    {
        $failed = $this->platform->failedPayments();

        if ($failed['count'] === 0) {
            return ['empty' => 'No failed payments.'];
        }

        return [
            'value' => $failed['count'],
            'format' => 'number',
            'direction' => 'down',
            'inverse' => true,
            'secondary' => $failed['total'],
            'secondary_format' => 'currency',
            'secondary_label' => 'amount',
        ];
    }
}
