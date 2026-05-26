<?php

return [
    'providers' => [
        'fashn' => [
            'base_url' => env('FASHN_BASE_URL'),
            'run_url' => env('FASHN_RUN_URL'),
            'status_url_template' => env('FASHN_STATUS_URL_TEMPLATE'),
            // Seller-level model/dummy config is stored in DB settings.
            'model' => 'tryon-max',
            'dummy_enabled' => false,
            'dummy_result_url' => null,
            'timeout_seconds' => (int) env('FASHN_TIMEOUT_SECONDS', 60),
            'retry_times' => (int) env('FASHN_RETRY_TIMES', 2),
            'retry_sleep_ms' => (int) env('FASHN_RETRY_SLEEP_MS', 300),
        ],
    ],
];
