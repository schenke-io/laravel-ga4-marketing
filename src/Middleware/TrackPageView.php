<?php

namespace SchenkeIo\LaravelGa4Marketing\Middleware;

use Closure;
use Illuminate\Http\Request;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

class TrackPageView
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->isMethod('GET') && ! $request->ajax()) {
            $this->analyticsService->pageView(
                $request->fullUrl(),
                null,
                $request->header('referer'),
                $request->getPreferredLanguage()
            );
        }

        return $next($request);
    }
}
