<?php

namespace Modules\Billing\Dashboard\Widgets;

class PastDueSubscriptionsCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.past_due_subscriptions';
    }

    public function title(): string
    {
        return 'Past Due Subscriptions';
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
        return 211;
    }

    public function payload(): ?array
    {
        $count = $this->platform->pastDueSubscriptions();

        if ($count === 0) {
            return ['empty' => 'No past-due subscriptions.'];
        }

        return [
            'value' => $count,
            'format' => 'number',
            'direction' => 'down',
            'inverse' => true,
        ];
    }
}
