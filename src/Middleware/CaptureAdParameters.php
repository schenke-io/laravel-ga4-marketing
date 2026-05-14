<?php

namespace SchenkeIo\LaravelGa4Marketing\Middleware;

use Closure;
use Illuminate\Http\Request;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

/**
 * Middleware to capture Google Ad parameters (gclid, dclid, wbraid, gbraid)
 * from the request and store them in the session/cache.
 */
class CaptureAdParameters
{
    public function __construct(protected AnalyticsService $ga4Service) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        foreach (['gclid', 'wbraid', 'gbraid'] as $param) {
            if ($value = $request->query($param)) {
                // Sanitize the ID: allow alphanumeric, hyphens, and underscores
                $sanitizedValue = preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value);

                if ($sanitizedValue !== '' && is_string($sanitizedValue)) {
                    $clientId = $this->ga4Service->getClientId();
                    $this->ga4Service->storeAdId($clientId, $param, $sanitizedValue);
                }
            }
        }

        return $next($request);
    }
}
