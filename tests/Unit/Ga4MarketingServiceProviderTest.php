<?php

use SchenkeIo\LaravelGa4Marketing\Ga4MarketingServiceProvider;

test('it can get package root', function () {
    $root = Ga4MarketingServiceProvider::getPackageRoot();
    expect($root)->toBeString();
    expect($root)->toContain('laravel-ga4-marketing');
});
