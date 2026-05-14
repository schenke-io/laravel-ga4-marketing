## Usage

This package supports three primary integration modes to suit different application architectures.

### 1. Standard Mode (Quick Setup)

Best for most applications. Simply add the `@Ga4MarketingScript` directive to your main layout file, typically before the closing `</body>` tag:

```html
@Ga4MarketingScript
```

This directive:
- Renders the necessary tracking scripts.
- Automatically handles CSRF tokens and event routing.
- Sends a `page_view` event on window load.
- Enables declarative tracking (clicks, visibility).
- Ensures scripts are only included once per page.

### 2. Advanced Mode (Vite & Bundling)

If you prefer to bundle the tracking logic with your application for better performance, you can import the tracker from the package resources and use the configuration directive.

**JavaScript Setup:**
Import the tracker in your `resources/js/app.js`:

```javascript
import '../../vendor/schenke-io/laravel-ga4-marketing/resources/js/ga4-tracker';
```

**Blade Setup:**
Add the `@Ga4MarketingConfig` directive in your layout. This provides the necessary configuration (route, CSRF token) to the bundled script:

```html
@Ga4MarketingConfig
```

The `@Ga4MarketingConfig` directive is smart: it automatically disables the client-side `page_view` event if the page view was already tracked on the server (e.g., via middleware), preventing double counting.

### 3. Server-Side Only Mode

For tracking without any client-side scripts, you can rely entirely on the `track-page-view` middleware and the `AnalyticsService`.

```php
Route::middleware(['track-page-view'])->group(function () {
    // your routes
});
```

### Automatic Page View Tracking

When using `@Ga4MarketingScript` or `@Ga4MarketingConfig`, a `page_view` event is automatically sent to GA4 on window load, unless:
1. It was already tracked on the server during the same request.
2. It is explicitly disabled via the `<body>` tag.

#### Disabling Automatic Page Views

If you want to disable automatic tracking for a specific page while still keeping other tracking features active, add `data-ga4-event="no-pageview"` (or the shorthand `data-ga4="no-pageview"`) to the `<body>` tag:

```html
<body data-ga4-event="no-pageview">
<!-- or -->
<body data-ga4="no-pageview">
```

### Visitor & User Identification

The package uses a `visitor` cookie (configurable) to maintain a persistent client ID for anonymous users. This cookie is set to last 100 days by default and is automatically extended on each request.

If a user is authenticated and `client_from_user_id` is enabled in the config, the package will:
1. Generate a hashed version of the User ID.
2. Use this hash as the GA4 `client_id`.
3. Overwrite the `visitor` cookie with this hashed ID.

This ensures that the same user is tracked consistently across different devices if they log in, while maintaining privacy by hashing the actual database ID.

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
    $clientId = $ga4Service->getClientId();

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

To send events from JavaScript, you can use the `window.ga4Event` helper:

```javascript
ga4Event('js_button_click', {
    source: 'home_banner'
});
```

### Visibility Tracking

#### Declarative Tracking

The easiest way to track visibility is by adding `data-ga4-event="scroll"` (or `data-ga4="scroll"`) to any element. You can specify the area name using `data-ga4-area`:

```html
<div data-ga4-event="scroll" data-ga4-area="pricing_table">
    <!-- content -->
</div>
```

This sends a `scroll` event with the `visible_area` parameter when the element becomes visible in the viewport.

### Click Tracking

You can automatically track clicks on any element using declarative data attributes. 

#### Outbound Link Tracking

To track clicks to external domains, use `data-ga4-event="outbound"` (or `data-ga4="outbound"`) on an `<a>` tag:

```html
<a href="https://example.com" data-ga4-event="outbound">External Link</a>
```

This automatically sets the event name to `click`, sets `outbound: true`, and extracts `link_url` and `link_domain`.

#### Custom Click Events

Use `data-ga4-event` (or `data-ga4`) for the event name and `data-ga4-params` for optional parameters (as a JSON string):

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