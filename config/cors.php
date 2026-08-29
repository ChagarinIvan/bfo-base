<?php

declare(strict_types=1);

$configuredOrigins = trim((string) env('CORS_ALLOWED_ORIGINS', ''));
$allowedOrigins = $configuredOrigins === ''
    ? [
        (string) env('APP_URL', 'http://localhost'),
        'http://localhost',
        'http://127.0.0.1',
    ]
    : explode(',', $configuredOrigins);

$allowedOrigins = array_map(
        trim(...),
        $allowedOrigins,
    )
        |> (fn($x) => array_filter($x, static fn(string $origin): bool => $origin !== '',))
        |> array_unique(...)
        |> array_values(...);

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
