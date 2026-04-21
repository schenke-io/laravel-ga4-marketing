<!DOCTYPE html>
<html>
<head>
    <title>Workbench Test Page</title>
    @livewireStyles
</head>
<body>
    <h1>Workbench Test Page</h1>

    <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;">
        <h3>Environment Values</h3>
        <ul>
            @foreach($envValues as $key => $value)
                <li><strong>{{ $key }}:</strong> {{ $value ?: '(empty)' }}</li>
            @endforeach
        </ul>
    </div>

    <div>
        <livewire:test-element />
    </div>

    <div style="margin-top: 20px;">
        <button onclick="ga4Event('js_button_click', {source: 'inline_js'})">Send JS Event</button>
    </div>

    @G4MarketingScript
    @livewireScripts
</body>
</html>
