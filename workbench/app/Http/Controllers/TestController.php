<?php

namespace SchenkeIo\LaravelGa4Marketing\Workbench\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TestController extends Controller
{
    public function __invoke(): View
    {
        Log::info('TestController called');
        $envValues = [
            'GA4_MEASUREMENT_ID' => config('ga4-marketing.ga4.measurement_id'),
            'GA4_API_SECRET' => config('ga4-marketing.ga4.api_secret'),
            'GOOGLE_MARKETING_CREDENTIALS' => config('ga4-marketing.credentials'),
        ];

        return view('test-page', compact('envValues'));
    }
}
