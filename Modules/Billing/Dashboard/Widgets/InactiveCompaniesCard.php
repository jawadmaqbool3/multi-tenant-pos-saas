<?php

namespace Modules\Billing\Dashboard\Widgets;

class InactiveCompaniesCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.inactive_companies';
    }

    public function title(): string
    {
        return 'Inactive Companies';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'dashboard active companies';
    }

    public function order(): int
    {
        return 202;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->inactiveCompanies(),
            'format' => 'number',
        ];
    }
}
