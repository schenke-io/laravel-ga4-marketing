<?php

namespace SchenkeIo\LaravelGa4Marketing\Workbench\App\Livewire;

use Livewire\Component;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;

class TestElement extends Component
{
    public string $message = '';

    public string $eventName = 'test_event';

    public string $eventValue = '123';

    public bool $debugMode = true;

    public array $debugResponse = [];

    public function sendMessage(): void
    {
        $this->message = 'Event Sent!';
        $this->dispatch('test-event');
        $this->dispatch('ga4-event', 'livewire_test_event');
    }

    public function sendTestEvent(AnalyticsService $ga4Service): void
    {
        $this->message = "Sending event: {$this->eventName}";

        $ga4Service->setDebugMode($this->debugMode)
            ->sendEvent(
                $ga4Service->getClientId(),
                $this->eventName,
                ['value' => $this->eventValue]
            );

        $this->message = 'Event sent successfully!';
    }

    public function render()
    {
        return view('livewire.test-element');
    }
}
