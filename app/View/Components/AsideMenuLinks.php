<?php

namespace App\View\Components;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\View\Component;
use Modules\Billing\Services\PlanFeatureService;
use Modules\RBAC\Models\Permission;

class AsideMenuLinks extends Component
{
    /**
     * @var array
     */
    public $links;

    /**
     * When true links render as top-level menu items instead of accordions.
     *
     * @var bool
     */
    public $flat = false;

    /**
     * Module names included in the current company's plan (null = no gating).
     *
     * @var array<int, string>|null
     */
    protected $planModules = null;

    /**
     * Map of permission name => module name for the current company.
     *
     * @var array<string, string|null>|null
     */
    protected $permissionModules = null;

    public function __construct()
    {
        $this->links = $this->markActive($this->buildLinks());
    }

    /**
     * Platform (SaaS owner) accounts get a hardcoded menu. Company accounts get
     * the permission-filtered, database-driven menu that they can customize.
     *
     * @return array
     */
    protected function buildLinks()
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        if (method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin()) {
            $this->flat = true;

            return [
                [
                    'title' => 'Pages',
                    'categories' => $this->platformCategories(),
                ],
            ];
        }

        return $this->filterLinks([
            [
                'title' => 'Pages',
                'categories' => $this->tenantCategories($user),
            ],
        ]);
    }

    /**
     * Hardcoded modules available to the super admin.
     *
     * @return array
     */
    protected function platformCategories()
    {
        return [
            [
                'name' => 'SaaS',
                'icon' => 'bi bi-share',
                'links' => [
                    [
                        'title' => 'Companies',
                        'permission' => 'view company',
                        'link' => route('companies.index'),
                        'icon' => 'bi bi-building',
                    ],
                ],
            ],
            [
                'name' => 'Billing',
                'icon' => 'bi bi-bank',
                'links' => [
                    [
                        'title' => 'Plans',
                        'permission' => 'view billing plan',
                        'link' => route('billing.plans.index'),
                        'icon' => 'bi bi-credit-card',
                    ],
                    [
                        'title' => 'Payment Invoices',
                        'permission' => 'view billing invoice',
                        'link' => route('billing.invoices.index'),
                        'icon' => 'bi bi-receipt',
                    ],
                    [
                        'title' => 'Promotions & Coupons',
                        'permission' => 'view coupon',
                        'link' => route('billing.coupons.index'),
                        'icon' => 'bi bi-tag',
                    ],
                ],
            ],
        ];
    }

    /**
     * Database-driven menu assigned to a company (and inherited by its users).
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return array
     */
    protected function tenantCategories($user)
    {
        $setting = $user->setting();
        $categories = $setting ? json_decode($setting->asidebar, true) : [];

        if (!is_array($categories)) {
            return [];
        }

        foreach ($categories as &$category) {
            if (!isset($category['links']) || !is_array($category['links'])) {
                continue;
            }
            foreach ($category['links'] as &$link) {
                if (!empty($link['link'])) {
                    $link['link'] = $this->relativeUrl($link['link']);
                }
            }
            unset($link);
        }
        unset($category);

        return $categories;
    }

    /**
     * Stored menu links can carry an absolute host (e.g. a dev tunnel) which
     * breaks on any other domain. Reduce them to a site-relative path so they
     * always resolve against the current host.
     *
     * @param  string  $url
     * @return string
     */
    protected function relativeUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return $url;
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        return $path . ($query ? '?' . $query : '');
    }

    public function filterLinks($links)
    {
        foreach ($links as $x => $menu) {
            foreach ($menu['categories']  as $y => $category) {
                foreach ($category['links']  as $z => $link) {
                    if (auth()->user()->hasPermission($link['permission']) == false) {
                        unset($links[$x]['categories'][$y]['links'][$z]);
                        continue;
                    }
                    if (!$this->moduleAllowedByPlan($link['permission'])) {
                        unset($links[$x]['categories'][$y]['links'][$z]);
                    }
                }
                if (count($links[$x]['categories'][$y]['links']) == 0) {
                    unset($links[$x]['categories'][$y]);
                }
            }
            if (count($links[$x]['categories']) == 0) {
                unset($links[$x]);
            }
        }
        if (count($links) == 0) {
            $links = [];
        }
        return $links;
    }

    /**
     * Gate feature modules (chart of accounts, inventory, promotions) behind
     * the modules included in the company's plan.
     *
     * @param  string  $permission
     * @return bool
     */
    protected function moduleAllowedByPlan($permission)
    {
        $planModules = $this->planModules();

        if ($planModules === null) {
            return true;
        }

        $module = $this->moduleForPermission($permission);

        if (!$module) {
            return true;
        }

        if (!app(PlanFeatureService::class)->isModuleGated($module)) {
            return true;
        }

        return in_array($module, $planModules, true);
    }

    /**
     * @return array<int, string>|null
     */
    protected function planModules()
    {
        if ($this->planModules !== null) {
            return $this->planModules;
        }

        $user = auth()->user();

        if (!$user || !$user->company_id) {
            return null;
        }

        $company = Company::where('id', $user->company_id)->first();

        if (!$company) {
            return null;
        }

        return $this->planModules = app(PlanFeatureService::class)->allowedModules($company);
    }

    /**
     * @param  string  $permission
     * @return string|null
     */
    protected function moduleForPermission($permission)
    {
        if ($this->permissionModules === null) {
            $this->permissionModules = [];

            $permissions = Permission::with('module')
                ->whereNull('company_id')
                ->get();

            foreach ($permissions as $row) {
                $this->permissionModules[$row->name] = $row->module->name ?? null;
            }
        }

        return $this->permissionModules[$permission] ?? null;
    }

    /**
     * Flag the link (and its parent category) that matches the current page,
     * so the sidebar can highlight it.
     *
     * @param  array  $links
     * @return array
     */
    protected function markActive($links)
    {
        $paths = $this->linkPaths($links);
        $requestPath = trim(request()->path(), '/');
        $exactMatch = $requestPath !== '' && isset($paths[$requestPath]);

        foreach ($links as $x => $menu) {
            foreach ($menu['categories'] as $y => $category) {
                $categoryActive = false;
                foreach ($category['links'] as $z => $link) {
                    $active = $this->isActiveLink($link['link'] ?? '', $requestPath, $exactMatch);
                    $links[$x]['categories'][$y]['links'][$z]['active'] = $active;
                    if ($active) {
                        $categoryActive = true;
                    }
                }
                $links[$x]['categories'][$y]['active'] = $categoryActive;
            }
        }

        return $links;
    }

    /**
     * The set of site-relative paths referenced by the menu, keyed by path.
     *
     * @param  array  $links
     * @return array<string, bool>
     */
    protected function linkPaths($links)
    {
        $paths = [];

        foreach ($links as $menu) {
            foreach (($menu['categories'] ?? []) as $category) {
                foreach (($category['links'] ?? []) as $link) {
                    $path = $this->linkPath($link['link'] ?? '');
                    if ($path !== '') {
                        $paths[$path] = true;
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * A menu link is active when the current request path matches it exactly.
     * Sub-pages (e.g. `/users/1/edit`) also light up their parent link, but only
     * when no other menu link matches the request exactly — so `/attendance`
     * does not stay lit while the more specific `/attendance/me` is open.
     *
     * @param  string  $url
     * @param  string  $requestPath
     * @param  bool  $exactMatch
     * @return bool
     */
    protected function isActiveLink($url, $requestPath = '', $exactMatch = false)
    {
        $path = $this->linkPath($url);

        if ($path === '') {
            return false;
        }

        if ($path === $requestPath) {
            return true;
        }

        if ($exactMatch) {
            return false;
        }

        return request()->is($path) || request()->is($path . '/*');
    }

    /**
     * Normalise a menu URL to a site-relative path with no surrounding slashes.
     *
     * @param  string  $url
     * @return string
     */
    protected function linkPath($url)
    {
        return trim((string) parse_url($url, PHP_URL_PATH), '/');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.aside-menu-links');
    }
}
