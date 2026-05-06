<?php

namespace SchenkeIo\LaravelGa4Marketing\Middleware;

use Closure;
use Illuminate\Http\Request;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;
use Symfony\Component\HttpFoundation\Response;

class HandleVisitorCookie
{
    public function __construct(protected ClientIdGenerator $clientIdGenerator) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! method_exists($response, 'cookie')) {
            return $response;
        }

        $clientId = $this->clientIdGenerator->getClientId();
        $cookieName = config('ga4-marketing.ga4.cookie_name', 'visitor');
        $cookieLifetime = config('ga4-marketing.ga4.cookie_lifetime', 144000);

        $response->cookie(
            $cookieName,
            $clientId,
            $cookieLifetime
        );

        return $response;
    }
}
