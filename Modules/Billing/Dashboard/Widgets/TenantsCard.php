<?php

namespace Modules\Billing\Dashboard\Widgets;

class TenantsCard extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.tenants';
    }

    public function title(): string
    {
        return 'Tenant Users';
    }

    public function type(): string
    {
        return 'card';
    }

    public function permission(): string
    {
        return 'view tenant';
    }

    public function order(): int
    {
        return 204;
    }

    public function payload(): ?array
    {
        return [
            'value' => $this->platform->tenantUsers(),
            'format' => 'number',
        ];
    }
}
