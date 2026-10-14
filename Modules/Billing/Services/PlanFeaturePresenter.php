<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Plan;

class PlanFeaturePresenter
{
    /**
     * Presentation metadata for each plan-gated feature lever.
     *
     * @var array<string, array{label: string, icon: string}>
     */
    const GROUPS = [
        'chart-of-accounts' => ['label' => 'Chart of accounts', 'icon' => 'landmark'],
        'inventory' => ['label' => 'Inventory management', 'icon' => 'package'],
        'promotions' => ['label' => 'Promotions', 'icon' => 'tag'],
    ];

    /**
     * Friendly labels for permission modules that may appear outside the
     * feature levers (future-proofing the checklist).
     *
     * @var array<string, string>
     */
    const MODULE_LABELS = [
        'Sales Orders' => 'Sales orders',
        'Purchase Orders' => 'Purchase orders',
        'Customers' => 'Customers',
        'Suppliers' => 'Suppliers',
        'Dashboard' => 'Dashboard & reports',
        'Settings' => 'Settings',
        'Users' => 'User accounts',
        'Managers' => 'Managers',
        'Employees' => 'Employees',
        'Role' => 'Roles & permissions',
        'Permissions' => 'Permissions',
    ];

    /**
     * Build the ordered feature checklist shown on a plan card.
     *
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return array<int, array{label: string, icon: string, included: bool}>
     */
    public function present(Plan $plan): array
    {
        $modules = $plan->features()
            ->with('module')
            ->get()
            ->pluck('module.name')
            ->filter()
            ->unique()
            ->values();

        $limits = $plan->limits->pluck('limit_value', 'resource_key');

        $lines = [
            [
                'label' => $this->limitLabel($limits->get('employees'), 'employees'),
                'icon' => 'users',
                'included' => true,
            ],
            [
                'label' => $this->limitLabel($limits->get('roles'), 'roles'),
                'icon' => 'shield',
                'included' => true,
            ],
        ];

        $grouped = [];

        foreach (PlanFeatureService::FEATURES as $feature => $featureModules) {
            $meta = self::GROUPS[$feature] ?? null;

            if (!$meta) {
                continue;
            }

            $grouped = array_merge($grouped, $featureModules);

            $lines[] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'included' => $this->covers($featureModules, $modules),
            ];
        }

        foreach ($modules as $module) {
            if (in_array($module, $grouped, true)) {
                continue;
            }

            $lines[] = [
                'label' => self::MODULE_LABELS[$module] ?? $module,
                'icon' => 'check-circle',
                'included' => true,
            ];
        }

        return $lines;
    }

    /**
     * @param  array<int, string>  $required
     * @param  \Illuminate\Support\Collection  $modules
     * @return bool
     */
    protected function covers(array $required, $modules): bool
    {
        foreach ($required as $module) {
            if ($modules->contains($module)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  mixed  $value
     * @param  string  $noun
     * @return string
     */
    protected function limitLabel($value, string $noun): string
    {
        if ($value === null || (int) $value < 0) {
            return 'Unlimited ' . $noun;
        }

        return 'Up to ' . (int) $value . ' ' . $noun;
    }
}
