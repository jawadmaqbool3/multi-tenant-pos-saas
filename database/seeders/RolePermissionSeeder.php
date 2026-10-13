<?php

namespace Database\Seeders;

use Modules\Employee\Models\User;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\PermissionModule;
use Modules\RBAC\Models\PermissionRole;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Platform (SaaS owner) only permissions. These are never granted to a
     * company/tenant admin role.
     *
     * @var array
     */
    protected $platformModules = [
        "Company" => [
            'view company',
            'create company',
            'edit company',
            'delete company',
        ],

        "SaaS" => [
            'view tenant',
            'manage subscription',
        ],

        "Billing" => [
            'view billing plan',
            'create billing plan',
            'edit billing plan',
            'delete billing plan',
            'view subscription',
            'view billing invoice',
            'view coupon',
            'create coupon',
            'edit coupon',
            'delete coupon',
            'assign plan',
            'suspend company',
            'view company billing',
            'view billing report',
        ],
    ];

    /**
     * Tenant (company) permissions.
     *
     * @var array
     */
    protected $modules = [
        "Permissions" => [
            'view permission',
            'create permission',
            'edit permission',
            'delete permission',
        ],

        "Role" => [
            'view role',
            'create role',
            'edit role',
            'delete role',
        ],

        "Users" => [
            'view user',
            'create user',
            'edit user',
            'delete user',
        ],

        "Managers" => [
            'view manager',
            'create manager',
            'edit manager',
            'delete manager',
        ],

        "Employees" => [
            'view employee',
            'create employee',
            'edit employee',
            'delete employee',
            'view employee attendance report',
        ],

        "Products" => [
            'view product',
            'create product',
            'edit product',
            'delete product',
            'view stocks',
            'print barcode',
        ],

        "Categories" => [
            'view category',
            'create category',
            'edit category',
            'delete category',
        ],

        "Accounts" => [
            'view account',
            'create account',
            'edit account',
            'delete account',
            'account settings'
        ],


        "Suppliers" => [
            'view supplier',
            'create supplier',
            'edit supplier',
            'delete supplier',
            'pay supplier',
        ],

        "Voucher Types" => [
            'view voucher type',
            'create voucher type',
            'edit voucher type',
            'delete voucher type',
        ],


        "Vouchers" => [
            'view voucher',
            'create voucher',
            'edit voucher',
            'delete voucher',
        ],

        "Sales Orders" => [
            'place order',
            'view order',
            'cancel order',
            'mark paid',
            'view sales',
            'order refund',
        ],

        "Purchase Orders" => [
            'add stock',
            'view purchase orders',
            'print purchase orders',
        ],

        "Customers" => [
            'view customer',
        ],


        "Dashboard" => [
            'dashboard profit loss report',
            'dashboard daily average sales',
            'dashboard monthly sales',
            'dashboard monthly orders',
            'dashboard monthly cancelled orders',
            'dashboard monthly refunded orders',
            'dashboard monthly customers',
            'dashboard recent orders',
            'dashboard stock report',
            'dashboard employee mark attendance',
        ],

        "Promotions" => [
            'view promotion',
            'create promotion',
            'edit promotion',
            'delete promotion',
        ],


        "Settings" => [
            'general setting',
            'public dashboard setting',
            'sidebar menu setting',
            'account settings'
        ],


    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedPermissions($this->modules, false);
        $this->seedPermissions($this->platformModules, true);

        $roles = [
            'admin',
            'company',
            'manager',
            'employee',
            'customer',
        ];

        $platformRole = null;
        foreach ($roles as $key => $role) {
            $roleModel = Role::firstOrCreate([
                'name' => $role,
                'company_id' => null,
            ], [
                'status' => 'active',
            ]);
            $roleModel->refresh();
            if ($key == 0) {
                $platformRole = $roleModel;
            }
        }

        if ($platformRole) {
            foreach (Permission::where('company_id', null)->get() as $permission) {
                PermissionRole::firstOrCreate([
                    'role_id' => $platformRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        $superAdmin = User::where('email', 'superadmin@gmail.com')->first();
        if ($superAdmin && $platformRole) {
            UserRole::firstOrCreate([
                'user_id' => $superAdmin->id,
                'role_id' => $platformRole->id,
            ]);
        }
    }

    /**
     * Create a permission module and its permissions.
     *
     * @param  array  $modules
     * @param  bool  $isPlatform
     * @return void
     */
    protected function seedPermissions($modules, $isPlatform)
    {
        foreach ($modules as $key => $module) {
            $_module = PermissionModule::firstOrCreate(
                ['name' => $key],
                ['is_platform' => $isPlatform]
            );
            if ($_module->is_platform != $isPlatform) {
                $_module->update(['is_platform' => $isPlatform]);
            }
            $_module->refresh();
            foreach ($module as $permission) {
                Permission::firstOrCreate(
                    [
                        'name' => $permission,
                        'module_id' => $_module->id,
                        'company_id' => null,
                    ],
                    ['is_platform' => $isPlatform]
                );
            }
        }
    }
}
