<?php

namespace Modules\Billing\Dashboard\Widgets;

class ActiveCompaniesCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.active_companies';
    }

    public function title(): string
    {
        return 'Active Companies';
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
        return 201;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->activeCompanies(),
            'format' => 'number',
        ];
    }
}
