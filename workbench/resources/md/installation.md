## Installation

You can install the package via composer:

```bash
composer require schenke-io/laravel-ga4-marketing
```

You should publish the config file with:

```bash
php artisan vendor:publish --tag="ga4-marketing-config"
```

### Client-Side Setup

To enable automatic page view tracking and declarative event tracking (clicks, visibility), add the following Blade directive to your main layout file (e.g., `resources/views/layouts/app.blade.php`), typically before the closing `</body>` tag:

```html
@G4MarketingScript
```

### Configuration

Set up your Google Analytics 4 credentials in your `.env` file:

```env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
GA4_API_SECRET=your-api-secret
```

You can find these values in your Google Analytics Admin panel under **Data Streams > [Your Stream] > Measurement
Protocol API secrets**.

#### Advanced Configuration

The `config/ga4-marketing.php` file allows you to customize several aspects of the package:

```php
return [
    /*
     * Full path to the service account credentials JSON file.
     * This is required for server-to-server authentication.
     */
    'credentials' => env('GOOGLE_MARKETING_CREDENTIALS'),

    'ga4' => [
        /*
         * Your Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX).
         * Found in GA4 Admin > Data Streams > [Your Stream].
         */
        'measurement_id' => env('GA4_MEASUREMENT_ID', config('services.google.ga4.measurement_id')),

        /*
         * The Measurement Protocol API Secret.
         * Created in GA4 Admin > Data Streams > [Your Stream] > Measurement Protocol API secrets.
         */
        'api_secret' => env('GA4_API_SECRET', config('services.google.ga4.api_secret')),

        /*
         * When enabled, events include 'debug_mode=1', making them
         * instantly visible in the GA4 DebugView for troubleshooting.
         */
        'debug_mode' => env('GA4_DEBUG_MODE', false),

        /*
         * A string used to salt the hash when generating anonymous Client IDs.
         * Changing this will reset the identity of returning anonymous users.
         */
        'client_id_salt' => env('GA4_CLIENT_ID_SALT', ''),

        'rate_limit' => [
            /*
             * Enable or disable server-side rate limiting per client
             * to prevent API abuse or excessive event tracking.
             */
            'enabled' => env('GA4_RATE_LIMIT_ENABLED', true),

            /*
             * The maximum number of events allowed per client within
             * the specified decay period.
             */
            'max_attempts' => env('GA4_RATE_LIMIT_MAX_ATTEMPTS', 30),

            /*
             * The time window (in seconds) for rate limiting. After this period,
             * the attempt count for a client is reset.
             */
            'decay_seconds' => env('GA4_RATE_LIMIT_DECAY_SECONDS', 60),
        ],

        /*
         * The duration (in seconds) of inactivity before a new session is started.
         * Defaults to 1800 seconds (30 minutes).
         */
        'session_lifetime' => env('GA4_SESSION_LIFETIME', 1800),

        /*
         * How events are processed: 'api' for immediate sending or 'job' to
         * queue them for background processing.
         */
        'event_handling' => env('GA4_EVENT_HANDLING', 'api'),
    ],

    /*
     * Add additional bot user-agent fragments here to be excluded from tracking.
     * Useful for filtering out custom crawlers or internal monitoring tools.
     */
    'extra_bots' => [
        // 'custom-bot',
    ],
];
```
