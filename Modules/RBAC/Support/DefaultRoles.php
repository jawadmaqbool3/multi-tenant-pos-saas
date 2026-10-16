<?php

namespace Modules\RBAC\Support;

/**
 * Canonical map of the default tenant (company) roles and the permissions each
 * receives when a company is provisioned. Keys are role names; values are
 * permission names seeded globally (company_id = null) by the
 * RolePermissionSeeder.
 *
 * This map is the single source of truth used by the seeder, the RBAC module
 * provisioning service and company creation.
 */
class DefaultRoles
{
    public static function roles(): array
    {
        return [
            // Sees only their own attendance history and payslips (enforced in code).
            'employee' => [
                'view own attendance',
                'view own payroll',
            ],

            // Manages employees and runs payroll.
            'hr' => [
                'view user',
                'create user',
                'edit user',
                'delete user',
                'view employee',
                'create employee',
                'edit employee',
                'delete employee',
                'view employee attendance report',
                'view payroll',
                'create payroll',
                'edit payroll',
                'delete payroll',
                'view allowance',
                'create allowance',
                'edit allowance',
                'delete allowance',
                'view deduction',
                'create deduction',
                'edit deduction',
                'delete deduction',
            ],

            // Accounting/bookkeeping and paying payroll.
            'accountant' => [
                'view account',
                'create account',
                'edit account',
                'delete account',
                'account settings',
                'view voucher',
                'create voucher',
                'edit voucher',
                'delete voucher',
                'view voucher type',
                'create voucher type',
                'edit voucher type',
                'delete voucher type',
                'view payroll',
                'pay payroll',
            ],

            // Sells using the product catalog and processes sales orders.
            'sales person' => [
                'view product',
                'create product',
                'edit product',
                'delete product',
                'view stocks',
                'print barcode',
                'view category',
                'create category',
                'edit category',
                'delete category',
                'place order',
                'view order',
                'cancel order',
                'mark paid',
                'view sales',
                'order refund',
                'view customer',
            ],

            // Manages stock levels, purchasing, products and categories.
            'stock manager' => [
                'view product',
                'create product',
                'edit product',
                'delete product',
                'view stocks',
                'print barcode',
                'view category',
                'create category',
                'edit category',
                'delete category',
                'add stock',
                'view purchase orders',
                'print purchase orders',
            ],
        ];
    }

    public static function names(): array
    {
        return array_keys(self::roles());
    }
}
