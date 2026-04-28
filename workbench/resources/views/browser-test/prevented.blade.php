<!DOCTYPE html>
<html>
<head>
    <title>Prevented Page View Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body data-ga4-event="no-pageview">
    <h1>Prevented Page View Test</h1>
    @Ga4MarketingScript
</body>
</html>
