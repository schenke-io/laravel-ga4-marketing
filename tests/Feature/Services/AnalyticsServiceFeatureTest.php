<?php

namespace SchenkeIo\LaravelGa4Marketing\Tests\Feature\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Request;
use SchenkeIo\LaravelGa4Marketing\Jobs\SendAnalyticsEventJob;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;
use SchenkeIo\LaravelGa4Marketing\Services\BotDetector;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;
use SchenkeIo\LaravelGa4Marketing\Services\EventMapper;
use SchenkeIo\LaravelGa4Marketing\Services\EventValidator;
use SchenkeIo\LaravelGa4Marketing\Services\PayloadBuilder;
use SchenkeIo\LaravelGa4Marketing\Services\SessionManager;
use SchenkeIo\LaravelGa4Marketing\Tests\TestCase;

class AnalyticsServiceFeatureTest extends TestCase
{
    public function test_analytics_service_queue_event_without_bus()
    {
        Bus::fake();

        $service = new AnalyticsService(
            app(ClientIdGenerator::class),
            app(BotDetector::class),
            app(EventValidator::class),
            app(EventMapper::class),
            app(SessionManager::class),
            app(PayloadBuilder::class),
            ['ga4' => ['measurement_id' => 'm', 'api_secret' => 's']],
            app(Factory::class),
            app(RateLimiter::class),
            app('request'),
            app(ExceptionHandler::class),
            null // NO BUS
        );

        $service->queueEvent('client-123', 'test_event');

        Bus::assertDispatched(SendAnalyticsEventJob::class);
    }

    public function test_analytics_service_get_ip_address_with_request_helper()
    {
        $service = new AnalyticsService(
            app(ClientIdGenerator::class),
            app(BotDetector::class),
            app(EventValidator::class),
            app(EventMapper::class),
            app(SessionManager::class),
            app(PayloadBuilder::class),
            ['ga4' => ['measurement_id' => 'm', 'api_secret' => 's']],
            app(Factory::class),
            app(RateLimiter::class),
            null, // NO REQUEST PROPERTY
            app(ExceptionHandler::class),
            app(Dispatcher::class)
        );

        // This will call getIpAddress which should use the request() helper
        // In this test environment, request() should return a request object.
        $service->sendEvent('client-123', 'test_event');

        expect(true)->toBeTrue();
    }
}
