<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe configuration
    |--------------------------------------------------------------------------
    |
    | `currency` is a dedicated billing currency (STRIPE_CURRENCY, e.g. usd).
    | It is separate from `config('app.currency')` which stays PKR for POS
    | display purposes.
    |
    */

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    'api_version' => '2025-08-27.basil',
];