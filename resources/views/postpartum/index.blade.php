@extends(request()->routeIs('midwife.postpartum.*') ? 'midwife.layout' : 'bhw.layout')

@section('title', 'Postpartum & Newborn Monitoring - ReproCare')

@php
    $contentSection = request()->routeIs('midwife.postpartum.*') ? 'midwife-content' : 'bhw-content';
    $routeBase = request()->routeIs('midwife.postpartum.*') ? 'midwife.postpartum' : 'bhw.postpartum';
    $isMidwife = request()->routeIs('midwife.postpartum.*');
    $railLimit = 5;
    $hasRail = $dangerVisits->count() > 0;
    // A filtered list can empty the follow-up rail, and "show only that section" would
    // then render a blank page — fall back to the combined view instead.
    $view = ($view === 'followups' && !$hasRail) ? 'all' : $view;
@endphp

@section($contentSection)
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Postpartum &amp; Newborn Monitoring</div>
            <p class="page-hero-subtitle mb-0">Activated once a delivery date is recorded — prenatal → delivery → postnatal in one view.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="summary-chip chip-primary"><i class="bi bi-person-badge-fill"></i> {{ $delivered->total() }} mother(s) tracked</span>
            <span class="summary-chip chip-info"><i class="bi bi-balloon-heart-fill"></i> {{ $newborns->total() }} newborn(s)</span>
            <span class="summary-chip chip-warning"><i class="bi bi-syringe"></i> {{ $immunizationsDue }} vaccine dose(s) due</span>
            @if($hasRail)
                <span class="summary-chip chip-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ $dangerVisits->count() }} visit(s) flagged</span>
            @else
                <span class="summary-chip chip-success"><i class="bi bi-shield-check"></i> No flagged visits</span>
            @endif
            @unless($isMidwife)
            <span class="summary-chip chip-primary"><i class="bi bi-house-heart-fill"></i> House-to-house toolkit</span>
            @endunless
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mirrors .wd-tab from the women directory; the app has no global tab style. */
    .pp-filter { display:inline-flex; align-items:center; gap:0.4rem; border:1px solid var(--color-border) !important; border-radius:999px !important; font-weight:800 !important; font-size:0.78rem !important; padding:0.42rem 1rem !important; color:var(--color-text) !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; text-decoration:none !important; }
    .pp-filter:hover { border-color:var(--color-primary) !important; color:var(--color-primary-text) !important; }
    .pp-filter.active { background:var(--color-text) !important; background-color:var(--color-text) !important; border-color:var(--color-text) !important; color:var(--color-bg) !important; }
    .pp-filter.active i, .pp-filter .count { color:inherit; }
    .pp-filter .count { opacity:0.7; font-weight:700; }

    /* Tabs and filters each get their own row. Kept beside the tabs the form was
       squeezed into a narrow right column and the three selects stacked one per line. */
    .pp-toolbar { display:grid; gap:0.5rem; margin-bottom:0.85rem; }
    .pp-tabs { display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; }
    .pp-filters { display:flex; align-items:center; gap:0.45rem 0.6rem; flex-wrap:wrap; }
    .pp-search { position:relative; flex:1 1 170px; min-width:150px; max-width:260px; }
    .pp-search i { position:absolute; left:0.6rem; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--color-text-muted); pointer-events:none; }
    .pp-search input { width:100% !important; border-radius:10px; padding:0 0.7rem 0 1.9rem; font-size:0.8rem; }
    .pp-field { display:inline-flex; align-items:center; gap:0.35rem; }
    .pp-word { font-size:0.66rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); white-space:nowrap; }
    /* portal-theme.blade.php:401 pins .main-content controls at min-height:42px and the global
       input/select rule at width:100%, which would blow this strip apart — both need beating. */
    body .main-content .pp-select { width:auto !important; min-height:38px !important; border-radius:10px; font-size:0.78rem; padding:0 1.7rem 0 0.6rem; }
    body .main-content .pp-search input { min-height:38px !important; }
    .pp-clear { flex:0 0 auto; font-size:0.75rem; min-height:0; padding:0.3rem 0.6rem; }

    /* One section at a time has to beat Bootstrap's col-lg-* widths. */
    #postpartumSections.is-filtered > [data-section] { display:none; }
    #postpartumSections.is-filtered > [data-section].is-active { display:block; flex:0 0 100%; max-width:100%; }

    /* The hero carried four full-size chips above the lists; shrink it so the tabs sit
       near the top of the screen on a phone. */
    body .main-content .page-hero { padding:0.7rem 1rem !important; }
    body .main-content .page-hero .page-hero-title { font-size:1.05rem !important; }
    body .main-content .page-hero .page-hero-subtitle { font-size:0.78rem !important; }
    body .main-content .summary-chip { font-size:0.72rem !important; padding:0.3rem 0.7rem !important; min-height:0; }

    @media (max-width: 991.98px) {
        .pp-search { flex:1 1 100%; max-width:none; }
        body .main-content .pp-select { width:100% !important; }
        .pp-field { flex:1 1 150px; }
    }
