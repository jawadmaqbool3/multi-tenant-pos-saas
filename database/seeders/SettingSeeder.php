<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::firstOrCreate(
            ['company_id' => null],
            [
            'logo' => 'logo.jpeg',
            'name' => 'POS',
            'address' => 'Some address lorem ipsum',
            'url' => 'https://pos.local',
            'facebook' => 'pos.facebook.com',
            'instagram' => '@pos',
            'whatsapp' => '',
            'email' => 'jawadmaqbool32@gmail.com',
            'contact' => '03445909505',
            'asidebar' => '[
                {
                    "icon": "bi bi-share",
                    "name": "SaaS",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/companies",
                            "title": "Companies",
                            "permission": "view company"
                        }
                    ]
                },
                {
                    "icon": "bi bi-credit-card",
                    "name": "Billing",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/plans",
                            "title": "Plans",
                            "permission": "view billing plan"
                        }
                    ]
                },
                {
                    "icon": "bi bi-shop",
                    "name": "Inventory",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/categories",
                            "title": "Categories",
                            "permission": "view category"
                        },
                        {
                            "link": "http://127.0.0.1:8000/products",
                            "title": "Products",
                            "permission": "view product"
                        }
                    ]
                },
                {
                    "icon": "bi bi-cart",
                    "name": "Orders",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/orders",
                            "title": "Sales Orders",
                            "permission": "view order"
                        },
                        {
                            "link": "http://127.0.0.1:8000/purchase-orders",
                            "title": "Purchase Orders",
                            "permission": "view purchase orders"
                        }
                    ]
                },
                {
                    "icon": "bi bi-award-fill",
                    "name": "Promotions",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/promotions",
                            "title": "Promotions",
                            "permission": "view promotion"
                        }
                    ]
                },
                {
                    "icon": "bi bi-cash-stack",
                    "name": "Payroll",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/payrolls",
                            "title": "Payroll Runs",
                            "permission": "view payroll"
                        },
                        {
                            "link": "http://127.0.0.1:8000/allowances",
                            "title": "Allowances",
                            "permission": "view allowance"
                        },
                        {
                            "link": "http://127.0.0.1:8000/deductions",
                            "title": "Deductions",
                            "permission": "view deduction"
                        }
                    ]
                },
                {
                    "icon": "bi bi-file-earmark-bar-graph",
                    "name": "Reports",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/stocks",
                            "title": "Stocks",
                            "permission": "view stocks"
                        },
                        {
                            "link": "http://127.0.0.1:8000/sales",
                            "title": "Sales",
                            "permission": "view sales"
                        },
                        {
                            "link": "http://127.0.0.1:8000/attendance",
                            "title": "Employee Attendance",
                            "permission": "view employee attendance report"
                        },
                        {
                            "link": "http://127.0.0.1:8000/attendance/me",
                            "title": "My Attendance",
                            "permission": "view own attendance"
                        },
                        {
                            "link": "http://127.0.0.1:8000/payrolls/me",
                            "title": "My Payslips",
                            "permission": "view own payroll"
                        }
                    ]
                },
                {
                    "icon": "bi bi-person-bounding-box",
                    "name": "HR",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/suppliers",
                            "title": "Suppliers",
                            "permission": "view supplier"
                        }
                    ]
                },
                {
                    "icon": "bi bi-people",
                    "name": "Users",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/users",
                            "title": "User",
                            "permission": "view user"
                        }
                    ]
                },
                {
                    "icon": "bi bi-lock",
                    "name": "Access Control",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/roles",
                            "title": "Roles",
                            "permission": "view role"
                        },
                        {
                            "link": "http://127.0.0.1:8000/permissions",
                            "title": "Permissions",
                            "permission": "view permission"
                        }
                    ]
                },
                {
                    "icon": "bi bi-gear",
                    "name": "Settings",
                    "links": [
                        {
                            "link": "http://127.0.0.1:8000/settings/general",
                            "title": "General",
                            "permission": "general setting"
                        },
                        {
                            "link": "http://127.0.0.1:8000/settings/public/dashboard",
                            "title": "Public Dashboard",
                            "permission": "public dashboard setting"
                        },
                        {
                            "link": "http://127.0.0.1:8000/settings/aside",
                            "title": "Sidebar Setting",
                            "permission": "sidebar menu setting"
                        }
                    ]
                }
            ]',
        ]);
    }
}
