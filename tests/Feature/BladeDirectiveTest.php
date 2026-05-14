<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use SchenkeIo\LaravelGa4Marketing\Tests\TestCase;

class BladeDirectiveTest extends TestCase
{
    public function test_ga4_marketing_script_directive_renders_correctly()
    {
        $view = Blade::render('@Ga4MarketingScript');

        $this->assertStringContainsString('<script', $view);
        $this->assertStringContainsString('ga4Marketing', $view);
    }

    public function test_ga4_marketing_config_directive_renders_correctly()
    {
        $view = Blade::render('@Ga4MarketingConfig');

        $this->assertStringContainsString('<script', $view);
        $this->assertStringContainsString('ga4Marketing.init', $view);
        $this->assertStringContainsString('autoPageView: true', $view);
    }
}
