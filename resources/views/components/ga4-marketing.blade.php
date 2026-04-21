@if(view()->shared('ga4_marketing_loaded'))
    <script>console.warn('ga4-marketing component included more than once.');</script>
@else
    @php
        view()->share('ga4_marketing_loaded', true);
    @endphp
    <x-ga4-marketing::scripts />
@endif
