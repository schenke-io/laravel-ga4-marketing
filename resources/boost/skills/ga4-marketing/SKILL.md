---
name: ga4-marketing
description: Comprehensive guide for GA4 integration, including setup, server-side tracking, and Blade components.
---

# ga4-marketing

## Purpose
Comprehensive guidelines for implementing GA4 tracking, covering installation, environment configuration, server-side service usage, and front-end Blade/JavaScript integration.

## When to Use
Use this skill when:
- Setting up the package for the first time or configuring environment variables.
- Using `AnalyticsService` for server-side event tracking and magic methods.
- Including tracking scripts and using declarative data attributes in Blade templates.
- Using the `window.ga4Marketing` or `window.ga4Event` JavaScript API.
- Integrating GA4 events with Livewire components.
- Configuring and using package middlewares.
- Verifying the GA4 connection or writing integration tests.

## Subskill: Setup & Configuration

### Installation
Publish the config file:
```bash
php artisan vendor:publish --tag=ga4-marketing-config
```

### Environment Variables

#### Required
- `GA4_MEASUREMENT_ID` — GA4 Measurement ID, e.g. `G-XXXXXXXXXX`.
- `GA4_API_SECRET` — API secret from GA4 → Admin → Data Streams → Measurement Protocol API secrets.

#### Optional
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

### Verification
Check that credentials are valid and the GA4 endpoint is reachable:
```bash
php artisan ga4-marketing:verify-ga4
```
This sends a test hit to the GA4 Measurement Protocol validation server and reports any validation errors.

### Testing Patterns

#### Mock All GA4 Requests
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

#### Stub the Validation Server (debug mode)
```php
Http::fake([
    'https://www.google-analytics.com/debug/mp/collect*' => Http::response(
        ['validationMessages' => []],
        200
    ),
]);
```
An empty `validationMessages` array means the payload is valid.

## Subskill: Server-Side Tracking (Code)

### AnalyticsService
The primary entry point for server-side tracking. Handles client ID generation, session management, and event dispatching.

#### Immediate Tracking
```php
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

public function __construct(protected AnalyticsService $ga4) {}

public function completePurchase(): void
{
    $this->ga4->sendEvent($this->ga4->getClientId(), 'purchase', [
        'transaction_id' => 'T_12345',
        'value' => 25.00,
        'currency' => 'USD',
    ]);
}
```

#### Fluent Event Methods
Magic methods map positional arguments to GA4 parameters via `EventMapper`:

| Method | Arguments |
|--------|-----------|
| `pageView` | `string $pageLocation, ?string $pageTitle, ?string $pageReferrer, ?string $language` |
| `click` | `string $linkUrl, ?string $linkText, ?string $linkId, ?string $linkClasses, ?string $linkDomain, ?bool $outbound` |
| `login` | `string $method` |
| `signUp` | `string $method` |
| `share` | `string $method, string $contentType, string $itemId` |
| `search` | `string $searchTerm` |
| `viewItem` | `array $items, ?string $currency, ?float $value` |
| `addToCart` | `array $items, ?string $currency, ?float $value` |
| `beginCheckout` | `array $items, ?string $currency, ?float $value, ?string $coupon` |
| `purchase` | `string $transactionId, array $items, ?float $value, ?string $currency, ?float $tax, ?float $shipping, ?string $coupon` |
| `scroll` | `int $percentScrolled` |
| `fileDownload` | `string $fileName, ?string $fileExtension, ?string $linkUrl, ?string $linkText, ?string $linkId, ?string $linkClasses, ?string $linkDomain` |
| `calculatorUsed` | `array $params` |

```php
$this->ga4->login('google');
$this->ga4->purchase('T_12345', $items, 49.99, 'USD');
```

#### Background Tracking (Queues)
To avoid blocking the HTTP response, use `queueEvent()` which dispatches `SendAnalyticsEventJob`:
```php
$this->ga4->queueEvent($this->ga4->getClientId(), 'long_running_event', ['param' => 'value']);
```
Set `GA4_EVENT_HANDLING=job` in `.env` to also queue JS-triggered (front-end) events.

### Internal Components

#### SessionManager
Manages `session_id`, `engagement_time_msec`, `first_visit`, and `session_start` flags. State is stored in the Laravel cache using the client ID as key.

#### PayloadBuilder
Constructs the Measurement Protocol JSON payload. Automatically anonymizes IP addresses before sending.

#### BotDetector
Filters requests from known bots by matching User-Agent strings. Add custom fragments in `config/ga4-marketing.php` under `extra_bots`.

## Subskill: Front-End Tracking (Blade)

### Blade Integration

#### Include the Script
Add to your layout file:
```blade
@Ga4MarketingScript
```
Or as a component:
```blade
<x-ga4-marketing::ga4-marketing />
```

#### Vite / Bundling Integration
If you bundle the tracker, use the config-only directive:
```blade
@Ga4MarketingConfig
```
On init, the tracker automatically fires a `page_view` event. To suppress it:
```html
<body data-ga4="no-pageview">
```

### Data Attributes

#### Click Tracking
```html
<button data-ga4-event="button_click">Click Me</button>

<button data-ga4-event="add_to_cart"
        data-ga4-params='{"item_id": "SKU_123", "item_name": "Product X"}'>
    Add to Cart
</button>
```

#### Outbound Links
Use `data-ga4-event="outbound"` on `<a>` tags. The tracker fires a `click` event with `outbound: true`.

#### Scroll / Visibility Tracking
Use `data-ga4="scroll"` to fire a `scroll` event when the element becomes visible.
```html
<div data-ga4="scroll" data-ga4-area="pricing-section">...</div>
```

### JavaScript API
`window.ga4Event` is a convenience alias for `window.ga4Marketing.sendEvent()`:
```javascript
window.ga4Event('custom_event', { category: 'engagement' });
```

### Livewire Integration
The tracker listens for `ga4-event` dispatched from Livewire components:
```php
$this->dispatch('ga4-event', 'file_download', ['file_name' => 'report.pdf']);
```

## Subskill: Middlewares
The package registers the following aliases:
- `handle-visitor-cookie`: Manages persistent visitor identification (default in `web`).
- `capture-ad-parameters`: Reads `gclid`, `wbraid`, and `gbraid` from the URL.
- `track-page-view`: Tracks a `page_view` event on GET requests (server-side).
- `track-outbound-link`: Tracks a `click` event for external redirects (server-side).
