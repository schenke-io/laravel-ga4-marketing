<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use SchenkeIo\LaravelGa4Marketing\Console\VerifyGA4Command;

it('fails if GA4 credentials are not configured', function () {
    Config::set('ga4-marketing.ga4.measurement_id', null);
    Config::set('ga4-marketing.ga4.api_secret', null);

    $this->artisan('ga4-marketing:verify-ga4')
        ->expectsOutput('GA4 Measurement ID or API Secret is not configured.')
        ->assertExitCode(VerifyGA4Command::FAILURE);
});

it('succeeds if GA4 validation returns no errors', function () {
    Config::set('ga4-marketing.ga4.measurement_id', 'G-12345');
    Config::set('ga4-marketing.ga4.api_secret', 'secret');

    Http::fake([
        'https://www.google-analytics.com/debug/mp/collect*' => Http::response(['validationMessages' => []], 200),
    ]);

    $this->artisan('ga4-marketing:verify-ga4')
        ->expectsOutput('Verifying GA4 connection for Measurement ID: G-12345')
        ->expectsOutput('✅ GA4 connection verified successfully! No validation errors found.')
        ->assertExitCode(VerifyGA4Command::SUCCESS);
});

it('fails if GA4 validation returns errors', function () {
    Config::set('ga4-marketing.ga4.measurement_id', 'G-12345');
    Config::set('ga4-marketing.ga4.api_secret', 'secret');

    Http::fake([
        'https://www.google-analytics.com/debug/mp/collect*' => Http::response([
            'validationMessages' => [
                [
                    'validationCode' => 'VALUE_INVALID',
                    'description' => 'Value is invalid',
                    'fieldPath' => 'events[0].name',
                ],
            ],
        ], 200),
    ]);

    $this->artisan('ga4-marketing:verify-ga4')
        ->expectsOutput('❌ GA4 connection verification failed with the following messages:')
        ->expectsOutput('- [VALUE_INVALID] Value is invalid (Field: events[0].name)')
        ->assertExitCode(VerifyGA4Command::FAILURE);
});

it('fails if HTTP request fails', function () {
    Config::set('ga4-marketing.ga4.measurement_id', 'G-12345');
    Config::set('ga4-marketing.ga4.api_secret', 'secret');

    Http::fake([
        'https://www.google-analytics.com/debug/mp/collect*' => Http::response([], 500),
    ]);

    $this->artisan('ga4-marketing:verify-ga4')
        ->expectsOutput('HTTP request to GA4 validation server failed.')
        ->assertExitCode(VerifyGA4Command::FAILURE);
});
