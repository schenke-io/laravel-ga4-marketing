<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

use Illuminate\Http\Request;

class ClientIdGenerator
{
    protected ?string $cachedClientId = null;

    public function __construct(
        protected ?Request $request = null,
        private readonly string $salt = ''
    ) {}

    /**
     * Generate a unique client ID in the form [random].[timestamp]
     */
    public function generate(): string
    {
        return mt_rand(1000000000, mt_getrandmax()).'.'.time();
    }

    /**
     * Get the client ID for the current request.
     */
    public function getClientId(): string
    {
        if ($this->cachedClientId) {
            return $this->cachedClientId;
        }

        if (config('ga4-marketing.ga4.client_from_user_id') && auth()->check()) {
            return $this->cachedClientId = $this->getHashedUserId();
        }

        $request = $this->request ?: request();
        $cookieName = config('ga4-marketing.ga4.cookie_name', 'visitor');

        $cookieValue = $request->cookie($cookieName);

        return $this->cachedClientId = (is_string($cookieValue) ? $cookieValue : null) ?: $this->generate();
    }

    public function getHashedUserId(): string
    {
        return sha1('user-'.auth()->id().$this->salt);
    }
}
