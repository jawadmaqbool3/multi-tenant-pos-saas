<?php

return [
    'namespace' => 'Modules',
    'paths' => [
        'modules' => base_path('Modules'),
        'migrations' => 'Database/Migrations',
        'views' => 'Resources/views',
        'assets' => 'Resources/assets',
        'lang' => 'Resources/lang',
        'config' => 'Config',
    ],
    'enabled' => [
        'Billing',
        'ChartOfAccounts',
        'Employee',
        'Inventory',
        'Order',
        'RBAC',
        'Supplier',
    ],
    'register' => [
        'route_namespace' => null,
    ],
];
