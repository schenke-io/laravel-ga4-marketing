<?php

namespace Tests\Unit;

use ReflectionClass;
use SchenkeIo\LaravelGa4Marketing\Services\PayloadBuilder;

test('it handles non-ip addresses in anonymizeIp', function () {
    $builder = new PayloadBuilder;

    // Use reflection to access private method
    $reflection = new ReflectionClass(PayloadBuilder::class);
    $method = $reflection->getMethod('anonymizeIp');
    $method->setAccessible(true);

    $result = $method->invoke($builder, 'not-an-ip');
    expect($result)->toBe('not-an-ip');
});

test('it anonymizes IPv4', function () {
    $builder = new PayloadBuilder;
    $reflection = new ReflectionClass(PayloadBuilder::class);
    $method = $reflection->getMethod('anonymizeIp');
    $method->setAccessible(true);

    expect($method->invoke($builder, '192.168.1.123'))->toBe('192.168.1.0');
});

test('it anonymizes IPv6', function () {
    $builder = new PayloadBuilder;
    $reflection = new ReflectionClass(PayloadBuilder::class);
    $method = $reflection->getMethod('anonymizeIp');
    $method->setAccessible(true);

    expect($method->invoke($builder, '2001:db8:85a3:0:0:8a2e:370:7334'))->toBe('2001:db8:85a3:0:0:8a2e:370:0');
});

test('it builds full payload', function () {
    $builder = new PayloadBuilder;
    $sessionData = [
        'session_id' => 's123',
        'engagement_time_msec' => 500,
        'is_new_session' => true,
        'is_new_user' => true,
        'google_ad_id' => ['type' => 'gclid', 'value' => 'v123'],
    ];

    $payload = $builder->build('c123', 'u123', 'test_event', ['p' => 'v'], $sessionData, '1.2.3.4');

    expect($payload['client_id'])->toBe('c123');
    expect($payload['user_id'])->toBe('u123');
    expect($payload['ip_override'])->toBe('1.2.3.0');
    expect($payload['events'])->toHaveCount(3); // first_visit, session_start, test_event
    expect($payload['events'][0]['name'])->toBe('first_visit');
    expect($payload['events'][1]['name'])->toBe('session_start');
    expect($payload['events'][2]['name'])->toBe('test_event');
    expect($payload['events'][2]['params']['gclid'])->toBe('v123');
});

test('it includes debug_mode in first_visit and session_start', function () {
    $builder = new PayloadBuilder;
    $sessionData = [
        'session_id' => 's123',
        'engagement_time_msec' => 500,
        'is_new_session' => true,
        'is_new_user' => true,
        'google_ad_id' => null,
    ];

    $payload = $builder->build('c123', null, 'test_event', ['debug_mode' => 1], $sessionData);

    expect($payload['events'][0]['params']['debug_mode'])->toBe(1);
    expect($payload['events'][1]['params']['debug_mode'])->toBe(1);
});
