<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\SettingNotFoundException;

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
                'name' => 'Billing',
                'icon' => 'bi bi-credit-card',
                'links' => [
                    [
                        'title' => 'Plans',
                        'permission' => 'view billing plan',
                        'link' => route('billing.plans.index'),
                    ],
                ],
            ],
            [
                'name' => 'Bank',
                'icon' => 'bi bi-bank',
                'links' => [
                    [
                        'title' => 'Chart of Accounts',
                        'permission' => 'view account',
                        'link' => route('accounts.index'),
                    ],
                    [
                        'title' => 'Vouchers',
                        'permission' => 'view voucher',
                        'link' => route('vouchers.index'),
                    ],
                    [
                        'title' => 'Voucher Types',
                        'permission' => 'view voucher type',
                        'link' => route('voucher-types.index'),
                    ],
                    [
                        'title' => 'Ledger',
                        'permission' => 'view ledger',
                        'link' => route('ledger.index'),
                    ],
                    [
                        'title' => 'Balance Sheet',
                        'permission' => 'view balance sheet',
                        'link' => route('balance-sheet.index'),
                    ],
                    [
                        'title' => 'Payroll Runs',
                        'permission' => 'view payroll',
                        'link' => route('payrolls.index'),
                    ],
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
                    [
                        'title' => 'Suppliers',
                        'permission' => 'view supplier',
                        'link' => route('suppliers.index'),
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
                        'title' => 'Employees',
                        'permission' => 'view employee',
                        'link' => route('employee.index'),
                    ],
                    [
                        'title' => 'Attendance',
                        'permission' => 'view employee attendance report',
                        'link' => route('attendance.index'),
                    ],
                    [
                        'title' => 'Bulk Payroll',
                        'permission' => 'view payroll',
                        'link' => route('payrolls.create'),
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
                        'title' => 'Account Settings',
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
        $menus = array_values(array_filter($this->menuLinks, function ($menu) {
            return ($menu['name'] ?? null) !== 'SaaS';
        }));

        // The tenant-only self-service subscription link is added here so it is
        // never offered to platform (SaaS owner) accounts.
        foreach ($menus as $i => $menu) {
            if (($menu['name'] ?? null) !== 'Billing') {
                continue;
            }

            $menus[$i]['links'][] = [
                'title' => 'My Subscription',
                'permission' => 'manage company subscription',
                'link' => route('billing.subscription'),
            ];
        }

        return $menus;
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
        $record = $this->settings->query()->select('asidebar')->filter()->first();
        $settings = $record ? json_decode($record->asidebar) : null;
        return [
            'settings' => $settings,
            'menuLinks' => $this->menuLinks,
        ];
    }

    public function update($request)
    {
        $categories = $request->categories;
        if (is_array($categories)) {
            $categories = json_encode($categories);
        } elseif ($categories !== null && json_decode($categories) === null) {
            throw new InvalidFieldException('categories');
        }
        $setting = $this->settings->query()->filter()->first();
        if (!$setting) {
            throw new SettingNotFoundException();
        }
        $this->settings->query()->where('id', $setting->id)->update([
            'asidebar' => $categories,
        ]);
    }

    public function reset()
    {
        $menu = $this->menuLinksFor();
        $setting = $this->settings->query()->filter()->first();
        if (!$setting) {
            throw new SettingNotFoundException();
        }
        $this->settings->query()->where('id', $setting->id)->update([
            'asidebar' => json_encode($menu)
        ]);
    }
}
