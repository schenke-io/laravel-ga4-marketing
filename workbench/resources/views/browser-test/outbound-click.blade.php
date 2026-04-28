<!DOCTYPE html>
<html>
<head>
    <title>Outbound Click Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body data-ga4-event="no-pageview">
    <h1>Outbound Click Test</h1>
    <a href="/browser-test/page-view" id="outbound-link" data-ga4-event="outbound">Outbound Link</a>
    @Ga4MarketingScript
</body>
</html>
