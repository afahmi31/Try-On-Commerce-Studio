<?php

return [
    'retention_minutes' => (int) env('TRYON_RETENTION_MINUTES', 30),
    'polling' => [
        'max_attempts' => (int) env('TRYON_PROVIDER_MAX_ATTEMPTS', 90),
        'release_seconds' => (int) env('TRYON_PROVIDER_RELEASE_SECONDS', 2),
    ],
    'token_policy' => [
        // If true, token is still charged when provider has accepted/executed the job but final status failed.
        'charge_on_provider_failure' => (bool) env('TRYON_CHARGE_ON_PROVIDER_FAILURE', false),
    ],
    'public_limits' => [
        // Hard cap fallback for customer/public generate requests.
        // Primary source is seller setting (public_generate_per_day).
        'generate_per_day' => 3,
        // Burst protection for repeated clicks/spam.
        'generate_per_minute_per_ip' => 3,
        // Polling limit stays higher because frontend polls processing status.
        'polling_per_minute' => (int) env('TRYON_PUBLIC_POLLING_PER_MINUTE', 120),
    ],
    'reserved_seller_slugs' => [
        'dashboard',
        'api',
        'login',
        'logout',
        'setup',
    ],
];
