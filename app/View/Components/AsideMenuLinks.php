<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;

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

        return is_array($categories) ? $categories : [];
    }

    public function filterLinks($links)
    {
        foreach ($links as $x => $menu) {
            foreach ($menu['categories']  as $y => $category) {
                foreach ($category['links']  as $z => $link) {
                    if (auth()->user()->hasPermission($link['permission']) == false) {
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
     * Flag the link (and its parent category) that matches the current page,
     * so the sidebar can highlight it.
     *
     * @param  array  $links
     * @return array
     */
    protected function markActive($links)
    {
        foreach ($links as $x => $menu) {
            foreach ($menu['categories'] as $y => $category) {
                $categoryActive = false;
                foreach ($category['links'] as $z => $link) {
                    $active = $this->isActiveLink($link['link'] ?? '');
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
     * Match a menu URL against the current request path, ignoring the host so
     * that stored (absolute) links and the live request always line up.
     *
     * @param  string  $url
     * @return bool
     */
    protected function isActiveLink($url)
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return false;
        }

        return request()->is($path) || request()->is($path . '/*');
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
