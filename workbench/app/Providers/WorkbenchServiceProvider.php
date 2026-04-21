<?php

namespace SchenkeIo\LaravelGa4Marketing\Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use SchenkeIo\LaravelGa4Marketing\Workbench\App\Livewire\TestElement;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Livewire::component('test-element', TestElement::class);
    }
}
