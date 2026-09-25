@extends(request()->routeIs('midwife.postpartum.*') ? 'midwife.layout' : 'bhw.layout')

@section('title', 'Postpartum & Newborn Monitoring - ReproCare')

@php
    $contentSection = request()->routeIs('midwife.postpartum.*') ? 'midwife-content' : 'bhw-content';
    $routeBase = request()->routeIs('midwife.postpartum.*') ? 'midwife.postpartum' : 'bhw.postpartum';
    $isMidwife = request()->routeIs('midwife.postpartum.*');
@endphp

@section($contentSection)
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Postpartum &amp; Newborn Monitoring</div>
            <p class="page-hero-subtitle mb-0">Activated once a delivery date is recorded — prenatal → delivery → postnatal in one view.</p>
        </div>
        @unless($isMidwife)
        <span class="summary-chip chip-primary"><i class="bi bi-house-heart-fill"></i> House-to-house toolkit</span>
        @endunless
    </div>
    @if($dangerVisits->count() > 0)
    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3" style="border-top:1px solid var(--color-border);">
        <span class="summary-chip chip-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ $dangerVisits->count() }} visit(s) flagged for follow-up</span>
    </div>
    @endif
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
            <div class="card-header">
                <h5>Recently Delivered Mothers</h5>
                <small class="text-muted">Postpartum tracking starts at delivery</small>
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
                <div class="px-3 py-2 border-top">{{ $delivered->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5>Newborns</h5>
                <small class="text-muted">Vitals, danger signs &amp; immunizations</small>
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
                <div class="px-3 py-2 border-top">{{ $newborns->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
