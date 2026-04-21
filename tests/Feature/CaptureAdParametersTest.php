<?php

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

beforeEach(function () {
    Config::set('ga4-marketing.ga4.measurement_id', 'G-12345');
    Config::set('ga4-marketing.ga4.api_secret', 'secret');

    Route::middleware([StartSession::class, 'capture-ad-parameters'])->get('/test-page', function () {
        return 'ok';
    });
});

it('captures google ad id from url and stores in cache', function () {
    Http::fake();

    $this->get('/test-page?gclid=123')
        ->assertStatus(200);

    $ga4Service = app(AnalyticsService::class);
    $clientId = $ga4Service->getClientId();
    $cached = Cache::get("ga_last_activity_{$clientId}");

    expect($cached['google_ad_id'])->toBe(['type' => 'gclid', 'value' => '123']);
});

it('sanitizes google ad id', function () {
    Http::fake();

    $this->get('/test-page?gclid=abc-123_XYZ!@#')
        ->assertStatus(200);

    $ga4Service = app(AnalyticsService::class);
    $clientId = $ga4Service->getClientId();
    $cached = Cache::get("ga_last_activity_{$clientId}");

    expect($cached['google_ad_id'])->toBe(['type' => 'gclid', 'value' => 'abc-123_XYZ']);
});
