<?php

namespace Modules\Billing\Dashboard\Widgets;

class MrrCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.mrr';
    }

    public function title(): string
    {
        return 'Monthly Recurring Revenue';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'view billing report';
    }

    public function order(): int
    {
        return 207;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->mrr(),
            'format' => 'currency',
            'currency' => 'USD',
        ];
    }
}
