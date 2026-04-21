# ga4-marketing-code

## Purpose
Best practices for server-side event tracking and interacting with the package's core services.

## When to Use
Use this skill when:
- Using `AnalyticsService` for server-side event tracking.
- Using fluent magic methods for common GA4 events.
- Configuring background queue processing for events.
- Adding custom bot user-agent filters.

## AnalyticsService

The primary entry point for server-side tracking. Handles client ID generation, session management, and event dispatching.

### Immediate Tracking
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

### Fluent Event Methods
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

```php
$this->ga4->login('google');
$this->ga4->purchase('T_12345', $items, 49.99, 'USD');
```

### Background Tracking (Queues)
To avoid blocking the HTTP response, use `queueEvent()` which dispatches `SendAnalyticsEventJob`:
```php
$this->ga4->queueEvent($this->ga4->getClientId(), 'long_running_event', ['param' => 'value']);
```
Set `GA4_EVENT_HANDLING=job` in `.env` to also queue JS-triggered (front-end) events.

## Internal Components

### Middlewares
The package includes several middlewares for automatic tracking:
- `TrackPageView`: Tracks page views on GET requests. Useful for environments without JavaScript.
- `TrackOutboundLink`: Tracks redirects to external domains.
- `CaptureAdParameters`: Captures Google Ad IDs (`gclid`, etc.) for attribution.

### SessionManager
Manages `session_id`, `engagement_time_msec`, `first_visit`, and `session_start` flags. State is stored in the Laravel cache using the client ID as key.

### PayloadBuilder
Constructs the Measurement Protocol JSON payload. Automatically anonymizes IP addresses before sending.

### BotDetector
Filters requests from known bots by matching User-Agent strings. Add custom fragments to exclude internal tools:
```php
// config/ga4-marketing.php
'extra_bots' => [
    'my-internal-monitor',
],
```

### EventMapper
Maps magic method arguments to GA4 parameter arrays. See the fluent methods table above for the exact argument order.