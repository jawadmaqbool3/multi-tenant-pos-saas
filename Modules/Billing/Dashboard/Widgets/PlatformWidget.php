<?php

namespace Modules\Billing\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Billing\Services\PlatformDashboardService;

abstract class PlatformWidget extends AbstractWidget
{
    protected PlatformDashboardService $platform;

    public function __construct(PlatformDashboardService $platform)
    {
        $this->platform = $platform;
    }

    public function module(): string
    {
        return '';
    }

    public function scope(): string
    {
        return 'platform';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-3';
    }

    public function statusBadge(string $status): array
    {
        $map = [
            'active' => 'success',
            'inactive' => 'warning',
            'past_due' => 'danger',
            'unpaid' => 'danger',
            'canceled' => 'secondary',
        ];

        return ['text' => ucfirst(str_replace('_', ' ', $status)), 'badge' => $map[$status] ?? 'secondary'];
    }
}
