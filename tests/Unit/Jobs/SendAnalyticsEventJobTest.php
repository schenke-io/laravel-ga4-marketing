<?php

use SchenkeIo\LaravelGa4Marketing\Jobs\SendAnalyticsEventJob;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

test('it handles the job by calling AnalyticsService', function () {
    $analyticsService = Mockery::mock(AnalyticsService::class);

    $analyticsService->shouldReceive('sendEvent')
        ->once()
        ->with('client-123', 'test_event', ['param1' => 'value1'], 'user-456');

    $job = new SendAnalyticsEventJob('client-123', 'test_event', ['param1' => 'value1'], 'user-456');
    $job->handle($analyticsService);

    expect(true)->toBeTrue();
});
