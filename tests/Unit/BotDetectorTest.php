<?php

use SchenkeIo\LaravelGa4Marketing\Services\BotDetector;

test('it returns the merged and deduplicated bot blacklist', function () {
    $detector = new BotDetector(['Googlebot', 'CustomBot']);
    $bots = $detector->getBotBlacklist();

    expect($bots)->toContain('Googlebot')
        ->and($bots)->toContain('CustomBot');

    $counts = array_count_values($bots);
    expect($counts['Googlebot'])->toBe(1);
});

test('it detects bots', function () {
    $detector = new BotDetector;
    expect($detector->isBot('Googlebot/2.1'))->toBeTrue()
        ->and($detector->isBot('Mozilla/5.0 (Windows NT 10.0; Win64; x64)'))->toBeFalse()
        ->and($detector->isBot(''))->toBeFalse();
});
