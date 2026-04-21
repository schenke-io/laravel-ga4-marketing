<div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff;">
    <h4 style="margin-top: 0;">Test GA4 Event</h4>

    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 12px; color: #666;">Event Name</label>
        <input type="text" wire:model="eventName" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 10px;">
        <label style="display: block; font-size: 12px; color: #666;">Event Value</label>
        <input type="text" wire:model="eventValue" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: flex; align-items: center; cursor: pointer;">
            <input type="checkbox" wire:model="debugMode" style="margin-right: 8px;">
            <span style="font-size: 14px;">Debug Mode (Use validation endpoint)</span>
        </label>
    </div>

    <button wire:click="sendTestEvent" style="background: #4A90E2; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">
        Send Test Event
    </button>

    @if($message)
        <div id="event-message" style="margin-top: 15px; padding: 10px; border-radius: 4px; background: #eef7ff; border: 1px solid #d1e9ff; font-size: 14px;">
            {{ $message }}
        </div>
    @endif

    @if(!empty($debugResponse))
        <div style="margin-top: 15px;">
            <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Debug Info / Response:</label>
            <pre style="background: #2d2d2d; color: #ccc; padding: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto;">{{ json_encode($debugResponse, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    <button wire:click="sendMessage" id="send-event-button" style="background: #eee; border: 1px solid #ccc; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">
        Send Default Event
    </button>
</div>
