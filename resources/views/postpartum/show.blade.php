@extends(request()->routeIs('midwife.postpartum.*') ? 'midwife.layout' : 'bhw.layout')

@section('title', 'Postpartum Record - ReproCare')

@php
    $contentSection = request()->routeIs('midwife.postpartum.*') ? 'midwife-content' : 'bhw-content';
    $routeBase = request()->routeIs('midwife.postpartum.*') ? 'midwife.postpartum' : 'bhw.postpartum';
    $isMidwife = request()->routeIs('midwife.postpartum.*');

    $visits = $mother->postpartumVisits->sortByDesc('visit_date');
    $delivery = $mother->pregnancies->whereNotNull('delivery_date')->sortByDesc('delivery_date')->first();
    $daysPostpartum = $delivery ? (int) $delivery->delivery_date->diffInDays(now()) : null;
    $weeksPostpartum = $daysPostpartum !== null ? intdiv($daysPostpartum, 7) : null;
    $flaggedVisits = $visits->filter->has_danger_signs->count();
    $doses = $mother->newborns->flatMap(fn ($nb) => $nb->immunizations);
    $dosesGiven = $doses->where('status', 'given')->count();
    $dosesOverdue = $doses->reject(fn ($dose) => $dose->status === 'given')->filter->is_overdue->count();
    $newbornFlags = $mother->newborns->filter->has_danger_signs->count();

    // Kept out of the markup: a Blade directive welded to the next word ("@elseNo")
    // is parsed as an unknown directive name and prints literally.
    $visitHint = $isMidwife
        ? 'Nothing has been filed for this mother yet.'
        : 'Use Log Visit to record the week-1 check.';
    $newbornHint = $isMidwife
        ? 'No birth has been filed for this mother yet.'
        : 'Use Log Birth to file the delivery and generate the vaccine schedule.';
@endphp

@section($contentSection)
@push('styles')
<style>
    /* Mirrors the .record-summary idiom from the CHO patient page; the app has no
       global version of these classes. */
    .pp-summary .summary-item { padding:0.85rem 1.1rem; border-right:1px solid var(--color-border); min-width:0; }
    .pp-summary .summary-item:last-child { border-right:0; }
    .pp-label { font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:2px; line-height:1; }
    .pp-value { font-size:0.84rem; font-weight:700; line-height:1.35; color:var(--color-text); }
    .pp-metric { display:inline-flex; align-items:center; gap:0.25rem; font-size:0.72rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px; background:var(--color-surface-soft); color:var(--color-text-muted); border:1px solid var(--color-border); }
    .pp-metric.is-alert { background:var(--color-danger-soft); color:var(--color-danger-text); border-color:var(--color-danger-border); }
    .pp-check + .pp-check, .pp-immun + .pp-immun { border-top:1px solid var(--color-border); }
    .pp-immun { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; padding:0.45rem 0; }
    @media (max-width: 767.98px) {
        /* Four stacked cells pushed the actual checks below a phone fold; 2x2 keeps
           the summary above the fold without losing the rules between cells. */
        .pp-summary .summary-item { border-right:0; }
        .pp-summary .summary-item:nth-child(odd) { border-right:1px solid var(--color-border); }
        .pp-summary .summary-item:nth-child(-n+2) { border-bottom:1px solid var(--color-border); }
    }
