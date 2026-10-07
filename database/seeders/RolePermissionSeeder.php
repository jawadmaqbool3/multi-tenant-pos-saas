<?php

namespace Database\Seeders;

use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\PermissionModule;
use Modules\RBAC\Models\PermissionRole;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\UserRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [

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

            "Company" => [
                'view company',
                'create company',
                'edit company',
                'delete company',
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
        $roles = [
            'superadmin',
            'employee',
            'admin',
            'manager',
            'customer',
        ];
        foreach ($modules  as $key => $module) {
            $_module = PermissionModule::create([
                "name" => $key
            ]);
            foreach ($module as $key => $permission) {
                Permission::create([
                    'name' => $permission,
                    'module_id' => $_module->id
                ]);
            }
        }
        foreach ($roles as $key => $role) {
            Role::create([
                'name' => $role
            ]);
            if ($key == 0) {
                $permissions = Permission::get();
                foreach ($permissions as $permission) {
                    PermissionRole::create([
                        'role_id' => 1,
                        'permission_id' => $permission->id
                    ]);
                }
            }
        }
        UserRole::create([
            'user_id' => 1,
            'role_id' => 1
        ]);
    }
}
