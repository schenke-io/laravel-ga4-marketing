<?php

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;
use SchenkeIo\LaravelGa4Marketing\Services\BotDetector;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;
use SchenkeIo\LaravelGa4Marketing\Services\EventMapper;
use SchenkeIo\LaravelGa4Marketing\Services\EventValidator;
use SchenkeIo\LaravelGa4Marketing\Services\PayloadBuilder;
use SchenkeIo\LaravelGa4Marketing\Services\SessionManager;

beforeEach(function () {
    $this->clientIdGenerator = Mockery::mock(ClientIdGenerator::class);
    $this->botDetector = Mockery::mock(BotDetector::class);
    $this->eventValidator = Mockery::mock(EventValidator::class);
    $this->eventMapper = Mockery::mock(EventMapper::class);
    $this->sessionManager = Mockery::mock(SessionManager::class);
    $this->payloadBuilder = Mockery::mock(PayloadBuilder::class);
    $this->http = Mockery::mock(HttpFactory::class);
    $this->rateLimiter = Mockery::mock(RateLimiter::class);
    $this->request = Mockery::mock(Request::class);
    $this->request->shouldReceive('ip')->andReturn('127.0.0.1');
    $this->request->shouldReceive('userAgent')->andReturn('test-ua');
    $this->handler = Mockery::mock(ExceptionHandler::class);
    $this->bus = Mockery::mock(Dispatcher::class);

    $this->service = new AnalyticsService(
        $this->clientIdGenerator,
        $this->botDetector,
        $this->eventValidator,
        $this->eventMapper,
        $this->sessionManager,
        $this->payloadBuilder,
        ['ga4' => [
            'measurement_id' => 'mid',
            'api_secret' => 'secret',
            'rate_limit' => ['enabled' => false],
        ]],
        $this->http,
        $this->rateLimiter,
        $this->request,
        $this->handler,
        $this->bus
    );
});

afterEach(function () {
    Mockery::close();
});

test('it can set user id', function () {
    expect($this->service->setUserId('user-123'))->toBe($this->service);
});

test('it can set debug mode', function () {
    expect($this->service->setDebugMode(true))->toBe($this->service);
});

test('it can generate client id', function () {
    $this->clientIdGenerator->shouldReceive('generate')->with('ip', 'ua')->andReturn('client-123');
    expect($this->service->generateClientId('ip', 'ua'))->toBe('client-123');
});

test('it can get client id', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');
    expect($this->service->getClientId())->toBe('client-123');
});

test('it can get session id', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');
    $this->sessionManager->shouldReceive('getSessionId')->with('client-123')->andReturn('session-456');
    expect($this->service->getSessionId())->toBe('session-456');
});

test('it can get engagement time', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');
    $this->sessionManager->shouldReceive('getEngagementTime')->with('client-123')->andReturn(1000);
    expect($this->service->getEngagementTime())->toBe(1000);
});

test('it can store ad id', function () {
    $this->sessionManager->shouldReceive('storeAdId')->with('client-123', 'gclid', 'value')->once();
    $this->service->storeAdId('client-123', 'gclid', 'value');
    expect(true)->toBeTrue();
});

test('it can check for bots', function () {
    $this->botDetector->shouldReceive('isBot')->with('Googlebot')->andReturn(true);
    expect($this->service->isBot('Googlebot'))->toBeTrue();
});

test('it can get bot blacklist', function () {
    $this->botDetector->shouldReceive('getBotBlacklist')->andReturn(['bot1']);
    expect($this->service->getBotBlacklist())->toBe(['bot1']);
});

test('it can send an event', function () {
    $this->eventValidator->shouldReceive('validateName')->with('test_event')->andReturn('test_event');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn(['payload']);

    $response = Mockery::mock(Response::class);
    $this->http->shouldReceive('timeout->post')->andReturn($response);

    $result = $this->service->sendEvent('client-123', 'test_event', ['p' => 'v']);
    expect($result)->toBe($response);
});

test('it uses debug endpoint and debug_mode=1 when debug mode is enabled', function () {
    $this->eventValidator->shouldReceive('validateName')->andReturn('test_event');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn(['payload']);

    $this->service->setDebugMode(true);

    $this->http->shouldReceive('timeout->post')
        ->with(Mockery::on(function ($url) {
            return str_contains($url, 'debug/mp/collect') && str_contains($url, 'measurement_id=mid');
        }), Mockery::any())
        ->once();

    $this->service->sendEvent('client-123', 'test_event', []);
});

