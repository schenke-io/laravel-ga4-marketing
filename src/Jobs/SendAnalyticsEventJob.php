<?php

namespace SchenkeIo\LaravelGa4Marketing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

/**
 * Job to send analytics events asynchronously.
 *
 * This job handles the background processing of GA4 events,
 * allowing the application to respond quickly to the user
 * while the event is sent in the background.
 */
class SendAnalyticsEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $eventParams
     */
    public function __construct(
        public string $clientId,
        public string $eventName,
        public array $eventParams = [],
        public ?string $userId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnalyticsService $analyticsService): void
    {
        $analyticsService->sendEvent($this->clientId, $this->eventName, $this->eventParams, $this->userId);
    }
}
