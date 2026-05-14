@if(view()->shared('ga4_marketing_rendered'))
    @if(config('app.debug'))
        <script>console.warn('GA4 Marketing: component included more than once.');</script>
    @endif
@else
    @php
        view()->share('ga4_marketing_rendered', true);
    @endphp
    <x-ga4-marketing::scripts />
    <x-ga4-marketing::config />
@endif