test('it uses live endpoint but includes debug_mode=1 when config debug_mode is true', function () {
    $service = new AnalyticsService(
        $this->clientIdGenerator, $this->botDetector, $this->eventValidator,
        $this->eventMapper, $this->sessionManager, $this->payloadBuilder,
        ['ga4' => [
            'measurement_id' => 'mid',
            'api_secret' => 'secret',
            'debug_mode' => true,
        ]],
        $this->http,
        null, // rateLimiter
        $this->request
    );

    $this->eventValidator->shouldReceive('validateName')->andReturn('test_event');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);

    // Check if debug_mode is passed to payload builder
    $this->payloadBuilder->shouldReceive('build')
        ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::on(function ($params) {
            return ($params['debug_mode'] ?? null) === 1;
        }), Mockery::any(), Mockery::any())
        ->andReturn(['payload']);

    $this->http->shouldReceive('timeout->post')
        ->with(Mockery::on(function ($url) {
            return ! str_contains($url, 'debug/mp/collect') && str_contains($url, 'mp/collect');
        }), Mockery::any())
        ->once();

    $service->sendEvent('client-123', 'test_event', []);
});

test('it handles exceptions during sendEvent', function () {
    $this->eventValidator->shouldReceive('validateName')->andReturn('e');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn(['p']);

    $exception = new Exception('test error');
    $this->http->shouldReceive('timeout->post')->andThrow($exception);
    $this->handler->shouldReceive('report')->with($exception)->once();

    $this->service->sendEvent('c', 'e');
    expect(true)->toBeTrue();
});

test('it returns null if measurement id is missing', function () {
    $service = new AnalyticsService(
        $this->clientIdGenerator, $this->botDetector, $this->eventValidator,
        $this->eventMapper, $this->sessionManager, $this->payloadBuilder,
        ['ga4' => []]
    );
    expect($service->sendEvent('c', 'e'))->toBeNull();
});

test('it handles rate limiting', function () {
    $service = new AnalyticsService(
        $this->clientIdGenerator, $this->botDetector, $this->eventValidator,
        $this->eventMapper, $this->sessionManager, $this->payloadBuilder,
        ['ga4' => [
            'measurement_id' => 'mid',
            'api_secret' => 'secret',
            'rate_limit' => ['enabled' => true, 'max_attempts' => 1],
        ]],
        $this->http,
        $this->rateLimiter
    );

    $this->rateLimiter->shouldReceive('tooManyAttempts')->andReturn(true);

    expect($service->sendEvent('c', 'e'))->toBeNull();
});

test('it can handle dynamic calls', function () {
    $this->eventMapper->shouldReceive('mapArgumentsToParams')->andReturn(['p' => 'v']);
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');

    // We need to mock sendEvent or at least the things it calls
    $this->eventValidator->shouldReceive('validateName')->andReturn('pageView');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn(['payload']);
    $this->http->shouldReceive('timeout->post');

    $this->service->pageView('loc');
    expect(true)->toBeTrue();
});

test('it can check health', function () {
    expect($this->service->isHealthy())->toBeTrue();
});

test('it returns unhealthy if config missing', function () {
    $service = new AnalyticsService(
        $this->clientIdGenerator, $this->botDetector, $this->eventValidator,
        $this->eventMapper, $this->sessionManager, $this->payloadBuilder,
        ['ga4' => []]
    );
    expect($service->isHealthy())->toBeFalse();
});

test('it can process event from JS with api handling', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');

    // Mock sendEvent dependencies
    $this->eventValidator->shouldReceive('validateName')->andReturn('e');
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn(['p']);
    $this->http->shouldReceive('timeout->post');

    $this->service->processEventFromJs('e', []);
    expect(true)->toBeTrue();
});

test('it can process event from JS with job handling', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');

    // Create a service with 'job' handling
    $service = new AnalyticsService(
        $this->clientIdGenerator,
        $this->botDetector,
        $this->eventValidator,
        $this->eventMapper,
        $this->sessionManager,
        $this->payloadBuilder,
        ['ga4' => [
            'event_handling' => 'job',
        ]],
        null, // http
        null, // rateLimiter
        null, // request
        null, // handler
        $this->bus
    );

    $this->bus->shouldReceive('dispatch')->once();

    $service->processEventFromJs('e', []);
    expect(true)->toBeTrue();
});
