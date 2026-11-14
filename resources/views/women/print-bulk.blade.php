{{-- Bulk print document: a cover index page followed by one complete record
     file per woman, each starting on a fresh A4 page. --}}
@php
    $printedAt = now();
    $roleLabel = match (auth()->user()->role) {
        'rhu' => 'RHU',
        'cho' => 'CHO',
        default => ucwords(str_replace('_', ' ', auth()->user()->role)),
    };
    $printedBy = auth()->user()->name . ' (' . $roleLabel . ')';
    $total = count($records);
    $withCover = $total > 1;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Health Records ({{ $total }}) — ReproCare</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon-logo.png?v=1') }}">
    @include('women.partials.print-styles')
</head>
<body>
    <div class="pr-toolbar no-print">
        <div>
            <div class="pr-toolbar-title">Bulk health records — {{ $total }} {{ Str::plural('woman', $total) }}</div>
            <div class="pr-toolbar-hint">
                The print dialog opens automatically. Each woman's record starts on a new page.
                @if ($skipped > 0)
                    {{ $skipped }} selected {{ Str::plural('record', $skipped) }} could not be loaded and {{ $skipped === 1 ? 'was' : 'were' }} skipped.
                @endif
            </div>
        </div>
        <div class="pr-toolbar-actions">
            <a href="{{ $backUrl }}" class="pr-btn pr-btn-ghost">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0Zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5Z"/></svg>
                Back to All Women
            </a>
            @if ($total > 0)
                <button type="button" class="pr-btn pr-btn-primary" onclick="window.print()">
                    <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1Z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5ZM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3Zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5Zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1Z"/></svg>
                    Print {{ $total }} {{ Str::plural('record', $total) }}
                </button>
            @endif
        </div>
    </div>

    @if ($skipped > 0)
        <div class="pr-notice no-print">
            {{ $skipped }} of the selected {{ Str::plural('record', $skipped + $total) }} could not be loaded (archived, converted, or outside your scope) and {{ $skipped === 1 ? 'was' : 'were' }} skipped.
        </div>
    @endif

    <main class="pr-paper">
        @if ($total === 0)
            <div class="pr-empty" style="font-size:10pt; padding:8mm 0;">
                No printable records remain in this selection. Return to All Women and select again.
            </div>
        @else
            @if ($withCover)
                <article class="pr-record pr-cover">
                    <header class="pr-doc-head">
                        <div class="pr-brand">
                            <img src="{{ asset('images/brand/reprocare-logo.svg') }}" alt="ReproCare logo">
                            <div>
                                <div class="pr-brand-name">ReproCare</div>
                                <div class="pr-brand-sub">Maternal &amp; Reproductive Health Record System<br>San Carlos City, Pangasinan</div>
                            </div>
                        </div>
                        <div class="pr-doc-title">
                            <h1>Bulk Health Records</h1>
                            <div class="pr-doc-meta">
                                <strong>{{ $portal }} Portal</strong> · {{ $scopeLabel }}<br>
                                Printed {{ $printedAt->format('M d, Y · g:i A') }}<br>
                                By {{ $printedBy }}
                            </div>
                        </div>
                    </header>

                    <div class="pr-cover-summary">
                        <span><strong>{{ $total }}</strong> {{ Str::plural('woman', $total) }} in this batch</span>
                        <span>Scope: <strong>{{ $scopeLabel }}</strong></span>
                        <span>Each record begins on a new page, numbered to match this index.</span>
                        @if ($skipped > 0)
                            <span><strong>{{ $skipped }}</strong> selected {{ Str::plural('record', $skipped) }} unavailable and skipped.</span>
                        @endif
                    </div>

                    <section class="pr-section">
                        <div class="pr-section-head">
                            <span class="pr-section-num"><svg viewBox="0 0 16 16" width="9" height="9" fill="currentColor" aria-hidden="true"><path d="M2 2h12v2H2V2Zm0 4h12v2H2V6Zm0 4h8v2H2v-2Z"/></svg></span>
                            <h3 class="pr-section-title">Batch Index</h3>
                            <span class="pr-section-count">{{ $total }} {{ Str::plural('record', $total) }}</span>
                        </div>
                        <table class="pr-table">
                            <colgroup><col style="width:6%"><col style="width:34%"><col style="width:16%"><col style="width:26%"><col style="width:18%"></colgroup>
                            <thead><tr><th class="pr-num">#</th><th>Name</th><th>Type</th><th>Barangay</th><th>Record ID</th></tr></thead>
                            <tbody>
                                @foreach ($records as $index => $record)
                                    @php
                                        $rowWoman = $record['woman'];
                                        $rowWalkIn = $record['type'] === 'unregistered';
                                        $rowName = $rowWalkIn ? $rowWoman->full_name : $rowWoman->name;
                                    @endphp
                                    <tr>
                                        <td class="pr-num">{{ $index + 1 }}</td>
                                        <td>{{ $rowName ?: 'Unknown' }}</td>
                                        <td>{{ $rowWalkIn ? 'Field-only' : 'Registered' }}</td>
                                        <td>{{ $rowWoman->barangay ?: '—' }}</td>
                                        <td>{{ $rowWalkIn ? 'Field' : 'Patient' }} #{{ $rowWoman->id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </section>

                    <footer class="pr-record-foot">
                        <span>Confidential patient information — for authorized health personnel only.</span>
                        <span>Batch cover · ReproCare {{ $portal }}</span>
                    </footer>
                </article>
            @endif

            @foreach ($records as $index => $record)
                @include('women.partials.print-record', $record + [
                    'portal' => $portal,
                    'scopeLabel' => $scopeLabel,
                    'printedBy' => $printedBy,
                    'printedAt' => $printedAt,
                    'bulkIndex' => $withCover ? $index + 1 : null,
                    'bulkTotal' => $withCover ? $total : null,
                ])
            @endforeach
        @endif
    </main>

    @if ($total > 0)
        <script>
            window.addEventListener('load', () => setTimeout(() => window.print(), 400));
        </script>
    @endif
</body>
</html>
