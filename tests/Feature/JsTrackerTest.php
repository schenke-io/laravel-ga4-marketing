<?php

test('ga4-tracker.js does not contain legacy attributes', function () {
    $jsPath = __DIR__.'/../../resources/js/ga4-tracker.js';
    $content = file_get_contents($jsPath);

    expect($content)->not->toContain('data-gmarketing');
});

test('ga4-tracker.js contains expected tracking logic', function () {
    $jsPath = __DIR__.'/../../resources/js/ga4-tracker.js';
    $content = file_get_contents($jsPath);

    expect($content)->toContain('data-ga4-event');
    expect($content)->toContain('window.ga4Marketing = {');
    expect($content)->toContain('sendEvent: function(eventName, eventParams = {})');
    expect($content)->toContain('page_view');
    expect($content)->toContain('user_engagement');
    expect($content)->toContain('IntersectionObserver');
});
