# ga4-marketing-setup

## Purpose
Configuration, environment setup, and health verification of the GA4 integration.

## When to Use
Use this skill when:
- Setting up the package for the first time.
- Configuring environment variables.
- Modifying `config/ga4-marketing.php`.
- Verifying the GA4 connection.
- Writing tests for GA4 integration.

## Installation

Publish the config file:
```bash
php artisan vendor:publish --tag=ga4-marketing-config
```

## Environment Variables

### Required
- `GA4_MEASUREMENT_ID` — GA4 Measurement ID, e.g. `G-XXXXXXXXXX`.
- `GA4_API_SECRET` — API secret from GA4 → Admin → Data Streams → Measurement Protocol API secrets.

### Optional
| Variable | Default | Description |
|----------|---------|-------------|
| `GA4_DEBUG_MODE` | `false` | Route events to the GA4 validation server instead of production. |
| `GA4_EVENT_HANDLING` | `api` | `api` for immediate HTTP dispatch; `job` for background queue. |
| `GA4_CLIENT_ID_SALT` | `""` | Salt used when generating client IDs from IP + User-Agent. |
| `GA4_SESSION_LIFETIME` | `1800` | Session duration in seconds (default: 30 minutes). |
| `GA4_RATE_LIMIT_ENABLED` | `true` | Enable per-client rate limiting. |
| `GA4_RATE_LIMIT_MAX_ATTEMPTS` | `30` | Max events per window before throttling. |
| `GA4_RATE_LIMIT_DECAY_SECONDS` | `60` | Rate limit window duration in seconds. |
| `GA4_COOKIE_NAME` | `visitor` | Name of the persistent visitor cookie. |
| `GA4_COOKIE_LIFETIME` | `144000` | Lifetime of the visitor cookie in minutes. |
| `GA4_CLIENT_FROM_USER_ID` | `false` | Use hashed User ID as client ID when authenticated. |

Credentials can also be set via `config/services.php`:
```php
'google' => ['ga4' => ['measurement_id' => '...', 'api_secret' => '...']],
```

## Middlewares

The package registers the following aliases for use in your routes:

- `capture-ad-parameters`: Captures Google Ad IDs from URL query.
- `track-page-view`: Server-side page view tracking (GET requests).
- `track-outbound-link`: Server-side tracking for redirects to external hosts.
- `handle-visitor-cookie`: Manages the persistent visitor identification cookie.

## Verification

Check that credentials are valid and the GA4 endpoint is reachable:
```bash
php artisan ga4-marketing:verify-ga4
```
This sends a test hit to the GA4 Measurement Protocol validation server and reports any validation errors.

## Testing Patterns

### Mock All GA4 Requests
```php
it('sends an event to GA4', function () {
    Http::fake();

    // trigger the action under test

    Http::assertSent(fn ($request) =>
        str_contains($request->url(), 'google-analytics.com') &&
        $request['events'][0]['name'] === 'expected_event'
    );
});
```

### Stub the Validation Server (debug mode)
```php
Http::fake([
    'https://www.google-analytics.com/debug/mp/collect*' => Http::response(
        ['validationMessages' => []],
        200
    ),
]);
```

An empty `validationMessages` array means the payload is valid. Non-empty entries contain `fieldPath` and `description` fields describing what GA4 rejected.