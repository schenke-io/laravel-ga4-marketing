<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;

test('getClientId returns hashed user ID when config is enabled and user is logged in', function () {
    Config::set('ga4-marketing.ga4.client_from_user_id', true);

    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(123);

    $generator = new ClientIdGenerator(null, 'test-salt');
    $clientId = $generator->getClientId();

    expect($clientId)->toBe(sha1('user-123test-salt'));
});

test('getClientId returns cookie value when present', function () {
    Config::set('ga4-marketing.ga4.cookie_name', 'visitor');

    $request = Request::create('/', 'GET', [], ['visitor' => 'test-cookie-value']);

    $generator = new ClientIdGenerator($request);
    $clientId = $generator->getClientId();

    expect($clientId)->toBe('test-cookie-value');
});

test('getClientId returns new ID when no cookie and not logged in', function () {
    Auth::shouldReceive('check')->andReturn(false);

    $request = Request::create('/', 'GET');

    $generator = new ClientIdGenerator($request);
    $clientId = $generator->getClientId();

    expect($clientId)->toMatch('/^\d+\.\d+$/');
});
