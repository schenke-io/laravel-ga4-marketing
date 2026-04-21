<?php

namespace SchenkeIo\LaravelGa4Marketing\Workbench\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class BrowserTestController extends Controller
{
    protected function getFilePath(): string
    {
        return dirname(__DIR__, 4).'/.junie/tmp/browser-test-events.json';
    }

    public function logEvent(Request $request)
    {
        $event = $request->all();
        $events = [];
        $path = $this->getFilePath();
        if (File::exists($path)) {
            $events = json_decode(File::get($path), true) ?: [];
        }
        $events[] = $event;
        File::put($path, json_encode($events));

        return response()->json(['status' => 'ok']);
    }

    public function getEvents()
    {
        $path = $this->getFilePath();
        if (File::exists($path)) {
            return response()->json(json_decode(File::get($path), true) ?: []);
        }

        return response()->json([]);
    }

    public function clearEvents()
    {
        $path = $this->getFilePath();
        if (File::exists($path)) {
            File::delete($path);
        }

        return response()->json(['status' => 'ok']);
    }

    public function pageView()
    {
        return view('browser-test.page-view');
    }

    public function prevented()
    {
        return view('browser-test.prevented');
    }

    public function outboundClick()
    {
        return view('browser-test.outbound-click');
    }

    public function scroll()
    {
        return view('browser-test.scroll');
    }
}
