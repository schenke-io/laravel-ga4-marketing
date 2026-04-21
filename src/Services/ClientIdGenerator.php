<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

use Illuminate\Http\Request;

class ClientIdGenerator
{
    public function __construct(
        protected ?Request $request = null,
        private string $salt = ''
    ) {}

    /**
     * Generate a unique client ID based on IP address and User Agent.
     */
    public function generate(?string $ipAddress, ?string $userAgent): string
    {
        return sha1(($ipAddress ?? '127.0.0.1').($userAgent ?? 'unknown').$this->salt);
    }

    /**
     * Get the client ID for the current request.
     */
    public function getClientId(): string
    {
        $request = $this->request ?: request();

        return $this->generate($request->ip(), $request->userAgent());
    }
}
