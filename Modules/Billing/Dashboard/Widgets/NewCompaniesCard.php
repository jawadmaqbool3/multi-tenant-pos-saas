<?php

namespace Modules\Billing\Dashboard\Widgets;

class NewCompaniesCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.new_companies';
    }

    public function title(): string
    {
        return 'New Companies (Month)';
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
        return 203;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->newCompaniesThisMonth(),
            'format' => 'number',
        ];
    }
}
