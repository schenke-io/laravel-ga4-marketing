<script>
    {!! $jsContent !!}
    window.ga4Marketing.init({
        route: '{{ route('ga4-marketing.event') }}',
        token: '{{ csrf_token() }}'
    });
</script>
