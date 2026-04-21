<?php

arch('globals')
    ->expect(['dd', 'dump', 'ray', 'exit', 'die'])
    ->not->toBeUsed();

arch('LaravelGa4Marketing')
    ->expect('SchenkeIo\LaravelGa4Marketing')
    ->toOnlyUse([
        'Illuminate',
        'Spatie\LaravelPackageTools',
        'SchenkeIo\LaravelGa4Marketing',
        'Livewire',
        'report',
        'config',
        'app',
        'request',
        'sha1',
    ])
    ->ignoring('SchenkeIo\LaravelGa4Marketing\Workbench');
