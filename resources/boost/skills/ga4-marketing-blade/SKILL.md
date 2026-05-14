# ga4-marketing-blade

## Purpose
Guidelines for implementing front-end tracking using Blade components and JavaScript.

## When to Use
Use this skill when:
- Including the tracking script in Blade templates.
- Adding `data-ga4-event` attributes to HTML elements.
- Using the `window.ga4Marketing` or `window.ga4Event` JavaScript API.
- Integrating GA4 events with Livewire components.
- Configuring middleware for ad parameter capture.

## Blade Integration

### Include the Script
Add to your layout file (in `<head>` or before `</body>`):
```blade
@Ga4MarketingScript
```
Or as a component:
```blade
<x-ga4-marketing::ga4-marketing />
```

### Vite / Bundling Integration
If you bundle the tracker (e.g., in `app.js`), use the config-only directive:
```blade
@Ga4MarketingConfig
```
This skips rendering the `<script>` tag but provides the necessary initialization parameters. It automatically suppresses the client-side `page_view` if already tracked on the server.

On init, the tracker automatically fires a `page_view` event. To suppress it on a specific page:
```html
<body data-ga4="no-pageview">
<!-- or -->
<body data-ga4-event="no-pageview">
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
Use `data-ga4-event="outbound"` on `<a>` tags. The tracker fires a `click` event with `outbound: true`, `link_url`, and `link_domain` parameters. For all `<a>` tags, `link_text`, `link_id`, and `link_classes` are also captured automatically.
```html
<a href="https://external-site.com" data-ga4-event="outbound">Visit External</a>
```

#### Scroll / Visibility Tracking
Use `data-ga4="scroll"` to fire a `scroll` event when the element becomes visible (via `IntersectionObserver`). The `data-ga4-area` value (or the element `id`, or `"unknown"`) is sent as the `visible_area` parameter. The event fires once per element.
```html
<div data-ga4="scroll" data-ga4-area="pricing-section">...</div>
```

#### Automatic User Engagement
The tracker fires a `user_engagement` event with `engagement_time_msec` on `beforeunload`. No markup needed.

## JavaScript API

`window.ga4Event` is a convenience alias for `window.ga4Marketing.sendEvent()` — both send a POST to `/ga4-marketing/event`:
```javascript
// Shorthand alias
window.ga4Event('custom_event', { category: 'engagement' });
// Full API
window.ga4Marketing.sendEvent('purchase', { value: 99.99, currency: 'USD' });
```

## Middlewares

### Visitor Attribution
The `handle-visitor-cookie` middleware (pushed to the `web` group by default) manages a persistent `visitor` cookie for anonymous users.

### Client-Side Ad Attribution
The `capture-ad-parameters` middleware reads `gclid`, `wbraid`, and `gbraid` from the URL and stores them in the session for the duration of the visit.
```php
Route::middleware(['web', 'capture-ad-parameters'])->group(function () {
    // Your routes
});
```

### Server-Side Tracking (Alternatives)
Use these middlewares if you need to track events without relying on JavaScript:
- `track-page-view`: Tracks a `page_view` event on GET requests.
- `track-outbound-link`: Tracks a `click` event (with `outbound: true`) for external redirects.

## Livewire Integration
The tracker listens for `ga4-event` dispatched from Livewire components (Livewire 3+).

```php
public function someAction(): void
{
    $this->dispatch('ga4-event', 'file_download', ['file_name' => 'report.pdf']);
}
```

Dispatching with a named `name` key also works:
```php
$this->dispatch('ga4-event', name: 'file_download', params: ['file_name' => 'report.pdf']);
```