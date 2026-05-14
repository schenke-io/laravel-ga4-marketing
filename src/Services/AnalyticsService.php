<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use SchenkeIo\LaravelGa4Marketing\Jobs\SendAnalyticsEventJob;

/**
 * Service for interacting with Google Analytics 4 (GA4) Measurement Protocol.
 *
 * This service handles event validation, mapping, session management, and
 * payload construction for GA4 events. It supports both immediate API
 * calls and queued background jobs for event processing.
 *
 * @method void click(string $linkUrl, ?string $linkText = null, ?string $linkId = null, ?string $linkClasses = null, ?string $linkDomain = null, ?bool $outbound = null) Track element clicks
 * @method void login(string $method) Track user login
 * @method void signUp(string $method) Track user sign-up
 * @method void share(string $method, string $contentType, string $itemId) Track content sharing
 * @method void search(string $searchTerm) Track site searches
 * @method void viewItem(array<int, mixed> $items, ?string $currency = null, ?float $value = null) Track item views
 * @method void addToCart(array<int, mixed> $items, ?string $currency = null, ?float $value = null) Track adding items to cart
 * @method void beginCheckout(array<int, mixed> $items, ?string $currency = null, ?float $value = null, ?string $coupon = null) Track start of checkout
 * @method void purchase(string $transactionId, array<int, mixed> $items, float $value, ?string $currency = null, ?float $tax = null, ?float $shipping = null, ?string $coupon = null) Track successful purchase
 * @method void scroll(int $percentScrolled) Track page scrolling
 * @method void fileDownload(string $fileName, string $extension, string $linkUrl, ?string $linkText = null, ?string $linkId = null, ?string $linkClasses = null, ?string $linkDomain = null) Track file downloads
 * @method void calculatorUsed(array<string, mixed> $params) Track calculator usage
 */
class AnalyticsService
{
    protected ?string $userId = null;

    protected bool $debugMode = false;

    private bool $pageViewTracked = false;

    /**
     * Create a new AnalyticsService instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected ClientIdGenerator $clientIdGenerator,
        protected BotDetector $botDetector,
        protected EventValidator $eventValidator,
        protected EventMapper $eventMapper,
        protected SessionManager $sessionManager,
        protected PayloadBuilder $payloadBuilder,
        protected array $config = [],
        protected ?Factory $http = null,
        protected ?RateLimiter $rateLimiter = null,
        protected ?Request $request = null,
        protected ?ExceptionHandler $handler = null,
        protected ?Dispatcher $bus = null
    ) {}

    /**
     * Set the user ID for the current session.
     */
    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Enable or disable debug mode for this instance.
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    /**
     * Mark the current request as having a page view tracked.
     */
    public function markPageViewAsTracked(): void
    {
        $this->pageViewTracked = true;
    }

    /**
     * Check if a page view has already been tracked in the current request.
     */
    public function wasPageViewTracked(): bool
    {
        return $this->pageViewTracked;
    }

    /**
     * Send a page_view event to GA4.
     */
    public function pageView(string $pageLocation, ?string $pageTitle = null, ?string $pageReferrer = null, ?string $language = null): void
    {
        $this->markPageViewAsTracked();
        $params = $this->eventMapper->mapArgumentsToParams('pageView', [$pageLocation, $pageTitle, $pageReferrer, $language]);
        $this->sendEvent($this->getClientId(), 'page_view', $params);
    }

