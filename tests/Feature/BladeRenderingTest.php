<?php

use Illuminate\Support\Facades\Blade;

it('renders the scripts component with inlined JS', function () {
    $view = Blade::render('<x-ga4-marketing::ga4-marketing />');

    expect($view)->toContain('<script>')
        ->and($view)->toContain('ga4Marketing.init')
        ->and($view)->toContain('ga4-marketing/event');
});
