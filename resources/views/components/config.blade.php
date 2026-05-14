<script>
    if (window.ga4Marketing) {
        ga4Marketing.init({
            route: '{{ route('ga4-marketing.event') }}',
            token: '{{ csrf_token() }}',
            autoPageView: {{ app(\SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService::class)->wasPageViewTracked() ? 'false' : 'true' }}
        });
    }
</script>
