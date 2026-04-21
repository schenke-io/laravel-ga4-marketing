<?php

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use SchenkeIo\LaravelGa4Marketing\Services\SessionManager;

beforeEach(function () {
    $this->cache = Mockery::mock(CacheRepository::class);
    $this->config = Mockery::mock(ConfigRepository::class);
    $this->manager = new SessionManager($this->cache, $this->config);
});

afterEach(function () {
    Mockery::close();
});

test('it creates a new session if none exists', function () {
    $clientId = 'client-123';

    $this->cache->shouldReceive('has')->with("ga_last_activity_$clientId")->andReturn(false);
    $this->cache->shouldReceive('get')->with("ga_last_activity_$clientId", [])->andReturn([]);
    $this->config->shouldReceive('get')->with('ga4-marketing.ga4.session_lifetime', 1800)->andReturn(1800);
    $this->cache->shouldReceive('put')->once();

    $data = $this->manager->getSessionData($clientId);

    expect($data['is_new_session'])->toBeTrue();
    expect($data['is_new_user'])->toBeTrue();
    expect($data['session_id'])->not->toBeEmpty();
});

test('it reuses existing session if within lifetime', function () {
    $clientId = 'client-123';
    $now = time();

    $this->cache->shouldReceive('has')->andReturn(true);
    $this->cache->shouldReceive('get')->andReturn([
        'session_id' => 'existing-session',
        'last_active' => $now - 100,
    ]);
    $this->config->shouldReceive('get')->andReturn(1800);
    $this->cache->shouldReceive('put')->once();

    $data = $this->manager->getSessionData($clientId);

    expect($data['is_new_session'])->toBeFalse();
    expect($data['session_id'])->toBe('existing-session');
    expect($data['engagement_time_msec'])->toBeGreaterThanOrEqual(100000);
});

test('it creates new session if existing expired', function () {
    $clientId = 'client-123';
    $now = time();

    $this->cache->shouldReceive('has')->andReturn(true);
    $this->cache->shouldReceive('get')->andReturn([
        'session_id' => 'old-session',
        'last_active' => $now - 2000,
    ]);
    $this->config->shouldReceive('get')->andReturn(1800);
    $this->cache->shouldReceive('put')->once();

    $data = $this->manager->getSessionData($clientId);

    expect($data['is_new_session'])->toBeTrue();
    expect($data['session_id'])->not->toBe('old-session');
});

test('it can store and retrieve ad id', function () {
    $clientId = 'client-123';

    $this->cache->shouldReceive('get')->andReturn([]);
    $this->config->shouldReceive('get')->andReturn(1800);
    $this->cache->shouldReceive('put')->with(
        "ga_last_activity_$clientId",
        Mockery::subset(['google_ad_id' => ['type' => 'gclid', 'value' => 'val']]),
        1800
    )->once();

    $this->manager->storeAdId($clientId, 'gclid', 'val');
    expect(true)->toBeTrue();
});

test('it can get session id directly', function () {
    $this->cache->shouldReceive('has')->andReturn(true);
    $this->cache->shouldReceive('get')->andReturn(['session_id' => 'abc', 'last_active' => time()]);
    $this->config->shouldReceive('get')->andReturn(1800);
    $this->cache->shouldReceive('put');

    expect($this->manager->getSessionId('c'))->toBe('abc');
});

test('it can get engagement time directly', function () {
    $now = time();
    $this->cache->shouldReceive('get')->andReturn(['last_active' => $now - 5]);

    expect($this->manager->getEngagementTime('c'))->toBeGreaterThanOrEqual(5000);
});

test('it returns 0 engagement time if no activity', function () {
    $this->cache->shouldReceive('get')->andReturn([]);

    expect($this->manager->getEngagementTime('c'))->toBe(0);
});
