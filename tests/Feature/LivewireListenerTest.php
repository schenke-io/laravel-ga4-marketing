<?php

namespace SchenkeIo\LaravelGa4Marketing\Tests\Feature;

use Livewire\Livewire;
use SchenkeIo\LaravelGa4Marketing\Tests\TestCase;

class LivewireListenerTest extends TestCase
{
    public function test_livewire_listener_registered()
    {
        $this->assertTrue(class_exists(Livewire::class));

        // We just want to ensure it doesn't crash
        \Livewire\trigger('ga4-event', 'test_event', ['p' => 'v']);
        $this->assertTrue(true);
    }
}
