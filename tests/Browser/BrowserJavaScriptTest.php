<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $filePath = __DIR__.'/../../.junie/tmp/browser-test-events.json';
    if (File::exists($filePath)) {
        File::delete($filePath);
    }
});

function getRecordedEvents(): array
{
    $filePath = __DIR__.'/../../.junie/tmp/browser-test-events.json';
    if (File::exists($filePath)) {
        return json_decode(File::get($filePath), true) ?: [];
    }

    return [];
}

it('can hit the listener directly', function () {
    visit('/browser-test/events')
        ->assertSee('[]');
});

it('reports a page view event', function () {
    visit('/browser-test/page-view')
        ->assertSee('Page View Test');

    // wait for JS to fire and the request to complete
    sleep(2);

    $events = getRecordedEvents();
    expect($events)->not->toBeEmpty();
    expect($events[0]['event_name'])->toBe('page_view');
});

it('do not report a pageview event since it is prevented in body tag', function () {
    visit('/browser-test/prevented')
        ->assertSee('Prevented Page View Test');

    sleep(1);

    $events = getRecordedEvents();
    expect($events)->toBeEmpty();
});

it('reports an outbound click when an a-tag is clicked', function () {
    visit('/browser-test/outbound-click')
        ->assertSee('Outbound Click Test')
        ->click('#outbound-link');

    // wait for events
    sleep(2);

    $events = getRecordedEvents();
    // might contain 'click' and 'user_engagement'
    $clickEvent = collect($events)->firstWhere('event_name', 'click');

    expect($clickEvent)->not->toBeNull();
    expect($clickEvent['event_params']['outbound'])->toBeTrue();
    expect($clickEvent['event_params']['link_url'])->toContain('/browser-test/page-view');
});

it('scrolls down a long page and report 80%', function () {
    $page = visit('/browser-test/scroll')
        ->assertSee('Scroll Test');

    // scroll to the target
    $page->script('document.getElementById("scroll-target").scrollIntoView()');

    // wait for the observer to fire and the request to complete
    sleep(2);

    $events = getRecordedEvents();
    $scrollEvent = collect($events)->firstWhere('event_name', 'scroll');

    expect($scrollEvent)->not->toBeNull();
    expect($scrollEvent['event_params']['visible_area'])->toBe('80%');
});

it('reports a JS-triggered event', function () {
    visit('/')
        ->press('Send JS Event');

    sleep(2);

    $events = getRecordedEvents();
    $jsEvent = collect($events)->firstWhere('event_name', 'js_button_click');

    expect($jsEvent)->not->toBeNull();
    expect($jsEvent['event_params']['source'])->toBe('inline_js');
});

it('reports a Livewire-triggered event', function () {
    visit('/')
        ->click('#send-event-button')
        ->waitForText('Event Sent!');

    $events = getRecordedEvents();
    $lwEvent = collect($events)->firstWhere('event_name', 'livewire_test_event');

    expect($lwEvent)->not->toBeNull();
});
