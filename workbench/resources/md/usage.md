## Usage

### JavaScript Tracking Initialization

To enable client-side tracking, include the following Blade directive in your main layout (usually before the closing `</body>` tag):

```html
@Ga4MarketingScript
```

This directive replaces the legacy `<x-ga4-marketing::ga4-marketing />` component. It renders the necessary tracking scripts and handles automatic events. It ensures scripts are only included once per page.

### Automatic Page View Tracking

When `@Ga4MarketingScript` is included, a `page_view` event is automatically sent to GA4 on window load.

#### Disabling Automatic Page Views

If you want to disable automatic tracking for a specific page, add `data-ga4-event="no-pageview"` to the `<body>` tag:

```html
<body data-ga4-event="no-pageview">
```

### Google Ads Attribution

To capture Google Ad IDs (`gclid`, `wbraid`, `gbraid`) from the URL and store them in the server-side cache, add the `capture-ad-parameters` middleware to your routes. This is recommended to be used alongside page view tracking:

```php
Route::middleware(['capture-ad-parameters'])->group(function () {
    // your routes
});
```

The captured IDs are automatically included in all subsequent events sent via `AnalyticsService`.

### Server-side Tracking Middlewares

The package provides optional middlewares for server-side tracking.

#### Page View Tracking
To track page views on the server-side, add the `track-page-view` middleware. This is useful when you want to ensure page views are tracked even if JavaScript is disabled or blocked.

```php
Route::middleware(['track-page-view'])->group(function () {
    // your routes
});
```

#### Outbound Link Tracking
To track outbound links that go through a redirect in your application, use the `track-outbound-link` middleware. It automatically sends a `click` event with `outbound: true` when a `RedirectResponse` to an external domain is detected.

```php
Route::middleware(['track-outbound-link'])->group(function () {
    Route::get('/external-redirect', function () {
        return redirect('https://external-site.com');
    });
});
```

### Sending Custom Events

You can also use the `AnalyticsService` to send custom events manually:

```php
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

public function someAction(AnalyticsService $ga4Service)
{
    $clientId = $ga4Service->generateClientId(request()->ip(), request()->userAgent());
    
    $ga4Service->sendEvent($clientId, 'button_click', [
        'button_name' => 'subscribe',
        'location' => 'footer'
    ]);
}
```

### Recommended Events

The `AnalyticsService` provides helper methods for standard GA4 events using camelCase naming:

```php
// E-commerce events
$ga4Service->viewItem($items, 'USD', 10.0);
$ga4Service->addToCart($items, 'USD', 10.0);
$ga4Service->beginCheckout($items, 'USD', 10.0, 'COUPON');
$ga4Service->purchase($transactionId, $items, 99.99, 'USD');

// Other recommended events
$ga4Service->login('google');
$ga4Service->signUp('email');
$ga4Service->search('query');
$ga4Service->share('social', 'article', '123');
$ga4Service->scroll(90);
$ga4Service->fileDownload('report.pdf', 'pdf', 'https://example.com/report.pdf');
$ga4Service->calculatorUsed(['type' => 'mortgage']);
```

### Bot Filtering

The package automatically filters out common bots to keep your analytics clean. You can extend the built-in bot blacklist in `config/ga4-marketing.php`:

```php
'extra_bots' => [
    'custom-bot-fragment',
    // ... add more as needed
],
```

### Livewire Event Bridging

You can automatically bridge Livewire events to GA4 by dispatching a `ga4-event` from your Livewire component. The package intercepts this and dispatches a browser event (`ga4-event-triggered`), which is then sent to GA4 by the client-side tracker.

```php
$this->dispatch('ga4-event', 'button_click', [
    'button_name' => 'subscribe'
]);
```

### JavaScript Event Bridging

To send events from JavaScript, first include the tracking script in your layout using the Blade directive:

```html
@Ga4MarketingScript
```

This directive renders the tracking scripts and ensures they are only included once. You can then use the `window.ga4Event` helper:

```javascript
ga4Event('js_button_click', {
    source: 'home_banner'
});
```

#### Vite & Bundling

If you prefer to bundle the tracking logic with your application, you can import the tracker from the package resources:

```javascript
import { ga4Event } from '../../vendor/schenke-io/laravel-ga4-marketing/resources/js/ga4-tracker';

ga4Event('custom_event', { key: 'value' });
```

### Visibility Tracking

#### Declarative Tracking

The easiest way to track visibility is by adding `data-ga4-event="scroll"` to any element. You can specify the area name using `data-ga4-area`:

```html
<div data-ga4-event="scroll" data-ga4-area="pricing_table">
    <!-- content -->
</div>
```

This sends a `scroll` event with the `visible_area` parameter when the element becomes visible in the viewport.

### Click Tracking

You can automatically track clicks on any element using declarative data attributes. 

#### Outbound Link Tracking

To track clicks to external domains, use `data-ga4-event="outbound"` on an `<a>` tag:

```html
<a href="https://example.com" data-ga4-event="outbound">External Link</a>
```

This automatically sets the event name to `click`, sets `outbound: true`, and extracts `link_url` and `link_domain`.

#### Custom Click Events

Use `data-ga4-event` for the event name and `data-ga4-params` for optional parameters (as a JSON string):

```html
<button data-ga4-event="signup_click" data-ga4-params='{"source": "hero"}'>
    Sign Up
</button>
```

#### Automatic Parameter Extraction

When you use tracking on an `<a>` tag, the package automatically captures:

| Parameter | Source | Description |
|-----------|--------|-------------|
| `link_url` | `href` | The destination URL of the link. |
| `link_text` | `innerText` | The visible text inside the link. |
| `link_id` | `id` | The ID attribute of the element. |
| `link_classes` | `className` | The CSS classes applied to the link. |
| `link_domain` | `href` | The hostname (for outbound links). |

### User Engagement Tracking

The tracker automatically tracks the time a user spends on a page and sends a `user_engagement` event (with `engagement_time_msec`) when the user navigates away or closes the tab. This requires `@Ga4MarketingScript` to be present.

### User ID Tracking

To track a specific user across sessions, you can set the User ID in the `AnalyticsService`:

```php
$ga4Service->setUserId('user-123');
```

Once set, the `user_id` will be included in all subsequent events sent during the current request.

### Event Validation (Debug Mode)

You can enable debug mode on the `AnalyticsService` to send events to the GA4 validation endpoint instead of the live collection endpoint. This is useful for testing your integration without affecting live data:

```php
$response = $ga4Service->setDebugMode(true)
    ->sendEvent($clientId, 'test_event', ['value' => 123]);

if ($response && $response->successful()) {
    $debugInfo = $response->json();
    // inspect validationMessages
}
```

When `setDebugMode(true)` is called:
1. The event is sent to the GA4 **debug validation endpoint**.
2. The `debug_mode: 1` parameter is automatically added to all events.
3. The `sendEvent` method returns the `Response` object containing GA4 validation messages.

### Connection Verification

You can verify your GA4 configuration using the built-in console command. This command sends a test event to the Google Analytics debug validation server and reports any issues:

```bash
php artisan ga4-marketing:verify-ga4
```