<?php

namespace SchenkeIo\LaravelGa4Marketing\Tests;

use Illuminate\Support\Facades\Cache;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use SchenkeIo\LaravelGa4Marketing\Ga4MarketingServiceProvider;
use SchenkeIo\LaravelGa4Marketing\Workbench\App\Providers\WorkbenchServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            WorkbenchServiceProvider::class,
            Ga4MarketingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:Hupx3yAySlyf96qD5qI8tcrS+1Y1iY4X8s86v0/T3eU=');
    }
}
