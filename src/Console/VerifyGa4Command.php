<?php

namespace SchenkeIo\LaravelGa4Marketing\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * Console command to verify the GA4 connection.
 *
 * This command sends a test event to the Google Analytics debug
 * validation server to ensure the Measurement ID and API Secret
 * are correctly configured and the connection is healthy.
 */
class VerifyGa4Command extends Command
{
    protected $signature = 'ga4-marketing:verify-ga4';

    protected $description = 'Verify GA4 connection using the debug validation server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $measurementId = Config::get('ga4-marketing.ga4.measurement_id');
        $apiSecret = Config::get('ga4-marketing.ga4.api_secret');

        if (! $measurementId || ! $apiSecret) {
            $this->error('GA4 Measurement ID or API Secret is not configured.');

            return self::FAILURE;
        }

        $this->info("Verifying GA4 connection for Measurement ID: $measurementId");

        $response = Http::post("https://www.google-analytics.com/debug/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}", [
            'client_id' => 'verification-client',
            'events' => [
                [
                    'name' => 'verification_event',
                    'params' => (object) [],
                ],
            ],
        ]);

        if ($response->failed()) {
            $this->error('HTTP request to GA4 validation server failed.');

            return self::FAILURE;
        }

        $results = $response->json('validationMessages');

        if (empty($results)) {
            $this->info('✅ GA4 connection verified successfully! No validation errors found.');

            Http::post("https://www.google-analytics.com/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}", [
                'client_id' => 'verification-client',
                'events' => [
                    [
                        'name' => 'verification_event',
                        'params' => ['debug_mode' => 1],
                    ],
                ],
            ]);

            $this->info('📡 A debug event was sent to GA4. You should see it in your DebugView shortly.');

            return self::SUCCESS;
        }

        $this->error('❌ GA4 connection verification failed with the following messages:');
        foreach ($results as $message) {
            $code = $message['validationCode'] ?? 'UNKNOWN';
            $description = $message['description'] ?? 'No description provided';
            $field = isset($message['fieldPath']) ? " (Field: {$message['fieldPath']})" : '';
            $this->line("- [$code] $description$field");
        }

        return self::FAILURE;
    }
}
