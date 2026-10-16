<?php

namespace Modules\Billing\Dashboard\Widgets;

class ArpaCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.arpa';
    }

    public function title(): string
    {
        return 'ARPA';
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
        return 208;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->arpa(),
            'format' => 'currency',
            'currency' => 'USD',
            'secondary' => $this->platform->activeSubscriptionCount(),
            'secondary_format' => 'number',
            'secondary_label' => 'active subs',
        ];
    }
}