</style>
@endpush

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3" style="min-width:0;">
            <span class="rc-av" style="width:48px;height:48px;border-radius:50%;background:var(--color-secondary-soft);color:var(--color-secondary-text);display:inline-flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;">{{ strtoupper(substr($mother->name ?? 'M', 0, 1)) }}</span>
            <div style="min-width:0;">
                <div class="page-hero-title">{{ $mother->name }}</div>
                <p class="page-hero-subtitle mb-0">
                    Postpartum record · File #{{ $mother->id }}
                    @if($mother->barangay) · Barangay {{ $mother->barangay }} @endif
                    @if($weeksPostpartum !== null) · Week {{ $weeksPostpartum }} postpartum @endif
                </p>
            </div>
        </div>
        <a href="{{ route($routeBase . '.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i> Back to list</a>
    </div>
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

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">At a Glance</h5></div>
    <div class="card-body p-0 pp-summary">
        <div class="row g-0">
            <div class="col-6 col-md-3 summary-item">
                <div class="pp-label">Delivery</div>
                @if($delivery)
                    <div class="pp-value">{{ $delivery->delivery_date->format('M j, Y') }}</div>
                    <small class="text-muted">{{ $weeksPostpartum }} week(s) ago · {{ $delivery->facility_delivery_place ?? 'facility not recorded' }}</small>
                @else
                    <div class="pp-value">No delivery recorded</div>
                    <small class="text-muted">Postpartum tracking starts at delivery.</small>
                @endif
            </div>
            <div class="col-6 col-md-3 summary-item">
                <div class="pp-label">Maternal checks</div>
                <div class="pp-value">{{ $visits->count() }} visit(s) logged</div>
                <small class="text-muted">
                    @if($visits->isNotEmpty())Latest week {{ $visits->first()->visit_week }} · @endif
                    @if($flaggedVisits > 0)<span style="color:var(--color-danger-text);font-weight:700;">{{ $flaggedVisits }} flagged</span>@else<span style="color:var(--color-success-text);">No flags</span>@endif
                </small>
            </div>
            <div class="col-6 col-md-3 summary-item">
                <div class="pp-label">Newborns</div>
                <div class="pp-value">{{ $mother->newborns->count() }} recorded</div>
                <small class="text-muted">
                    @if($newbornFlags > 0)<span style="color:var(--color-danger-text);font-weight:700;">{{ $newbornFlags }} with danger signs</span>@else No danger signs @endif
                </small>
            </div>
            <div class="col-6 col-md-3 summary-item">
                <div class="pp-label">Immunizations</div>
                <div class="pp-value">{{ $dosesGiven }} of {{ $doses->count() }} doses given</div>
                <small class="text-muted">
                    @if($dosesOverdue > 0)<span style="color:var(--color-warning-text);font-weight:700;">{{ $dosesOverdue }} overdue</span>@else Nothing overdue @endif
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 align-items-start">
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Maternal Postpartum Checks</h5>
                    <small class="text-muted">Week-by-week mother assessments</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">{{ $visits->count() }}</span>
                    @unless($isMidwife)
                    <a href="{{ route($routeBase . '.visit-create', ['mother_id' => $mother->id]) }}" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;"><i class="bi bi-plus-lg"></i> Log Visit</a>
                    @endunless
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($visits as $visit)
                    @php $flags = $visit->dangerSignals(); @endphp
                    <div class="pp-check p-3">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <strong style="color:var(--color-text);font-size:.9rem;">Week {{ $visit->visit_week }} · {{ $visit->visit_date->format('M j, Y') }}</strong>
                            <span class="badge bg-{{ $flags ? 'danger' : 'success' }}">{{ $flags ? 'Needs follow-up' : 'Stable' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="pp-metric{{ isset($flags['bp']) ? ' is-alert' : '' }}">BP {{ $visit->bp ?? '—' }}</span>
                            <span class="pp-metric{{ isset($flags['temperature']) ? ' is-alert' : '' }}">Temp {{ $visit->temperature ?? '—' }}°C</span>
                            <span class="pp-metric{{ isset($flags['bleeding']) ? ' is-alert' : '' }}">Bleeding: {{ ucfirst($visit->bleeding) }}</span>
                            <span class="pp-metric">Feeding: {{ ucfirst(str_replace('_', ' ', $visit->breastfeeding)) }}</span>
                            @if(!is_null($visit->depression_score))
                                <span class="pp-metric{{ isset($flags['mood']) ? ' is-alert' : '' }}">Mood {{ $visit->depression_score }}/9</span>
                            @endif
                        </div>
                        @if($flags)
                            <div class="mt-2" style="font-size:.8rem;color:var(--color-danger-text);"><i class="bi bi-exclamation-triangle me-1"></i>{{ implode(' · ', $flags) }}</div>
                        @endif
                        @if($visit->infection_signs)<small class="d-block mt-1" style="font-size:.8rem;color:var(--color-danger-text);"><i class="bi bi-exclamation-circle me-1"></i>{{ $visit->infection_signs }}</small>@endif
                        @if($visit->notes)<small class="text-muted d-block mt-1">{{ $visit->notes }}</small>@endif
                        <small class="text-muted d-block mt-1">Logged by {{ $visit->recordedBy?->name ?? '—' }}</small>
                    </div>
                @empty
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-clipboard2-pulse" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">No visits logged yet</h6>
                        <p class="text-muted small">{{ $visitHint }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($postpartumPlans->isNotEmpty())
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Postpartum Family-Planning Plan</h5>
                    <small class="text-muted">Method agreed after delivery</small>
                </div>
                <span class="badge bg-secondary">{{ $postpartumPlans->count() }}</span>
            </div>
            <div class="card-body p-0">
                @foreach($postpartumPlans as $plan)
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap p-3{{ !$loop->last ? ' border-bottom' : '' }}" style="border-color:var(--color-border) !important;">
                        <div style="min-width:0;">
                            <strong style="color:var(--color-text);font-size:.9rem;">{{ $plan->method_given ? \App\Models\FamilyPlanningRecord::methodLabel($plan->method_given) : ($plan->preferred_method ? \App\Models\FamilyPlanningRecord::methodLabel($plan->preferred_method) : 'Counseling requested') }}</strong>
                            <small class="text-muted d-block">Planned start: {{ ucfirst(str_replace('_', ' ', $plan->postpartum_start_timing ?? 'undecided')) }} · Status: {{ ucfirst($plan->status) }}</small>
                        </div>
                        <a class="btn btn-sm btn-outline-primary flex-shrink-0" href="{{ route($isMidwife ? 'midwife.family-planning.edit' : 'bhw.family-planning.edit', $plan) }}">Review plan</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Newborns &amp; Immunizations</h5>
                    <small class="text-muted">Birth details, danger signs &amp; vaccine schedule</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">{{ $mother->newborns->count() }}</span>
                    @unless($isMidwife)
                    <a href="{{ route($routeBase . '.newborn-create', ['mother_id' => $mother->id]) }}" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;"><i class="bi bi-plus-lg"></i> Log Birth</a>
                    @endunless
                </div>
            </div>
            <div class="card-body">
                @forelse($mother->newborns as $nb)
                    <div class="p-3 rounded-3 mb-3" style="background:var(--color-surface-soft);border:1px solid var(--color-border);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <strong style="color:var(--color-text);">{{ $nb->display_name }}</strong>
                            <span class="d-flex flex-wrap gap-1">
                                @if($nb->has_danger_signs)<span class="badge bg-danger">Danger signs</span>@endif
                                <span class="badge bg-secondary">{{ $nb->birth_weight_kg ? $nb->birth_weight_kg . ' kg' : 'weight n/a' }}</span>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $nb->feeding_type)) }}</span>
                            </span>
                        </div>
                        <small class="text-muted">Born {{ $nb->birth_date->format('M j, Y') }}{{ $nb->sex ? ' · ' . ucfirst($nb->sex) : '' }}</small>
                        @if($nb->danger_signs)<div class="mt-1" style="font-size:.82rem;color:var(--color-danger-text);"><i class="bi bi-exclamation-triangle me-1"></i>{{ $nb->danger_signs }}</div>@endif

                        <div class="mt-3 fw-bold" style="font-size:.8rem;color:var(--color-text);">Immunization Tracker</div>
                        @forelse($nb->immunizations as $im)
                            <form method="POST" action="{{ route($routeBase . '.immunize', $im->id) }}" class="pp-immun">
                                @csrf
                                <span style="flex:1 1 10rem;min-width:0;font-size:.83rem;color:var(--color-text);">{{ $im->vaccine }} <small class="text-muted">· due {{ $im->scheduled_date ? $im->scheduled_date->format('M j, Y') : 'not scheduled' }}</small></span>
                                @if($im->status === 'given')
                                    <span class="badge bg-success">Given {{ $im->given_date?->format('M j') }}</span>
                                @else
                                    @if($im->is_overdue)<span class="badge bg-warning">Overdue</span>@endif
                                    <label class="d-flex align-items-center gap-1 mb-0">
                                        <span class="visually-hidden">{{ $im->vaccine }} status</span>
                                        <select name="status" class="form-select form-select-sm" style="width:auto;font-size:.75rem;border-radius:8px;padding:.2rem 1.6rem .2rem .55rem;" onchange="this.form.submit()">
                                            <option value="scheduled" {{ $im->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="given">Mark given</option>
                                            <option value="missed">Mark missed</option>
                                        </select>
                                    </label>
                                @endif
                            </form>
                        @empty
                            <small class="text-muted d-block pt-2">No vaccine schedule generated for this newborn yet.</small>
                        @endforelse
                    </div>
                @empty
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-balloon-heart" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">No newborns recorded yet</h6>
                        <p class="text-muted small">{{ $newbornHint }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
