<?php

namespace Modules\Billing\Dashboard\Widgets;

class StripeBalanceCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.stripe_balance';
    }

    public function title(): string
    {
        return 'Stripe Balance';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard stripe balance';
    }

    public function order(): int
    {
        return 206;
    }

    public function payload(): ?array
    {
        $balance = $this->platform->stripeBalance();

        if (!($balance['configured'] ?? false)) {
            return ['empty' => 'Stripe is not configured.'];
        }

        if ($balance['error'] ?? false) {
            return ['empty' => 'Unable to fetch Stripe balance right now.'];
        }

        return [
            'value' => $balance['available'],
            'format' => 'currency',
            'currency' => 'USD',
            'secondary' => $balance['pending'],
            'secondary_format' => 'currency',
            'secondary_label' => 'pending',
        ];
    }
}
