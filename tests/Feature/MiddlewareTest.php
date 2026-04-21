<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Config::set('ga4-marketing.ga4.measurement_id', 'G-12345');
    Config::set('ga4-marketing.ga4.api_secret', 'secret');

    Route::middleware(['track-page-view'])->get('/track-page', function () {
        return 'ok';
    });

    Route::middleware(['track-outbound-link'])->get('/redirect-external', function () {
        return redirect('https://external-site.com');
    });

    Route::middleware(['track-outbound-link'])->get('/redirect-internal', function () {
        return redirect('/internal-page');
    });
});

it('tracks page view via middleware', function () {
    Http::fake();

    $this->get('/track-page')
        ->assertStatus(200);

    Http::assertSent(function ($request) {
        $events = $request['events'];
        $found = false;
        foreach ($events as $event) {
            if ($event['name'] === 'page_view') {
                $found = true;
                break;
            }
        }

        return str_contains($request->url(), 'google-analytics.com') && $found;
    });
});

it('tracks outbound link via middleware', function () {
    Http::fake();

    $this->get('/redirect-external')
        ->assertRedirect('https://external-site.com');

    Http::assertSent(function ($request) {
        $events = $request['events'];
        $found = false;
        foreach ($events as $event) {
            if ($event['name'] === 'click' &&
                ($event['params']['outbound'] ?? false) === true &&
                ($event['params']['link_domain'] ?? '') === 'external-site.com') {
                $found = true;
                break;
            }
        }

        return str_contains($request->url(), 'google-analytics.com') && $found;
    });
});

it('does not track internal redirect via outbound middleware', function () {
    Http::fake();

    $this->get('/redirect-internal')
        ->assertRedirect('/internal-page');

    Http::assertNothingSent();
});
