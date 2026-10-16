<?php

namespace Modules\Billing\Dashboard\Widgets;

class CompaniesCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.companies';
    }

    public function title(): string
    {
        return 'Total Companies';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'view company';
    }

    public function order(): int
    {
        return 200;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->totalCompanies(),
            'format' => 'number',
        ];
    }
}
