{{-- Print-ready complete health record for one woman. Standalone document:
     opens in a new tab from the record view and launches the print dialog. --}}
@php
    $isWalkIn = $type === 'unregistered';
    $displayName = $isWalkIn ? $woman->full_name : $woman->name;
    $printedAt = now();
    $roleLabel = match (auth()->user()->role) {
        'rhu' => 'RHU',
        'cho' => 'CHO',
        default => ucwords(str_replace('_', ' ', auth()->user()->role)),
    };
    $printedBy = auth()->user()->name . ' (' . $roleLabel . ')';
    $bulkIndex = null;
    $bulkTotal = null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $displayName ?: 'Health Record' }} — Women's Health Record — ReproCare</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon-logo.png?v=1') }}">
    @include('women.partials.print-styles')
</head>
<body>
    <div class="pr-toolbar no-print">
        <div>
            <div class="pr-toolbar-title">{{ $displayName ?: 'Health Record' }} — complete health record</div>
            <div class="pr-toolbar-hint">The print dialog opens automatically. Choose “Save as PDF” to export a copy.</div>
        </div>
        <div class="pr-toolbar-actions">
            <a href="{{ $backUrl }}" class="pr-btn pr-btn-ghost">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0Zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5Z"/></svg>
                Back to record
            </a>
            <button type="button" class="pr-btn pr-btn-primary" onclick="window.print()">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1Z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5ZM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3Zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5Zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1Z"/></svg>
                Print record
            </button>
        </div>
    </div>

    <main class="pr-paper">
        @include('women.partials.print-record')
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    </script>
</body>
</html>
