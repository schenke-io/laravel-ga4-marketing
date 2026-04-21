<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

class PayloadBuilder
{
    /**
     * Build the payload for the Google Measurement Protocol.
     *
     * @param  string  $clientId  The client ID
     * @param  string|null  $userId  The user ID
     * @param  string  $eventName  The event name
     * @param  array<string, mixed>  $eventParams  Additional parameters
     * @param  array{session_id: string, engagement_time_msec: int, is_new_session: bool, is_new_user: bool, google_ad_id: ?array{type: string, value: string}}  $sessionData  Session-related data
     * @param  string|null  $ipAddress  The user's IP address (will be anonymized)
     * @return array<string, mixed>
     */
    public function build(
        string $clientId,
        ?string $userId,
        string $eventName,
        array $eventParams,
        array $sessionData,
        ?string $ipAddress = null
    ): array {
        $sessionId = $sessionData['session_id'];
        $engagementTime = $sessionData['engagement_time_msec'];
        $isNewSession = $sessionData['is_new_session'];
        $isNewUser = $sessionData['is_new_user'];
        $googleAdId = $sessionData['google_ad_id'];

        if (! isset($eventParams['session_id'])) {
            $eventParams['session_id'] = $sessionId;
        }
        if (! isset($eventParams['engagement_time_msec'])) {
            $eventParams['engagement_time_msec'] = $engagementTime;
        }

        // Add Google Ad ID if available
        if ($googleAdId) {
            $eventParams[$googleAdId['type']] = $googleAdId['value'];
        }

        $events = [];

        if ($isNewUser) {
            $firstVisitParams = [
                'session_id' => $sessionId,
                'engagement_time_msec' => 1,
            ];
            if (isset($eventParams['debug_mode'])) {
                $firstVisitParams['debug_mode'] = 1;
            }
            $events[] = [
                'name' => 'first_visit',
                'params' => $firstVisitParams,
            ];
        }

        if ($isNewSession) {
            $sessionStartParams = [
                'session_id' => $sessionId,
                'engagement_time_msec' => 1,
            ];
            if (isset($eventParams['debug_mode'])) {
                $sessionStartParams['debug_mode'] = 1;
            }

            $events[] = [
                'name' => 'session_start',
                'params' => $sessionStartParams,
            ];
        }

        $events[] = [
            'name' => $eventName,
            'params' => $eventParams,
        ];

        $payload = [
            'client_id' => $clientId,
            'user_id' => $userId,
            'events' => $events,
        ];

        if ($ipAddress) {
            $payload['ip_override'] = $this->anonymizeIp($ipAddress);
        }

        return $payload;
    }

    /**
     * Anonymize the IP address by masking the last octet.
     */
    private function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\d+$/', '0', $ip) ?? $ip;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return preg_replace('/:[a-fA-F0-9]+$/', ':0', $ip) ?? $ip;
        }

        return $ip;
    }
}
