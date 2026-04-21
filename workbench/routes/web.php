<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use SchenkeIo\LaravelGa4Marketing\Workbench\App\Http\Controllers\BrowserTestController;
use SchenkeIo\LaravelGa4Marketing\Workbench\App\Http\Controllers\TestController;

Route::get('/simple', function () {
    return 'Simple Text';
});

Route::get('/', TestController::class)->name('workbench.test');

Route::post('ga4-marketing/event', [BrowserTestController::class, 'logEvent'])
    ->name('ga4-marketing.event')
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::prefix('browser-test')->group(function () {
    Route::get('page-view', [BrowserTestController::class, 'pageView']);
    Route::get('prevented', [BrowserTestController::class, 'prevented']);
    Route::get('outbound-click', [BrowserTestController::class, 'outboundClick']);
    Route::get('scroll', [BrowserTestController::class, 'scroll']);

    Route::post('listener', [BrowserTestController::class, 'logEvent'])
        ->name('browser-test.listener')
        ->withoutMiddleware([ValidateCsrfToken::class]);
    Route::get('events', [BrowserTestController::class, 'getEvents']);
    Route::get('clear', [BrowserTestController::class, 'clearEvents']);
});
