@extends(request()->routeIs('midwife.postpartum.*') ? 'midwife.layout' : 'bhw.layout')

@section('title', 'Postpartum Record - ReproCare')

@php
    $contentSection = request()->routeIs('midwife.postpartum.*') ? 'midwife-content' : 'bhw-content';
    $routeBase = request()->routeIs('midwife.postpartum.*') ? 'midwife.postpartum' : 'bhw.postpartum';
@endphp

@section($contentSection)
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">{{ $mother->name }}</div>
            <p class="page-hero-subtitle mb-0">Postpartum journey: mother checks, newborns &amp; immunizations.</p>
        </div>
        <a href="{{ route($routeBase . '.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i> Back</a>
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
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Maternal Postpartum Checks</h5>
                @if(!request()->routeIs('midwife.postpartum.*'))
                <a href="{{ route($routeBase . '.visit-create', ['mother_id' => $mother->id]) }}" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;"><i class="bi bi-plus-lg"></i> Log Visit</a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($mother->postpartumVisits as $visit)
                    <div class="p-3 border-bottom" style="border-color:var(--color-border) !important;">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <strong style="color:var(--color-text);font-size:.9rem;">Week {{ $visit->visit_week }} · {{ $visit->visit_date->format('M j, Y') }}</strong>
                            @if($visit->has_danger_signs)
                                <span class="badge bg-danger">Needs follow-up</span>
                            @else
                                <span class="badge bg-success">Stable</span>
                            @endif
                        </div>
                        <small class="text-muted d-block mt-1">
                            BP {{ $visit->bp ?? '—' }} · Temp {{ $visit->temperature ?? '—' }}°C · Bleeding: {{ ucfirst($visit->bleeding) }}
                            · Breastfeeding: {{ ucfirst($visit->breastfeeding) }}
                            @if(!is_null($visit->depression_score)) · Mood screen: {{ $visit->depression_score }}/9 @endif
                        </small>
                        @if($visit->infection_signs)<small class="d-block mt-1" style="color:var(--color-danger-text);"><i class="bi bi-exclamation-circle me-1"></i>{{ $visit->infection_signs }}</small>@endif
                        @if($visit->notes)<small class="text-muted d-block mt-1">{{ $visit->notes }}</small>@endif
                        <small class="text-muted d-block mt-1">By {{ $visit->recordedBy?->name ?? '—' }}</small>
                    </div>
                @empty
                    <p class="text-muted small p-3 mb-0">No postpartum visits logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Newborns &amp; Immunizations</h5>
                @if(!request()->routeIs('midwife.postpartum.*'))
                <a href="{{ route($routeBase . '.newborn-create', ['mother_id' => $mother->id]) }}" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;"><i class="bi bi-plus-lg"></i> Log Birth</a>
                @endif
            </div>
            <div class="card-body">
                @forelse($mother->newborns as $nb)
                    <div class="p-3 rounded-3 mb-3" style="background:var(--color-bg);border:1px solid var(--color-border);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <strong style="color:var(--color-text);">{{ $nb->display_name }}</strong>
                            <span>
                                @if($nb->has_danger_signs)<span class="badge bg-danger me-1">Danger signs</span>@endif
                                <span class="badge bg-light text-dark border">{{ $nb->birth_weight_kg ? $nb->birth_weight_kg . ' kg' : 'weight n/a' }} · {{ ucfirst(str_replace('_', ' ', $nb->feeding_type)) }}</span>
                            </span>
                        </div>
                        <small class="text-muted">Born {{ $nb->birth_date->format('M j, Y') }}{{ $nb->sex ? ' · ' . ucfirst($nb->sex) : '' }}</small>
                        @if($nb->danger_signs)<div class="mt-1" style="font-size:.82rem;color:var(--color-danger-text);"><i class="bi bi-exclamation-triangle me-1"></i>{{ $nb->danger_signs }}</div>@endif

                        <div class="mt-2 fw-bold" style="font-size:.8rem;color:var(--color-text);">Immunization Tracker</div>
                        @foreach($nb->immunizations as $im)
                            <form method="POST" action="{{ route($routeBase . '.immunize', $im->id) }}" class="d-flex align-items-center gap-2 py-1 border-bottom" style="border-color:var(--color-border) !important;">
                                @csrf
                                <span style="font-size:.83rem;flex:1;color:var(--color-text);">{{ $im->vaccine }} <small class="text-muted">· due {{ $im->scheduled_date->format('M j, Y') }}</small></span>
                                @if($im->status === 'given')
                                    <span class="badge bg-success">Given {{ $im->given_date?->format('M j') }}</span>
                                @else
                                    @if($im->is_overdue)<span class="badge bg-warning text-dark">Overdue</span>@endif
                                    <select name="status" class="form-select form-select-sm" style="width:auto;border-radius:8px;" onchange="this.form.submit()">
                                        <option value="scheduled" {{ $im->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="given">Mark given</option>
                                        <option value="missed">Mark missed</option>
                                    </select>
                                @endif
                            </form>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted small mb-0">No newborns recorded for this mother yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
