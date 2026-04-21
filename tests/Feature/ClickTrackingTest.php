<?php

use Illuminate\Support\Facades\Blade;

it('renders the scripts component with click tracking logic', function () {
    $view = Blade::render('<x-ga4-marketing::scripts />');

    expect($view)
        ->toContain('window.ga4Marketing')
        ->toContain('keepalive')
        ->toContain('document.addEventListener')
        ->toContain('data-ga4')
        ->toContain('data-ga4-params')
        ->toContain('link_url')
        ->toContain('link_text');
});
