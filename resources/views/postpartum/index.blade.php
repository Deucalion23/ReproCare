@extends(request()->routeIs('midwife.postpartum.*') ? 'midwife.layout' : 'bhw.layout')

@section('title', 'Postpartum & Newborn Monitoring - ReproCare')

@php
    $contentSection = request()->routeIs('midwife.postpartum.*') ? 'midwife-content' : 'bhw-content';
    $routeBase = request()->routeIs('midwife.postpartum.*') ? 'midwife.postpartum' : 'bhw.postpartum';
    $isMidwife = request()->routeIs('midwife.postpartum.*');
    $railLimit = 5;
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
            @if($dangerVisits->count() > 0)
                <a href="#postpartumFollowUps" class="summary-chip chip-danger text-decoration-none"><i class="bi bi-exclamation-triangle-fill"></i> {{ $dangerVisits->count() }} visit(s) flagged</a>
            @else
                <span class="summary-chip chip-success"><i class="bi bi-shield-check"></i> No flagged visits</span>
            @endif
            @unless($isMidwife)
            <span class="summary-chip chip-primary"><i class="bi bi-house-heart-fill"></i> House-to-house toolkit</span>
            @endunless
        </div>
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

<div class="row g-4 align-items-start">
    <div class="col-lg-7">
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
                        <i class="bi bi-balloon-heart" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">No deliveries recorded yet</h6>
                        <p class="text-muted small">Postpartum tracking activates once a delivery date is saved on a pregnancy.</p>
                    </div>
                @endforelse
            </div>
            @if($delivered->hasPages())
                <div class="px-3 py-2 border-top">{{ $delivered->links() }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        @if($dangerVisits->count() > 0)
        <div class="card mb-4" id="postpartumFollowUps">
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
        @endif

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
                        <i class="bi bi-emoji-smile" style="font-size:2.4rem;color:var(--color-secondary-text);"></i>
                        <h6 class="fw-bold mt-2" style="color:var(--color-text);">No newborns logged yet</h6>
                        @unless($isMidwife)<p class="text-muted small">Open a delivered mother and log the birth.</p>@endunless
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
    if (! toggle) return;
    const extras = document.querySelectorAll('.fu-extra');
    toggle.addEventListener('click', function () {
        const expand = toggle.getAttribute('aria-expanded') !== 'true';
        extras.forEach(function (row) { row.classList.toggle('d-none', ! expand); });
        toggle.setAttribute('aria-expanded', expand ? 'true' : 'false');
        toggle.textContent = expand ? 'Show {{ $railLimit }} most recent' : 'Show all';
    });
});
</script>
@endpush
@endsection
