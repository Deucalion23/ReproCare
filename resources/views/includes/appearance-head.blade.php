@php
    $appearanceService = app(\App\Services\AppearanceTheme::class);
    $systemAppearance = $appearanceService->current();
@endphp
<style id="reprocare-system-colors">
    :root {
        @foreach($appearanceService->variables($systemAppearance) as $property => $value)
        {{ $property }}: {{ $value }};
        @endforeach
    }
</style>
<script>
    window.ReproCareAppearance = {{ Illuminate\Support\Js::from([
        'current' => $systemAppearance,
        'defaults' => config('appearance.defaults'),
        'palette' => $appearanceService->palette(),
    ]) }};
</script>
<script src="{{ asset('js/appearance.js') }}?v={{ filemtime(public_path('js/appearance.js')) }}"></script>
