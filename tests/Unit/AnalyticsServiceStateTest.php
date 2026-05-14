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

test('it tracks page view state', function () {
    expect($this->service->wasPageViewTracked())->toBeFalse();

    $this->service->markPageViewAsTracked();

    expect($this->service->wasPageViewTracked())->toBeTrue();
});

test('pageView method marks as tracked', function () {
    $this->clientIdGenerator->shouldReceive('getClientId')->andReturn('client-123');
    $this->eventValidator->shouldReceive('validateName')->andReturn('page_view');
    $this->eventMapper->shouldReceive('mapArgumentsToParams')->andReturn([]);
    $this->sessionManager->shouldReceive('getSessionData')->andReturn([]);
    $this->payloadBuilder->shouldReceive('build')->andReturn([]);
    $this->http->shouldReceive('timeout->post')->andReturn(Mockery::mock(Response::class));

    expect($this->service->wasPageViewTracked())->toBeFalse();

    $this->service->pageView('https://example.com');

    expect($this->service->wasPageViewTracked())->toBeTrue();
});
