<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;
use SchenkeIo\LaravelGa4Marketing\Tests\TestCase;

class AnalyticsStateRenderingTest extends TestCase
{
    public function test_config_renders_with_auto_page_view_true_by_default()
    {
        $view = Blade::render('@Ga4MarketingConfig');
        $this->assertStringContainsString('autoPageView: true', $view);
    }

    public function test_config_renders_with_auto_page_view_false_when_tracked()
    {
        $service = app(AnalyticsService::class);
        $service->markPageViewAsTracked();

        $view = Blade::render('@Ga4MarketingConfig');
        $this->assertStringContainsString('autoPageView: false', $view);
    }
}
