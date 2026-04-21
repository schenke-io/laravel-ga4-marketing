<?php

use Illuminate\Support\Facades\Blade;

it('renders the tracking scripts component', function () {
    $view = Blade::render('<x-ga4-marketing::ga4-marketing />');

    expect($view)->toContain('window.ga4Marketing');
});

it('warns when component is included more than once', function () {
    $view = Blade::render('
        <x-ga4-marketing::ga4-marketing />
        <x-ga4-marketing::ga4-marketing />
    ');

    expect($view)->toContain('window.ga4Marketing')
        ->and($view)->toContain('console.warn(\'ga4-marketing component included more than once.\')');
});
