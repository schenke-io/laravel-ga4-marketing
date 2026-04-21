<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use SchenkeIo\LaravelGa4Marketing\Http\Controllers\EventController;

it('proxies events to GA4 via the controller', function () {
    // Ensure we hit the package controller, not the workbench override
    Route::post('ga4-marketing/event', [EventController::class, 'store'])
        ->middleware('web')
        ->name('ga4-marketing.event');

    Http::fake();
    config(['ga4-marketing.ga4.measurement_id' => 'G-12345']);
    config(['ga4-marketing.ga4.api_secret' => 'secret']);

    $this->post(route('ga4-marketing.event'), [
        'event_name' => 'js_event',
        'event_params' => ['foo' => 'bar'],
    ])->assertOk();

    Http::assertSent(function ($request) {
        $event = collect($request['events'])->firstWhere('name', 'js_event');

        return $event['name'] === 'js_event' &&
               $event['params']['foo'] === 'bar';
    });
});

it('validates event data', function () {
    // Ensure we hit the package controller, not the workbench override
    Route::post('ga4-marketing/event', [EventController::class, 'store'])
        ->middleware('web')
        ->name('ga4-marketing.event');

    $response = $this->from('/some-page')->post(route('ga4-marketing.event'), [
        // missing event_name
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/some-page');
});
