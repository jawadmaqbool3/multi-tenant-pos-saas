<?php

namespace App\Dashboard;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use Modules\Billing\Services\PlanFeatureService;

class DashboardService
{
    protected DashboardRegistry $registry;
    protected PlanFeatureService $planFeatures;
    protected CompanyRepository $companies;

    /**
     * @var array<int, Company|null>
     */
    protected array $companyCache = [];

    public function __construct(
        DashboardRegistry $registry,
        PlanFeatureService $planFeatures,
        CompanyRepository $companies
    ) {
        $this->registry = $registry;
        $this->planFeatures = $planFeatures;
        $this->companies = $companies;
    }

    /**
     * Widgets the given user may see, in display order.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return array<int, Widget>
     */
    public function widgetsFor($user): array
    {
        $isPlatform = method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin();

        $widgets = array_filter($this->registry->all(), function (Widget $widget) use ($user, $isPlatform) {
            if ($widget->scope() === 'platform') {
                if (!$isPlatform) {
                    return false;
                }
            } elseif ($isPlatform) {
                return false;
            }

            if (!$user->hasPermission($widget->permission())) {
                return false;
            }

            if (!$widget->visible($user)) {
                return false;
            }

            return $this->allowedByPlan($widget, $isPlatform);
        });

        usort($widgets, function (Widget $a, Widget $b) {
            return $a->order() <=> $b->order();
        });

        return array_values($widgets);
    }

    /**
     * Batched payload keyed by widget key. Self-hydrating widgets are skipped.
     *
     * @param  array<int, Widget>  $widgets
     * @return array<string, array>
     */
    public function payload(array $widgets): array
    {
        $out = [];

        foreach ($widgets as $widget) {
            $data = $widget->data();

            if ($data !== null) {
                $out[$widget->key()] = $data;
            }
        }

        return $out;
    }

    /**
     * Payload for a single widget key, or null when the key is unknown or the
     * current user is not allowed to see it.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function payloadFor($user, string $key): ?array
    {
        foreach ($this->widgetsFor($user) as $widget) {
            if ($widget->key() === $key) {
                return $widget->data();
            }
        }

        return null;
    }

    protected function allowedByPlan(Widget $widget, bool $isPlatform): bool
    {
        if ($isPlatform) {
            return true;
        }

        $module = $widget->module();

        if ($module === '') {
            return true;
        }

        if (!$this->planFeatures->isModuleGated($module)) {
            return true;
        }

        return $this->planFeatures->companyAllowsModule($this->companyFor(), $module);
    }

    protected function companyFor(): ?Company
    {
        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return null;
        }

        if (!array_key_exists($user->company_id, $this->companyCache)) {
            $this->companyCache[$user->company_id] = $this->companies->query()
                ->where('id', $user->company_id)
                ->first();
        }

        return $this->companyCache[$user->company_id];
    }
}
