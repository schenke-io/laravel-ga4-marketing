<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class SessionManager
{
    public function __construct(
        protected CacheRepository $cache,
        protected ConfigRepository $config
    ) {}

    /**
     * Get or create session data for a given client.
     *
     * @return array{session_id: string, engagement_time_msec: int, is_new_session: bool, is_new_user: bool, google_ad_id: ?array{type: string, value: string}}
     */
    public function getSessionData(string $clientId): array
    {
        $cacheKey = $this->getCacheKey($clientId);
        $isNewUser = ! $this->cache->has($cacheKey);
        /** @var array{session_id?: string, last_active?: int, google_ad_id?: array{type: string, value: string}} $sessionData */
        $sessionData = $this->cache->get($cacheKey, []);
        $now = time();
        $lifetime = (int) $this->config->get('ga4-marketing.ga4.session_lifetime', 1800);
        $isNewSession = false;

        if (! isset($sessionData['session_id']) || ($now - ($sessionData['last_active'] ?? 0) > $lifetime)) {
            $sessionId = (string) rand(1000000000, 9999999999);
            $sessionData['session_id'] = $sessionId;
            $isNewSession = true;
            $engagementTime = 0;
        } else {
            $sessionId = $sessionData['session_id'];
            $engagementTime = ($now - ($sessionData['last_active'] ?? $now)) * 1000;
        }

        $sessionData['last_active'] = $now;
        $this->cache->put($cacheKey, $sessionData, $lifetime);

        return [
            'session_id' => $sessionId,
            'engagement_time_msec' => max(0, $engagementTime),
            'is_new_session' => $isNewSession,
            'is_new_user' => $isNewUser,
            'google_ad_id' => $sessionData['google_ad_id'] ?? null,
        ];
    }

    /**
     * Store Google Ad ID in cache for the given client.
     */
    public function storeAdId(string $clientId, string $type, string $value): void
    {
        $cacheKey = $this->getCacheKey($clientId);
        /** @var array{session_id?: string, last_active?: int, google_ad_id?: array{type: string, value: string}} $sessionData */
        $sessionData = $this->cache->get($cacheKey, []);
        $sessionData['google_ad_id'] = [
            'type' => $type,
            'value' => $value,
        ];
        $lifetime = (int) $this->config->get('ga4-marketing.ga4.session_lifetime', 1800);
        $this->cache->put($cacheKey, $sessionData, $lifetime);
    }

    /**
     * Get the current session ID for a client.
     */
    public function getSessionId(string $clientId): string
    {
        return $this->getSessionData($clientId)['session_id'];
    }

    /**
     * Get the current engagement time for a client.
     */
    public function getEngagementTime(string $clientId): int
    {
        $cacheKey = $this->getCacheKey($clientId);
        /** @var array{last_active?: int} $sessionData */
        $sessionData = $this->cache->get($cacheKey, []);

        if (! isset($sessionData['last_active'])) {
            return 0;
        }

        $engagementTime = (time() - $sessionData['last_active']) * 1000;

        return max(0, $engagementTime);
    }

    private function getCacheKey(string $clientId): string
    {
        return "ga_last_activity_{$clientId}";
    }
}
