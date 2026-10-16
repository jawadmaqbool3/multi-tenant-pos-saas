<?php

namespace Modules\Billing\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use Modules\Billing\Services\BillingDashboardService;

/**
 * Base for the tenant subscription widgets. Subscription widgets are never
 * plan-gated (module is empty) so a company with no plan still sees the
 * "choose a plan" empty state.
 */
abstract class BillingWidget extends AbstractWidget
{
    protected BillingDashboardService $billing;

    public function __construct(BillingDashboardService $billing)
    {
        $this->billing = $billing;
    }

    public function module(): string
    {
        return '';
    }

    public function scope(): string
    {
        return 'company';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-4';
    }
}
