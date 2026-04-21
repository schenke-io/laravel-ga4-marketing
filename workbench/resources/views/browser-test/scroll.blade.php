<!DOCTYPE html>
<html>
<head>
    <title>Scroll Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .spacer { height: 1000px; }
        .target { height: 100px; background: red; }
    </style>
</head>
<body data-ga4-event="no-pageview">
    <h1>Scroll Test</h1>
    <div class="spacer"></div>
    <div id="scroll-target" class="target" data-ga4-event="scroll" data-ga4-area="80%">80% Scroll Target</div>
    <div class="spacer"></div>
    @G4MarketingScript
</body>
</html>
