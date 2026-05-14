<?php

namespace SchenkeIo\LaravelGa4Marketing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

/**
 * Controller for handling GA4 events triggered from the client-side.
 *
 * This controller receives event data via POST requests and routes it
 * to the AnalyticsService for processing.
 */
class EventController extends Controller
{
    /**
     * Store a new GA4 event triggered from the client-side.
     */
    public function store(Request $request, AnalyticsService $ga4Service): void
    {
        $request->validate([
            'event_name' => 'required|string',
            'event_params' => 'nullable|array',
        ]);

        $ga4Service->processEventFromJs(
            $request->input('event_name'),
            $request->input('event_params', [])
        );
    }
}
