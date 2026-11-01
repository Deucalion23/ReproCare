@extends('midwife.layout')

@section('title', 'Pregnancies - ReproCare')

@push('styles')
<style>
    /* portal-theme.blade.php:369-386 pins .main-content .table cells at 0.9rem/1.25rem
       with !important, so every rule that has to beat it carries body .main-content. */
    .pregnancy-records-card .card-body { padding:0.85rem 1rem !important; }
    body .main-content .page-hero { padding:0.6rem 1rem !important; margin-bottom:0.6rem !important; }
    body .main-content .page-hero .page-hero-title { font-size:1.05rem !important; margin-bottom:0 !important; }
    body .main-content .page-hero .page-hero-subtitle { font-size:0.78rem !important; }
    body .main-content .page-hero .btn-hero-primary { padding:0.4rem 0.85rem !important; font-size:0.85rem !important; }
    body .main-content .pregnancy-records-card .card-header { padding:0.5rem 0.85rem !important; gap:0.5rem !important; justify-content:flex-start !important; }
    body .main-content .pregnancy-records-card .card-header h5 { font-size:0.9rem !important; margin:0 !important; white-space:nowrap; }

    .pr-metrics { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:0.55rem; }
    .pr-tile { display:flex; align-items:center; gap:0.6rem; min-width:0; padding:0.5rem 0.7rem; text-decoration:none !important;
               border:1px solid var(--color-border); border-radius:12px; background:var(--color-surface);
               transition:border-color .18s, background .18s, box-shadow .18s; }
    .pr-tile:hover { border-color:var(--tile-ink); box-shadow:0 4px 14px rgb(16 24 40 / .07); }
    .pr-tile.active { border-color:var(--tile-ink); background:var(--tile-soft); }
    .pr-tile-icon { width:30px; height:30px; border-radius:9px; flex:0 0 auto; display:grid; place-items:center;
                    font-size:0.95rem; background:var(--tile-soft); color:var(--tile-ink) !important; }
    .pr-tile-text { min-width:0; display:flex; flex-direction:column; gap:1px; }
    .pr-tile-count { font-size:1.05rem; font-weight:800; line-height:1.1; color:var(--color-text) !important; }
    .pr-tile-label { font-size:0.64rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; line-height:1.15;
                     color:var(--color-text-muted) !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    /* Narrow screens get a 4-across strip, and only the short wording fits beside a count. */
    .pr-tile-label-short { display:none; font-size:0.58rem; font-weight:700; text-transform:uppercase; letter-spacing:0.03em;
                           line-height:1.1; color:var(--color-text-muted) !important; white-space:nowrap; }
    .pr-tile.active .pr-tile-label, .pr-tile.active .pr-tile-label-short,
    .pr-tile.active .pr-tile-count { color:var(--tile-ink) !important; }

    /* Filters live in the table header. width:auto beats the global input/select{width:100%}.
       Heights are left to portal-theme.blade.php:401 (min-height:42px) — a thumb target worth keeping. */
    .pr-filters { display:flex; align-items:center; gap:0.45rem; flex:1 1 auto; flex-wrap:wrap; justify-content:flex-end; }
    .pr-search { position:relative; flex:1 1 150px; min-width:140px; max-width:220px; }
    .pr-search i { position:absolute; left:0.6rem; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--color-text-muted); pointer-events:none; }
    .pr-search input { width:100% !important; border-radius:10px; padding:0 0.7rem 0 1.9rem; font-size:0.8rem; }
    .pr-select, .pr-month { width:auto !important; flex:0 0 auto; border-radius:10px; font-size:0.78rem; padding:0 1.7rem 0 0.6rem; }
    .pr-month { padding-right:0.5rem; }
    .pr-month-wrap { display:inline-flex; align-items:center; gap:0.35rem; flex:0 0 auto; }
    .pr-month-word { font-size:0.66rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;
                     color:var(--color-text-muted); white-space:nowrap; }
    .pr-reset { flex:0 0 auto; font-size:0.75rem; }

    .pregnancy-records-table { width:100%; table-layout:fixed; font-size:0.74rem !important; }
    body .main-content .pregnancy-records-table thead th,
    body .main-content .pregnancy-records-table tbody td { padding:0.4rem 0.4rem !important; overflow:hidden; }
    .pregnancy-records-table th { font-size:0.63rem; white-space:nowrap; }
    .pregnancy-records-table .pr-th-short { display:none; }
    .pregnancy-records-table td:first-child .d-flex,
    .pregnancy-records-table td:first-child .d-flex > div:last-child { min-width:0; }
    .pregnancy-records-table td:first-child div[style*="font-size"] { font-size:0.78rem !important; }
    .pregnancy-records-table td:first-child small { max-width:100% !important; }
    /* One short line under a date, never three: a wrapped note is what made rows 111px. */
    .pregnancy-records-table td small { display:block; font-size:0.66rem; line-height:1.2; white-space:nowrap; }
    .pregnancy-records-table .pregnancy-trimester,
    .pregnancy-records-table .pregnancy-status { gap:0.25rem !important; padding:0.2rem 0.45rem !important; font-size:0.68rem !important; white-space:nowrap; }
    .pregnancy-records-table .badge { padding:0.2rem 0.38rem !important; font-size:0.66rem !important; white-space:nowrap; }
    .pregnancy-records-table .pregnancy-actions { gap:0.3rem !important; flex-wrap:nowrap !important; justify-content:flex-end; }
    .pregnancy-records-table .pregnancy-actions .btn { flex:0 0 26px; width:26px; height:26px; display:inline-grid; place-items:center; padding:0 !important; }
    body .main-content .pregnancy-records-table th:first-child,
    body .main-content .pregnancy-records-table td:first-child { padding-left:0.85rem !important; }
    body .main-content .pregnancy-records-table th:last-child,
    body .main-content .pregnancy-records-table td:last-child { padding-right:0.6rem !important; overflow:visible; }

    /* Six nowrap columns cannot land inside a phone, so the wrapper scrolls; say so rather
       than leaving the midwife to discover the Risk column by accident. */
    .pr-swipe { display:none; font-size:0.66rem; color:var(--color-text-muted); margin:0; padding:0.35rem 0.75rem 0; }

    @media (max-width: 1199.98px) {
        .pregnancy-records-table .pr-col-lmp, .pregnancy-records-table .pr-col-tri { display:none; }
        .pregnancy-records-table td:first-child small { display:none !important; }
    }
    @media (max-width: 1199.98px) {
        /* Below desktop width the flex strip wraps the Clear button onto a third row, so the
           strip becomes a 3-column grid: search on top, one picker per column under it. */
        body .main-content .pregnancy-records-card .card-header { flex-direction:column; align-items:stretch !important; }
        /* portal-theme.blade.php:266 gives .main-content .card-header h5 display:flex at the same
           specificity as a plain page rule and later in the cascade, so the hide needs the body prefix. */
        body .main-content .pregnancy-records-card .card-header h5 { display:none; }
        .pr-filters { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:0.4rem; }
        .pr-search { grid-column:1 / -1; max-width:none; }
        .pr-filters.has-reset .pr-search { grid-column:1 / span 2; }
        .pr-filters.has-reset .pr-reset { grid-column:3; grid-row:1; justify-self:end; }
        .pr-select, .pr-month-wrap { grid-column:span 1; min-width:0; }
        body .main-content .pr-select, body .main-content .pr-month { width:100% !important; }
    }
    @media (max-width: 767.98px) {
        /* Tiles go 4-across with short labels so the whole status filter stays on one row. */
        .pr-metrics { grid-template-columns:repeat(4, minmax(0,1fr)); gap:0.4rem; }
        .pr-tile { flex-direction:column; align-items:flex-start; gap:0.1rem; padding:0.4rem 0.45rem; }
        .pr-tile-icon { display:none; }
        .pr-tile-label { display:none; }
        .pr-tile-label-short { display:block; }
        .pr-tile-count { font-size:0.95rem; }
        .pregnancy-records-card .card-body { padding:0.7rem !important; }
        body .main-content .pr-select, body .main-content .pr-month { font-size:0.7rem; padding:0 1.4rem 0 0.5rem; }
        .pr-month-word { display:none; }
        .pr-reset { min-height:0; padding:0.25rem 0.5rem; font-size:0.7rem; }
        .pregnancy-records-table .pr-col-bar { display:none; }
        /* Once three columns are hidden, table-layout:fixed matches the remaining cells to
           <col> entries by position and every width has to be re-guessed. Auto sizing costs
           nothing here — the e-mail sub-line is already hidden at this width — and it lets
           .table-responsive scroll instead of cutting "Completed" in half. */
        .pregnancy-records-table { table-layout:auto; }
        .pregnancy-records-table .pr-th-long { display:none; }
        .pregnancy-records-table .pr-th-short { display:inline; }
        .pregnancy-records-table .pr-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pr-swipe { display:block; }
    }