</style>
@endpush

<div class="pp-toolbar">
    @php
        // One builder for tabs, selects and Clear: filters stack instead of replacing each
        // other, and any change restarts both pagers. 'all'/'' never rides in the URL.
        $ppUrl = function (array $changes = [], array $unset = []) use ($routeBase) {
            $query = array_merge(request()->query(), $changes);
            foreach ($unset as $key) { unset($query[$key]); }
            unset($query['page'], $query['newborns_page']);
            return route($routeBase . '.index', array_filter(
                $query, fn ($value) => $value !== '' && $value !== null && $value !== 'all'
            ));
        };
        $segments = [
            'all' => ['label' => 'All', 'icon' => 'bi-collection-fill', 'count' => null],
            'mothers' => ['label' => 'Recently Delivered Mothers', 'icon' => 'bi-person-badge-fill', 'count' => $delivered->total()],
            'followups' => ['label' => 'Needs Follow-up', 'icon' => 'bi-exclamation-triangle-fill', 'count' => $dangerVisits->count()],
            'newborns' => ['label' => 'Newborns', 'icon' => 'bi-balloon-heart-fill', 'count' => $newborns->total()],
        ];
        $windows = ['all' => 'Any time', '6w' => 'Up to 6 weeks', '12w' => '6–12 weeks', '6m' => '3–6 months', 'older' => 'Past 6 months'];
        $attentions = ['all' => 'All cases', 'vaccines' => 'Vaccine doses due', 'danger' => 'Danger signs'];
    @endphp

    <nav class="pp-tabs" aria-label="Choose which list to show">
        @foreach($segments as $segmentKey => $segment)
            @if($segmentKey === 'followups' && !$hasRail)
                @continue
            @endif
            <a href="{{ $ppUrl(['view' => $segmentKey]) }}"
               class="pp-filter {{ $view === $segmentKey ? 'active' : '' }}"
               @if($view === $segmentKey) aria-current="page" @endif>
                <i class="bi {{ $segment['icon'] }}"></i> {{ $segment['label'] }}
                @if($segment['count'] !== null)<span class="count">{{ $segment['count'] }}</span>@endif
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route($routeBase . '.index') }}" class="pp-filters">
        {{-- the chosen segment rides along so filtering never throws the view back to "All" --}}
        <input type="hidden" name="view" value="{{ $view }}">
        <span class="pp-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="search" class="form-control" value="{{ $search }}"
                   placeholder="Mother or baby name" aria-label="Search by mother or baby name, email or contact number">
        </span>
        <label class="pp-field">
            <span class="pp-word">Delivery</span>
            <select name="window" class="form-select pp-select" aria-label="Filter by time since delivery" onchange="this.form.submit()">
                @foreach($windows as $windowKey => $windowLabel)
                    <option value="{{ $windowKey }}" {{ $window === $windowKey ? 'selected' : '' }}>{{ $windowLabel }}</option>
                @endforeach
            </select>
        </label>
        <label class="pp-field">
            <span class="pp-word">Month</span>
            <select name="month" class="form-select pp-select" aria-label="Filter by the calendar month of delivery" onchange="this.form.submit()">
                <option value="">All months</option>
                @foreach($monthList as $monthKey => $monthLabel)
                    <option value="{{ $monthKey }}" {{ $month === $monthKey ? 'selected' : '' }}>{{ $monthLabel }}</option>
                @endforeach
            </select>
        </label>
        <label class="pp-field">
            <span class="pp-word">Show</span>
            <select name="attention" class="form-select pp-select" aria-label="Filter by what needs attention" onchange="this.form.submit()">
                @foreach($attentions as $attentionKey => $attentionLabel)
                    <option value="{{ $attentionKey }}" {{ $attention === $attentionKey ? 'selected' : '' }}>{{ $attentionLabel }}</option>
                @endforeach
            </select>
        </label>
        @if($filtering)
            <a href="{{ $ppUrl([], ['search', 'window', 'month', 'attention']) }}" class="btn btn-sm btn-outline-secondary pp-clear">
                <i class="bi bi-x-lg"></i> Clear
            </a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:14px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 align-items-start{{ $view !== 'all' ? ' is-filtered' : '' }}" id="postpartumSections">
    <div class="col-12 col-lg-7{{ $view === 'mothers' ? ' is-active' : '' }}" data-section="mothers">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recently Delivered Mothers</h5>
                    <small class="text-muted">Postpartum tracking starts at delivery</small>
                </div>
                <span class="badge bg-secondary">{{ $delivered->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($delivered as $preg)
                    <a href="{{ route($routeBase . '.show-mother', $preg->user_id) }}" class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom" style="border-color:var(--color-border) !important;color:inherit;">
                        <span class="rc-av" style="width:42px;height:42px;border-radius:50%;background:var(--color-secondary-soft);color:var(--color-secondary-text);display:inline-flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;">{{ strtoupper(substr($preg->patient_name ?? 'M', 0, 1)) }}</span>
                        <span class="flex-grow-1" style="min-width:0;">
                            <span class="d-block fw-bold" style="color:var(--color-text);font-size:.9rem;">{{ $preg->patient_name ?? 'Mother #' . $preg->user_id }}</span>
                            <small class="text-muted">Delivered {{ $preg->delivery_date->format('M j, Y') }} · {{ $preg->newborns->count() }} newborn(s) · {{ $preg->facility_delivery_place ?? 'facility not recorded' }}</small>
                        </span>
                        <i class="bi bi-chevron-right" style="color:var(--color-secondary-text);"></i>
                    </a>
                @empty
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-{{ $filtering ? 'search' : 'balloon-heart' }}" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">{{ $filtering ? 'No mothers match these filters' : 'No deliveries recorded yet' }}</h6>
                        @if($filtering)
                            {{-- Saying "no deliveries" would be wrong: the records exist, the filter hid them. --}}
                            <p class="text-muted small mb-2">Nothing in the delivered list fits the current search or filters.</p>
                            <a href="{{ $ppUrl([], ['search', 'window', 'month', 'attention']) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Clear filters
                            </a>
                        @else
                            <p class="text-muted small">Postpartum tracking activates once a delivery date is saved on a pregnancy.</p>
                        @endif
                    </div>
                @endforelse
            </div>
            @if($delivered->hasPages())
                <div class="px-3 py-2 border-top">{{ $delivered->links() }}</div>
            @endif
        </div>
    </div>

    @if($hasRail)
    <div class="col-12 col-lg-5{{ $view === 'followups' ? ' is-active' : '' }}" data-section="followups">
        <div class="card" id="postpartumFollowUps">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Needs Follow-up</h5>
                    <small class="text-muted">Latest visits flagged on review</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger">{{ $dangerVisits->count() }}</span>
                    @if($dangerVisits->count() > $railLimit)
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="followUpToggle" aria-expanded="false">Show all</button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($dangerVisits as $visit)
                    <a href="{{ route($routeBase . '.show-mother', $visit->user_id) }}" class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom{{ $loop->index >= $railLimit ? ' d-none fu-extra' : '' }}" style="border-color:var(--color-border) !important;color:inherit;">
                        <span style="width:42px;height:42px;border-radius:50%;background:var(--color-danger-soft);color:var(--color-danger-text);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        <span class="flex-grow-1" style="min-width:0;">
                            <span class="d-block fw-bold" style="color:var(--color-text);font-size:.9rem;">{{ $visit->mother?->name ?? 'Mother' }} <small class="text-muted fw-normal">· Week {{ $visit->visit_week }}</small></span>
                            <small class="text-muted d-block">{{ $visit->visit_date->format('M j, Y') }} · {{ implode(' · ', $visit->dangerSignals()) }}</small>
                        </span>
                        <i class="bi bi-chevron-right" style="color:var(--color-secondary-text);"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="col-12{{ $hasRail ? '' : ' col-lg-5' }}{{ $view === 'newborns' ? ' is-active' : '' }}" data-section="newborns">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Newborns</h5>
                    <small class="text-muted">Vitals, danger signs &amp; immunizations</small>
                </div>
                <span class="badge bg-secondary">{{ $newborns->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($newborns as $nb)
                    @php
                        $dueCount = $nb->immunizations->where('status', 'scheduled')->where('scheduled_date', '<=', now()->toDateString())->count();
                    @endphp
                    <a href="{{ route($routeBase . '.show-mother', $nb->mother_id) }}" class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom" style="border-color:var(--color-border) !important;color:inherit;">
                        <span style="width:42px;height:42px;border-radius:12px;background:var(--color-secondary-soft);border:1px solid var(--color-secondary-soft);color:var(--color-secondary-text);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-balloon-fill"></i></span>
                        <span class="flex-grow-1" style="min-width:0;">
                            <span class="d-block fw-bold" style="color:var(--color-text);font-size:.9rem;">{{ $nb->display_name }}</span>
                            <small class="text-muted">Born {{ $nb->birth_date->format('M j, Y') }}{{ $nb->birth_weight_kg ? ' · ' . $nb->birth_weight_kg . ' kg' : '' }}</small>
                            <span class="d-block mt-1">
                                @if($nb->has_danger_signs)<span class="badge bg-danger">Danger signs</span>@endif
                                @if($dueCount > 0)<span class="badge bg-warning text-dark">{{ $dueCount }} vaccine(s) due</span>@endif
                            </span>
                        </span>
                        <i class="bi bi-chevron-right" style="color:var(--color-secondary-text);"></i>
                    </a>
                @empty
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-{{ $filtering ? 'search' : 'emoji-smile' }}" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">{{ $filtering ? 'No newborns match these filters' : 'No newborns logged yet' }}</h6>
                        @if($filtering)
                            <p class="text-muted small mb-2">Try widening the delivery window or clearing the search.</p>
                            <a href="{{ $ppUrl([], ['search', 'window', 'month', 'attention']) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Clear filters
                            </a>
                        @else
                            @unless($isMidwife)<p class="text-muted small">Open a delivered mother and log the birth.</p>@endunless
                        @endif
                    </div>
                @endforelse
            </div>
            @if($newborns->hasPages())
                <div class="px-3 py-2 border-top">{{ $newborns->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('followUpToggle');

    /* The segment tabs are plain links now: the view lives in the URL, so it survives
       a pager click and a shared link instead of hiding in sessionStorage. */
    if (toggle) {
        const extras = document.querySelectorAll('.fu-extra');
        toggle.addEventListener('click', function () {
            const expand = toggle.getAttribute('aria-expanded') !== 'true';
            extras.forEach(function (row) { row.classList.toggle('d-none', ! expand); });
            toggle.setAttribute('aria-expanded', expand ? 'true' : 'false');
            toggle.textContent = expand ? 'Show {{ $railLimit }} most recent' : 'Show all';
        });
    }
});
</script>
@endpush
@endsection