    /**
     * Send an event to GA4 immediately.
     *
     * @param  string  $clientId  The unique ID for the client
     * @param  string  $eventName  The name of the event
     * @param  array<string, mixed>  $eventParams  Additional parameters for the event
     * @param  string|null  $userId  Optional user ID
     */
    public function sendEvent(string $clientId, string $eventName, array $eventParams = [], ?string $userId = null): ?Response
    {
        $measurementId = data_get($this->config, 'ga4.measurement_id');
        $apiSecret = data_get($this->config, 'ga4.api_secret');

        if (! $measurementId || ! $apiSecret) {
            return null;
        }

        if (data_get($this->config, 'ga4.rate_limit.enabled', true)) {
            $maxAttempts = data_get($this->config, 'ga4.rate_limit.max_attempts', 30);
            $decaySeconds = data_get($this->config, 'ga4.rate_limit.decay_seconds', 60);

            if ($this->rateLimiter && $this->rateLimiter->tooManyAttempts('ga4-marketing-event:'.$clientId, $maxAttempts)) {
                return null;
            }

            $this->rateLimiter?->hit('ga4-marketing-event:'.$clientId, $decaySeconds);
        }

        $eventName = $this->eventValidator->validateName($eventName);

        $baseUrl = 'https://www.google-analytics.com/mp/collect';

        if ($this->debugMode || data_get($this->config, 'ga4.debug_mode')) {
            $eventParams['debug_mode'] = 1;
            if ($this->debugMode) {
                $baseUrl = 'https://www.google-analytics.com/debug/mp/collect';
            }
        }

        $sessionData = $this->sessionManager->getSessionData($clientId);
        $payload = $this->payloadBuilder->build(
            $clientId,
            $userId ?? $this->userId,
            $eventName,
            $eventParams,
            $sessionData,
            $this->getIpAddress()
        );

        try {
            return $this->http?->timeout(2)->post("{$baseUrl}?measurement_id={$measurementId}&api_secret={$apiSecret}", $payload);
        } catch (\Throwable $e) {
            $this->handler?->report($e);

            return null;
        }
    }

    protected function getIpAddress(): ?string
    {
        return $this->request?->ip();
    }

    /**
     * Queue an event for asynchronous sending to GA4.
     *
     * @param  array<string, mixed>  $eventParams
     */
    public function queueEvent(string $clientId, string $eventName, array $eventParams = [], ?string $userId = null): void
    {
        if ($this->bus) {
            $this->bus->dispatch(new SendAnalyticsEventJob($clientId, $eventName, $eventParams, $userId ?? $this->userId));
        } else {
            SendAnalyticsEventJob::dispatch($clientId, $eventName, $eventParams, $userId ?? $this->userId);
        }
    }

    /**
     * Handle dynamic calls to event helper methods.
     *
     * @param  array<int, mixed>  $arguments
     *
     * @phpstan-ignore-next-line
     */
    public function __call(string $name, array $arguments)
    {
        $params = $this->eventMapper->mapArgumentsToParams($name, $arguments);
        $this->sendEvent($this->getClientId(), $name, $params);
    }

    /**
     * Generate a unique client ID.
     */
    public function generateClientId(): string
    {
        return $this->clientIdGenerator->generate();
    }

    /**
     * Get the client ID for the current request.
     */
    public function getClientId(): string
    {
        return $this->clientIdGenerator->getClientId();
    }

    /**
     * Get or generate a session ID for GA4.
     */
    public function getSessionId(): string
    {
        return $this->sessionManager->getSessionId($this->getClientId());
    }

    /**
     * Get the engagement time since the last event in milliseconds.
     */
    public function getEngagementTime(): int
    {
        return $this->sessionManager->getEngagementTime($this->getClientId());
    }

    /**
     * Store Google Ad ID in cache for the given client.
     */
    public function storeAdId(string $clientId, string $type, string $value): void
    {
        $this->sessionManager->storeAdId($clientId, $type, $value);
    }

    /**
     * Process an event received from the JS trigger.
     *
     * @param  array<string, mixed>  $eventParams
     */
    public function processEventFromJs(string $eventName, array $eventParams = []): void
    {
        $clientId = $this->getClientId();
        $handling = data_get($this->config, 'ga4.event_handling', 'api');

        if ($handling === 'job') {
            $this->queueEvent($clientId, $eventName, $eventParams);
        } else {
            $this->sendEvent($clientId, $eventName, $eventParams);
        }
    }

    /**
     * Check if the configuration is healthy.
     */
    public function isHealthy(): bool
    {
        return ! empty(data_get($this->config, 'ga4.measurement_id')) &&
               ! empty(data_get($this->config, 'ga4.api_secret'));
    }

    /**
     * Check if the given User-Agent belongs to a bot.
     */
    public function isBot(string $userAgent): bool
    {
        return $this->botDetector->isBot($userAgent);
    }

    /**
     * Get the list of bot User Agents to ignore.
     *
     * @return array<int, string>
     */
    public function getBotBlacklist(): array
    {
        return $this->botDetector->getBotBlacklist();
    }
}
