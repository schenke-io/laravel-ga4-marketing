@php
    /** @var \SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService $ga4Service */
    $ga4Service = app(\SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService::class);
    $ga4Healthy = $ga4Service->isHealthy();
@endphp

<div class="p-4 bg-white shadow rounded-lg border border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">GA4 Marketing</h2>
        <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">v1.0.0</span>
    </div>

    <div class="space-y-4">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 {{ $ga4Healthy ? 'bg-green-500' : 'bg-red-500' }} rounded-full"></div>
            <span class="text-sm text-gray-600">GA4 {{ $ga4Healthy ? 'Configured' : 'Missing Credentials' }}</span>
        </div>

        <div class="bg-gray-100 p-3 rounded text-sm font-mono text-gray-700 break-all">
            &lt;x-ga4-marketing::ga4-marketing /&gt;
        </div>

        @if(!$ga4Healthy)
            <div class="p-2 text-sm text-red-700 bg-red-100 border border-red-200 rounded">
                Warning: Set GA4_MEASUREMENT_ID and GA4_API_SECRET in your .env file.
            </div>
        @endif
    </div>
</div>
