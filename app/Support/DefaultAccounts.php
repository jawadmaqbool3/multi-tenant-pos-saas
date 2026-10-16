<?php

namespace App\Support;

/**
 * Default demo/test accounts created by the seeders. Kept in one place so the
 * user seeder, the company seeder and the login page stay in sync.
 */
class DefaultAccounts
{
    /**
     * Shared password for every default account.
     */
    public const PASSWORD = '12345678';

    /**
     * Every default account, keyed by email => display name.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'superadmin@gmail.com' => 'SaaS Owner',
            'admin@gmail.com' => 'Company Admin',
            'hr@gmail.com' => 'HR Manager',
            'accountant@gmail.com' => 'Accountant',
            'employee@gmail.com' => 'Employee',
            'sales@gmail.com' => 'Sales Person',
            'stock@gmail.com' => 'Stock Manager',
        ];
    }

    /**
     * Accounts that belong to a company's default roles, keyed by
     * email => role name.
     *
     * @return array<string, string>
     */
    public static function roleAccounts(): array
    {
        return [
            'admin@gmail.com' => 'company',
            'hr@gmail.com' => 'hr',
            'accountant@gmail.com' => 'accountant',
            'employee@gmail.com' => 'employee',
            'sales@gmail.com' => 'sales person',
            'stock@gmail.com' => 'stock manager',
        ];
    }
}
