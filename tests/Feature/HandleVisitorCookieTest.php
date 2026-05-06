<?php

namespace SchenkeIo\LaravelGa4Marketing\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use SchenkeIo\LaravelGa4Marketing\Middleware\HandleVisitorCookie;
use Symfony\Component\HttpFoundation\Response;

test('middleware sets the visitor cookie on response', function () {
    Route::get('/test-cookie', function () {
        return 'ok';
    })->middleware(HandleVisitorCookie::class);

    $response = $this->get('/test-cookie');

    $response->assertCookie('visitor', null, false);
    $cookieValue = null;
    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getName() === 'visitor') {
            $cookieValue = $cookie->getValue();
        }
    }
    expect($cookieValue)->toMatch('/^\d+\.\d+$/');
});

test('middleware prolongs the visitor cookie', function () {
    Route::get('/test-cookie', function () {
        return 'ok';
    })->middleware(HandleVisitorCookie::class);

    $response = $this->withUnencryptedCookie('visitor', 'existing-id')
        ->get('/test-cookie');

    $response->assertCookie('visitor', 'existing-id', false);
});

test('middleware overwrites cookie with hashed user ID when logged in', function () {
    Config::set('ga4-marketing.ga4.client_from_user_id', true);
    Config::set('ga4-marketing.ga4.client_id_salt', 'test-salt');

    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(123);

    Route::get('/test-cookie-auth', function () {
        return 'ok';
    })->middleware(HandleVisitorCookie::class);

    $response = $this->get('/test-cookie-auth');

    $expectedId = sha1('user-123test-salt');
    $response->assertCookie('visitor', $expectedId, false);
});

test('middleware handles response without cookie method', function () {
    Route::get('/no-cookie-method', function () {
        return new Response('ok');
    })->middleware(HandleVisitorCookie::class);

    $response = $this->get('/no-cookie-method');

    $response->assertStatus(200);
    $response->assertCookieMissing('visitor');
});
