<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use SchenkeIo\LaravelGa4Marketing\Tests\TestCase;

class BladeDirectiveTest extends TestCase
{
    public function test_ga4_marketing_script_directive_renders_correctly()
    {
        $view = Blade::render('@G4MarketingScript');

        $this->assertStringContainsString('<script', $view);
        $this->assertStringContainsString('ga4Marketing', $view);
    }
}
