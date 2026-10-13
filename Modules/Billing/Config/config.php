<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    'api_version' => '2025-08-27.basil',
];