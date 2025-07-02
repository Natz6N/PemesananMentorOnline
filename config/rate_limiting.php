<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini untuk mengatur rate limiting pada aplikasi.
    | Setiap grup memiliki konfigurasi sendiri untuk maksimum permintaan
    | dan waktu reset (dalam menit).
    |
    */

    'enabled' => env('RATE_LIMITING_ENABLED', true),

    'groups' => [
        // Grup API umum (60 requests per menit)
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],

        // Grup untuk autentikasi (5 requests per menit)
        'auth' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],

        // Grup untuk payment gateway (10 requests per menit)
        'payment' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Headers
    |--------------------------------------------------------------------------
    |
    | Saat rate limiting aktif, tambahkan header-header ini ke dalam response
    | untuk memberikan informasi tentang status rate limiting ke client.
    |
    */
    'headers' => [
        'limit' => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset' => 'X-RateLimit-Reset',
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttle Key Format
    |--------------------------------------------------------------------------
    |
    | Format untuk menghasilkan throttle key.
    | Default: [group]:[identifier]
    | Contoh: api:1 (untuk user dengan ID 1 pada grup api)
    |
    */
    'key_format' => '{group}:{identifier}',
];
