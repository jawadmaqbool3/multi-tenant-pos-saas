<?php

namespace Modules\Billing\Dashboard\Widgets;

class RevenueChart extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.revenue_chart';
    }

    public function title(): string
    {
        return 'Revenue & Signups';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function permission(): string
    {
        return 'view billing report';
    }

    public function order(): int
    {
        return 209;
    }

    public function payload(): ?array
    {
        return $this->platform->revenueChart();
    }
}