</style>
@endpush

@section('midwife-content')
@php
    $status = (string) ($status ?? 'all');
    $month = (string) ($month ?? '');
    $tiles = [
        'all' => ['label' => 'All cases', 'short' => 'All', 'count' => $summary['total'], 'icon' => 'bi-heart-fill', 'hue' => 'primary'],
        'active' => ['label' => 'Active monitoring', 'short' => 'Active', 'count' => $summary['active'], 'icon' => 'bi-heart-pulse-fill', 'hue' => 'success'],
        'completed' => ['label' => 'Completed deliveries', 'short' => 'Delivered', 'count' => $summary['completed'], 'icon' => 'bi-check-circle-fill', 'hue' => 'info'],
        'high-risk' => ['label' => 'High risk triage', 'short' => 'High risk', 'count' => $summary['highRisk'], 'icon' => 'bi-exclamation-triangle-fill', 'hue' => 'danger'],
    ];
    // One builder for every tile, select and reset: filters stack instead of replacing
    // each other, and any change restarts pagination. 'all'/'' never rides in the URL.
    $prUrl = function (array $changes = [], array $unset = []) {
        $query = array_merge(request()->query(), $changes);
        foreach ($unset as $key) { unset($query[$key]); }
        unset($query['page']);
        return route('midwife.pregnancies.index', array_filter(
            $query, fn ($value) => $value !== '' && $value !== null && $value !== 'all' && $value !== []
        ));
    };
    $isFiltered = $status !== 'all' || $month !== '' || (string) request('trimester', 'all') !== 'all'
        || (string) request('risk_level', 'all') !== 'all' || (string) request('search', '') !== '';
