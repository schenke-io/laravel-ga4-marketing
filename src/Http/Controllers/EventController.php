<?php

namespace SchenkeIo\LaravelGa4Marketing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

class EventController extends Controller
{
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
