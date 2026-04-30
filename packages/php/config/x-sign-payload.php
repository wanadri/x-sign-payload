<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    |
    | This secret key is used to sign and verify request payloads.
    | Generate a secure 256-bit secret using the install command:
    | php artisan x-sign:install
    |
    */
    'secret' => env('X_SIGN_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Algorithm
    |--------------------------------------------------------------------------
    |
    | The HMAC algorithm to use for signing. Supported: sha256, sha512
    |
    */
    'algorithm' => env('X_SIGN_ALGORITHM', 'sha256'),

    /*
    |--------------------------------------------------------------------------
    | Enable Timestamp
    |--------------------------------------------------------------------------
    |
    | When enabled, timestamps are included in signatures to prevent
    | replay attacks. Disable only for specific use cases like webhooks
    | with idempotency keys.
    |
    */
    'enable_timestamp' => env('X_SIGN_ENABLE_TIMESTAMP', true),

    /*
    |--------------------------------------------------------------------------
    | Replay Window
    |--------------------------------------------------------------------------
    |
    | The number of minutes a signed request remains valid.
    | Requests older than this window will be rejected.
    |
    */
    'replay_window' => (int) env('X_SIGN_REPLAY_WINDOW', 10),

    /*
    |--------------------------------------------------------------------------
    | Route Exclusions
    |--------------------------------------------------------------------------
    |
    | Routes that should skip signature verification.
    | Supports wildcards using *.
    |
    */
    'routes' => [
        'exclude' => [
            // 'api/webhook/*',
            // 'api/public/*',
        ],
    ],
];
