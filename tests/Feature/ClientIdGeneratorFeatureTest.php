<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;

test('getClientId returns hashed user ID when config is enabled and user is logged in', function () {
    Config::set('ga4-marketing.ga4.client_from_user_id', true);

    $user = Mockery::mock('overload:User');
    $user->shouldReceive('getAuthIdentifier')->andReturn(123);
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(123);

    $generator = new ClientIdGenerator(null, 'test-salt');
    $clientId = $generator->getClientId();

    expect($clientId)->toBe(sha1('user-123test-salt'));
});

test('getClientId returns default hash when config is disabled even if user is logged in', function () {
    Config::set('ga4-marketing.ga4.client_from_user_id', false);

    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(123);

    $request = Request::create('/', 'GET');
    $request->headers->set('User-Agent', 'test-ua');

    $generator = new ClientIdGenerator($request, 'test-salt');
    $clientId = $generator->getClientId();

    expect($clientId)->not->toBe(sha1('user-123test-salt'));
    expect($clientId)->toBe(sha1('127.0.0.1test-uatest-salt'));
});

test('getClientId returns default hash when config is enabled but user is not logged in', function () {
    Config::set('ga4-marketing.ga4.client_from_user_id', true);

    Auth::shouldReceive('check')->andReturn(false);

    $request = Request::create('/', 'GET');
    $request->headers->set('User-Agent', 'test-ua');

    $generator = new ClientIdGenerator($request, 'test-salt');
    $clientId = $generator->getClientId();

    expect($clientId)->toBe(sha1('127.0.0.1test-uatest-salt'));
});
