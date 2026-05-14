<?php

namespace SchenkeIo\LaravelGa4Marketing\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

/**
 * Middleware to track outbound link clicks via the server-side.
 *
 * This middleware checks the response for redirects to external
 * domains and sends a click event to GA4.
 */
class TrackOutboundLink
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $targetUrl = $response->getTargetUrl();
            $host = parse_url($targetUrl, PHP_URL_HOST);

            if ($host && $host !== $request->getHost()) {
                $this->analyticsService->click(
                    $targetUrl,
                    null, // link_text
                    null, // link_id
                    null, // link_classes
                    $host,
                    true  // outbound
                );
            }
        }

        return $response;
    }
}
