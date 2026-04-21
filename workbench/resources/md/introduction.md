# Laravel GA4 Marketing

![Coverage](https://img.shields.io/badge/coverage-100%25-green)
![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen)

This package provides a simple way to integrate Google Analytics 4 (GA4) event tracking into your Laravel application. It includes middleware for automatic page view tracking and a service to send custom events directly to GA4.

## Features

- **Cookie-less Tracking**: Privacy-first tracking without relying on client-side cookies.
- **Privacy-first Hashing**: Securely generate client IDs using privacy-focused hashing techniques.
- **Hybrid Event Handling**: Support for both immediate API calls and queued background jobs for event processing.
- **JS-Triggered Tracking**: Client-side triggers for events, reducing server-side overhead and improving accuracy.
- **Page View Component**: Simple Blade component to manually track page views including page location, title, and visitor language.
- **Bot Filtering**: Built-in filtering to prevent common bots and crawlers from polluting your analytics.
- **Visibility Tracking**: Blade component to track when elements become visible using IntersectionObserver.
- **Non-blocking Execution**: Events are sent with a short timeout and exceptions are caught to ensure your application remains responsive.
