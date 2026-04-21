<?php

use Illuminate\Support\Facades\Blade;

it('renders the scripts component with inlined JS', function () {
    $view = Blade::render('<x-ga4-marketing::scripts />');

    expect($view)->toContain('<script>')
        ->and($view)->toContain('window.ga4Marketing.init')
        ->and($view)->toContain('ga4-marketing/event');
});
