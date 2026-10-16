<?php

namespace App\Services;

use App\Repositories\SettingRepository;

class AsideSettingService
{
    public $menuLinks;
    protected $settings;

    public function __construct(SettingRepository $settings)
    {
        $this->settings = $settings;
                $this->menuLinks = [

            [
                'name' => 'SaaS',
                'icon' => 'bi bi-share',
                'links' => [
                    [
                        'title' => 'Companies',
                        'permission' => 'view company',
                        'link' => route('companies.index'),
                    ],
                ],
            ],
            [
                'name' => 'Bank',
                'icon' => 'bi bi-bank',
                'links' => [
                    [
                        'title' => 'Accounts',
                        'permission' => 'view account',
                        'link' => route('accounts.index'),
                    ]
                ],
            ],
            [
                'name' => 'Inventory',
                'icon' => 'bi bi-shop',
                'links' => [
                    [
                        'title' => 'Categories',
                        'permission' => 'view category',
                        'link' => route('categories.index'),
                    ],
                    [
                        'title' => 'Products',
                        'permission' => 'view product',
                        'link' => route('products.index'),
                    ],
                ],
            ],
            [
                'name' => 'Orders',
                'icon' => 'bi bi-cart',
                'links' => [
                    [
                        'title' => 'Sales Orders',
                        'permission' => 'view order',
                        'link' => route('orders.index'),
                    ],
                    [
                        'title' => 'Purchase Orders',
                        'permission' => 'view purchase orders',
                        'link' => route('purchase-orders.index'),
                    ],
                ],
            ],
            [
                'name' => 'Promotions',
                'icon' => 'bi bi-award-fill',
                'links' => [
                    [
                        'title' => 'Promotions',
                        'permission' => 'view promotion',
                        'link' => route('promotions.index'),
                    ],
                ],
            ],
            [
                'name' => 'Payroll',
                'icon' => 'bi bi-cash-stack',
                'links' => [
                    [
                        'title' => 'Payroll Runs',
                        'permission' => 'view payroll',
                        'link' => route('payrolls.index'),
                    ],
                    [
                        'title' => 'Allowances',
                        'permission' => 'view allowance',
                        'link' => route('allowances.index'),
                    ],
                    [
                        'title' => 'Deductions',
                        'permission' => 'view deduction',
                        'link' => route('deductions.index'),
                    ],
                ],
            ],
            [
                'name' => 'Reports',
                'icon' => 'bi bi-file-earmark-bar-graph',
                'links' => [
                    [
                        'title' => 'Stocks',
                        'permission' => 'view stocks',
                        'link' => route('stocks.index'),
                    ],
                    [
                        'title' => 'Sales',
                        'permission' => 'view sales',
                        'link' => route('sales.index'),
                    ],
                    [
                        'title' => 'Employee Attendance',
                        'permission' => 'view employee attendance report',
                        'link' => route('attendance.index'),
                    ],
                    [
                        'title' => 'My Attendance',
                        'permission' => 'view own attendance',
                        'link' => route('attendance.me'),
                    ],
                    [
                        'title' => 'My Payslips',
                        'permission' => 'view own payroll',
                        'link' => route('payrolls.me'),
                    ],
                ],
            ],
            [
                'name' => 'HR',
                'icon' => 'bi bi-person-bounding-box',
                'links' => [
                    [
                        'title' => 'Suppliers',
                        'permission' => 'view supplier',
                        'link' => route('suppliers.index'),
                    ],
                ],
            ],
            [
                'name' => 'Users',
                'icon' => 'bi bi-people',
                'links' => [
                    [
                        'title' => 'User',
                        'permission' => 'view user',
                        'link' => route('users.index'),
                    ],
                ],
            ],
            [
                'name' => 'Access Control',
                'icon' => 'bi bi-lock',
                'links' => [
                    [
                        'title' => 'Roles',
                        'permission' => 'view role',
                        'link' => route('roles.index'),
                    ],
                    [
                        'title' => 'Permissions',
                        'permission' => 'view permission',
                        'link' => route('permissions.index'),
                    ],
                ],
            ],
            [
                'name' => 'Settings',
                'icon' => 'bi bi-gear',
                'links' => [
                    [
                        'title' => 'General',
                        'permission' => 'general setting',
                        'link' => route('general.settings'),
                    ],
                    [
                        'title' => 'Public Dashboard',
                        'permission' => 'public dashboard setting',
                        'link' => route('public.dashboard.settings'),
                    ],
                    [
                        'title' => 'Sidebar',
                        'permission' => 'sidebar menu setting',
                        'link' => route('aside.settings'),
                    ],
                    [
                        'title' => 'Accounts',
                        'permission' => 'account settings',
                        'link' => route('account.settings'),
                    ],
                ],
            ],
        ];

    }

    public function getMenuLinks()
    {
        return $this->menuLinks;
    }

    /**
     * Menu links that may be assigned to a tenant (company) setting.
     * Platform-only sections such as SaaS are excluded.
     *
     * @return array
     */
    public function getTenantMenuLinks()
    {
        return array_values(array_filter($this->menuLinks, function ($menu) {
            return ($menu['name'] ?? null) !== 'SaaS';
        }));
    }

    /**
     * Menu links that may be assigned to the given (or current) account.
     * Platform accounts may use every section; company accounts may not use
     * platform-only sections such as SaaS.
     *
     * @param  mixed  $user
     * @return array
     */
    public function menuLinksFor($user = null)
    {
        $user = $user ?: auth()->user();

        if ($user && method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin()) {
            return $this->menuLinks;
        }

        return $this->getTenantMenuLinks();
    }

    public function getSettings()
    {
        $settings = json_decode($this->settings->query()->select('asidebar')->filter()->first()->asidebar);
        return [
            'settings' => $settings,
            'menuLinks' => $this->menuLinks,
        ];
    }

    public function update($request)
    {
        $setting = $this->settings->query()->filter()->first();
        $this->settings->query()->where('id', $setting->id)->update([
            'asidebar' => $request->categories,
        ]);
    }

    public function reset()
    {
        $menu = $this->menuLinksFor();
        $setting = $this->settings->query()->filter()->first();
        $this->settings->query()->where('id', $setting->id)->update([
            'asidebar' => json_encode($menu)
        ]);
    }
}
