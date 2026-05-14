# Laravel GA4 Marketing

![Coverage](https://img.shields.io/badge/coverage-100%25-green)
![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen)

This package provides a simple way to integrate Google Analytics 4 (GA4) event tracking into your Laravel application. It includes middleware for automatic page view tracking and a service to send custom events directly to GA4.

## Features

- **Cookie-based Tracking**: Persistent visitor identification using a `visitor` cookie (default 100 days).
- **Privacy-first Hashing**: Securely generate hashed User IDs for authenticated users, which automatically overwrite the anonymous visitor cookie.
- **Hybrid Event Handling**: Support for both immediate API calls and queued background jobs for event processing.
- **JS-Triggered Tracking**: Client-side triggers for events, reducing server-side overhead and improving accuracy.
- **Automatic Tracking**: Easy integration via Blade directives (`@Ga4MarketingScript` or `@Ga4MarketingConfig`) for automatic page view and engagement tracking.
- **Bot Filtering**: Built-in filtering to prevent common bots and crawlers from polluting your analytics.
- **Visibility Tracking**: Blade component to track when elements become visible using IntersectionObserver.
- **Non-blocking Execution**: Events are sent with a short timeout and exceptions are caught to ensure your application remains responsive.
