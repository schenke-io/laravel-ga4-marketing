<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GA4 Marketing Configuration
    |--------------------------------------------------------------------------
    */
    'credentials' => env('GOOGLE_MARKETING_CREDENTIALS'),

    'ga4' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID', config('services.google.ga4.measurement_id')),
        'api_secret' => env('GA4_API_SECRET', config('services.google.ga4.api_secret')),
        'debug_mode' => env('GA4_DEBUG_MODE', false),
        'client_id_salt' => env('GA4_CLIENT_ID_SALT', ''),
        'client_from_user_id' => env('GA4_CLIENT_FROM_USER_ID', false),
        'rate_limit' => [
            'enabled' => env('GA4_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('GA4_RATE_LIMIT_MAX_ATTEMPTS', 30),
            'decay_seconds' => env('GA4_RATE_LIMIT_DECAY_SECONDS', 60),
        ],
        'session_lifetime' => env('GA4_SESSION_LIFETIME', 1800), // 30 minutes in seconds
        'event_handling' => env('GA4_EVENT_HANDLING', 'api'), // 'job' or 'api'
        'cookie_name' => env('GA4_COOKIE_NAME', 'visitor'),
        'cookie_lifetime' => env('GA4_COOKIE_LIFETIME', 144000), // 100 days in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Extra Bot User-Agents
    |--------------------------------------------------------------------------
    |
    | Add additional bot user-agent fragments here to be excluded from tracking.
    |
    */
    'extra_bots' => [
        // 'custom-bot',
    ],
];