@endphp

<div class="pt-1 pb-2">
    {{-- ═══ HERO: title, date, single action ═══ --}}
    <div class="page-hero fade-in-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Pregnancy Tracking</div>
                <p class="page-hero-subtitle">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                    &nbsp;·&nbsp; {{ $pregnancies->total() }} case{{ $pregnancies->total() === 1 ? '' : 's' }} in view
                </p>
            </div>
            <a href="{{ route('midwife.pregnancies.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill"></i> Add Pregnancy
            </a>
        </div>
    </div>

    {{-- ═══ STATUS TILES: the old stat cards, now the status filter ═══ --}}
    <div class="pr-metrics mb-2" role="group" aria-label="Filter pregnancies by case status">
        @foreach($tiles as $tileKey => $tile)
            <a href="{{ $prUrl(['status' => $tileKey]) }}"
               class="pr-tile fade-in-card {{ $status === $tileKey ? 'active' : '' }}"
               style="--tile-soft:var(--color-{{ $tile['hue'] }}-soft); --tile-ink:var(--color-{{ $tile['hue'] }}-text);"
               @if($status === $tileKey) aria-current="page" @endif>
                <span class="pr-tile-icon"><i class="bi {{ $tile['icon'] }}"></i></span>
                <span class="pr-tile-text">
                    <span class="pr-tile-count">{{ $tile['count'] }}</span>
                    <span class="pr-tile-label">{{ $tile['label'] }}</span>
                    <span class="pr-tile-label-short">{{ $tile['short'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- ═══ RECORDS TABLE, with the filters in its own header ═══ --}}
    <div class="card fade-in-card pregnancy-records-card" style="border:none; background:var(--bg-card);">
        <div class="card-header d-flex align-items-center">
            <h5 class="fw-bold">Pregnancy Records</h5>
            <form action="{{ route('midwife.pregnancies.index') }}" method="GET" class="pr-filters{{ $isFiltered ? ' has-reset' : '' }}">
                @if($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($month !== '')<input type="hidden" name="month" value="{{ $month }}">@endif
                <span class="pr-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Name, email or contact number" aria-label="Search pregnancies by name, email or contact number">
                </span>
                <select name="trimester" class="form-select pr-select" aria-label="Filter by trimester" onchange="this.form.submit()">
                    @foreach(['all' => 'All trimesters', '1' => '1st trimester', '2' => '2nd trimester', '3' => '3rd trimester'] as $optionKey => $optionLabel)
                        <option value="{{ $optionKey }}" {{ request('trimester', 'all') === $optionKey ? 'selected' : '' }}>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                <select name="risk_level" class="form-select pr-select" aria-label="Filter by risk level" onchange="this.form.submit()">
                    @foreach(['all' => 'All risk levels', 'Low' => 'Low risk', 'Medium' => 'Medium risk', 'High' => 'High risk'] as $optionKey => $optionLabel)
                        <option value="{{ $optionKey }}" {{ request('risk_level', 'all') === $optionKey ? 'selected' : '' }}>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                {{-- Deliveries are planned by month, so the picker is the due date, not the record date. --}}
                <label class="pr-month-wrap">
                    <span class="pr-month-word">Due</span>
                    {{-- the word is hidden on phones, so the input carries its own name --}}
                    <input type="month" id="prMonth" name="month" class="form-control pr-month" value="{{ $month }}"
                           aria-label="Filter by due month" onchange="this.form.submit()">
                </label>
                @if($isFiltered)
                    <a href="{{ route('midwife.pregnancies.index') }}" class="btn btn-sm btn-outline-secondary pr-reset">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 py-2" role="alert"
                     style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border-bottom:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:0;">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger fade show mb-0 rounded-0 py-2" role="alert" style="border-radius:0;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            @if($pregnancies->count() > 0)
                <p class="pr-swipe"><i class="bi bi-arrow-left-right me-1"></i>Swipe sideways to reach every column</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 pregnancy-records-table">
                        <colgroup>
                            <col style="width:18%"><col class="pr-col-bar" style="width:11%"><col style="width:8%">
                            <col class="pr-col-tri" style="width:8%"><col class="pr-col-lmp" style="width:11%">
                            <col style="width:12%"><col style="width:9%"><col style="width:9%"><col style="width:10%">
                        </colgroup>
                        <thead>
                            <tr style="color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                                <th>Patient</th>
                                <th class="pr-col-bar">Barangay</th>
                                <th><span class="pr-th-long">Gestational age</span><span class="pr-th-short">GA</span></th>
                                <th class="pr-col-tri">Trimester</th>
                                <th class="pr-col-lmp">LMP</th>
                                <th>EDD</th>
                                <th>Status</th>
                                <th>Risk</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pregnancies as $pregnancy)
                                @php
                                    // Field records carry their own barangay; an enrolled woman carries hers.
                                    $barangay = optional($pregnancy->woman)->barangay ?: optional($pregnancy->walkInPatient)->barangay;
                                    $isFieldRecord = !$pregnancy->user_id && $pregnancy->walk_in_patient_id;
                                @endphp
                                <tr style="border-bottom:1px solid var(--border-color);">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--color-on-solid);flex-shrink:0;font-size:0.8rem;">
                                                {{ strtoupper(substr($pregnancy->patient_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="pr-name" title="{{ $pregnancy->patient_name }}"
                                                     style="font-weight:600; color:var(--text); font-size:0.85rem;">{{ $pregnancy->patient_name }}</div>
                                                <small style="color:var(--text-muted); display:block; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                    @if($pregnancy->user_id && $pregnancy->woman)
                                                        {{ $pregnancy->woman->email }}
                                                    @else
                                                        {{ optional($pregnancy->walkInPatient)->contact_number ?? 'Field record · no contact' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pr-col-bar">
                                        @if($barangay)
                                            <span style="color:var(--text);">{{ $barangay }}</span>
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- "52 weeks, 3 days" is 124px in a 106px column; the short form reads
                                             at a glance and the full wording stays in the tooltip. --}}
                                        <div style="font-weight:600; color:var(--text); white-space:nowrap;"
                                             title="{{ $pregnancy->formatted_aog }}">
                                            {{ $pregnancy->lmp ? (int) floor($pregnancy->aog_weeks).'w '.$pregnancy->aog_days.'d' : '—' }}
                                        </div>
                                    </td>
                                    <td class="pr-col-tri">
                                        @php $triLabels = [1 => '1st tri', 2 => '2nd tri', 3 => '3rd tri']; @endphp
                                        @if($pregnancy->trimester)
                                            <div class="pregnancy-trimester" title="{{ $pregnancy->trimester_name }}"
                                                 style="display:inline-flex; align-items:center; gap:0.35rem; background:color-mix(in srgb, var(--color-info) 10%, transparent); color:var(--info); border-radius:20px; font-weight:500; white-space:nowrap;">
                                                <i class="bi bi-calendar-week"></i>
                                                {{ $triLabels[$pregnancy->trimester] ?? $pregnancy->trimester_name }}
                                            </div>
                                        @else
                                            <span style="color:var(--text-muted);">N/A</span>
                                        @endif
                                    </td>
                                    <td class="pr-col-lmp">
                                        <div style="font-weight:500; color:var(--text); white-space:nowrap;">
                                            {{ \Carbon\Carbon::parse($pregnancy->lmp)->format('M j, Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:500; color:var(--text); white-space:nowrap;">
                                            {{ optional($pregnancy->edd)->format('M j, Y') ?: \Carbon\Carbon::parse($pregnancy->lmp)->addDays(280)->format('M j, Y') }}
                                        </div>
                                        @if($pregnancy->is_active)
                                            <small style="color:var(--text-muted);">{{ $pregnancy->days_until_due }} days left</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="pregnancy-status" style="display:inline-flex; align-items:center; background:{{ $pregnancy->status === 'completed' ? 'color-mix(in srgb, var(--color-surface-soft) 10%, transparent)' : 'color-mix(in srgb, var(--color-success-text) 10%, transparent)' }}; color:{{ $pregnancy->status === 'completed' ? 'var(--text-muted)' : 'var(--success)' }}; border-radius:20px; font-weight:500;">
                                            {{ ucfirst($pregnancy->status ?? 'Unknown') }}
                                        </div>
                                    </td>
                                    <td>
                                        @php $rLevel = $pregnancy->risk_level ?? 'Low'; @endphp
                                        @if($rLevel === 'High')
                                            <span class="badge rounded-pill fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> High
                                            </span>
                                        @elseif($rLevel === 'Medium')
                                            <span class="badge rounded-pill fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i> Medium
                                            </span>
                                        @else
                                            <span class="badge rounded-pill fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                                <i class="bi bi-check-circle-fill me-1"></i> Low
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="d-flex pregnancy-actions">
                                            {{-- A field record has no account yet, so the case view is the only
                                                 way to reach the form she was interviewed on. --}}
                                            @if($isFieldRecord)
                                                <a href="{{ route('midwife.walk-in-patients.show', $pregnancy->walk_in_patient_id) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Field record · enrol"
                                                   style="border-radius:8px;">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}"
                                               class="btn btn-sm btn-outline-primary" title="View details"
                                               style="border-radius:8px;">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('midwife.pregnancies.edit', $pregnancy->id) }}"
                                               class="btn btn-sm btn-outline-warning" title="Edit"
                                               style="border-radius:8px;">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-3 py-2 border-top">
                    {{ $pregnancies->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div style="width:64px;height:64px;border-radius:18px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-heart-pulse" style="font-size:1.75rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color:var(--text);">No pregnancies match these filters</h5>
                    <p class="text-muted mb-3" style="font-size:0.9rem;">
                        @if($isFiltered)
                            Widen the search, pick another month, or
                            <a href="{{ route('midwife.pregnancies.index') }}" class="fw-semibold text-decoration-none"><i class="bi bi-x-lg"></i> clear all filters</a>.
                        @else
                            No pregnancy records have been added yet.
                        @endif
                    </p>
                    <a href="{{ route('midwife.pregnancies.create') }}" class="btn btn-primary" style="border-radius:10px; padding:0.6rem 1.2rem;">
                        <i class="bi bi-plus-circle me-2"></i>Add Pregnancy
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
