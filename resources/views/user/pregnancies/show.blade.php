@extends('user.layout')

@section('title', 'Pregnancy Details - ReproCare')

@section('user-content')
<div class="pregnancy-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <div class="page-title">Pregnancy Details</div>
            <p class="page-subtitle">LMP {{ optional($pregnancy->lmp)->format('M j, Y') ?? '—' }} · EDD {{ optional($pregnancy->edd)->format('M j, Y') ?? '—' }}</p>
        </div>
        <a href="{{ route('user.pregnancies.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> My Pregnancies</a>
    </div>

    <div class="preg-hero">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="preg-pill-row">
                <span class="preg-pill preg-trim">{{ $pregnancy->trimester_name ?? '—' }}</span>
                <span class="preg-pill {{ ($pregnancy->risk_level ?? 'Low') === 'High' ? 'preg-pill-risk-high' : (($pregnancy->risk_level ?? '') === 'Medium' ? 'preg-pill-risk-medium' : 'preg-pill-risk-low') }}">{{ $pregnancy->risk_level ?? 'Low' }} risk</span>
                @if($pregnancy->ended_at)
                    <span class="preg-pill preg-grav">Completed</span>
                @else
                    <span class="preg-pill preg-para">Active</span>
                @endif
            </div>
        </div>
        <p class="preg-hero-sub mt-2 mb-0">
            <strong>{{ $pregnancy->formatted_aog ?? '' }}</strong>
            @if($maternalCare)
                · Gravida {{ $maternalCare->gravida ?? '—' }}, Para {{ $maternalCare->parity ?? '—' }}
            @endif
        </p>
        @if($nextCheckup)
            <p class="preg-hero-sub mb-0">Next checkup: <strong>{{ optional($nextCheckup->scheduled_date)->format('M j, Y') }}</strong>{{ $nextCheckup->purpose ? ' — ' . e($nextCheckup->purpose) : '' }}</p>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6 class="fw-bold mb-3">Recent Health Records</h6>
                @forelse($pregnancy->healthRecords->take(5) as $record)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ optional($record->created_at)->format('M j, Y') }} · BP {{ $record->bp ?? '—' }}</span>
                        <span class="badge bg-light text-dark border">{{ $record->risk_level ?? '—' }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No health records linked yet.</p>
                @endforelse
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6 class="fw-bold mb-3">Upcoming Checkups</h6>
                @forelse($checkups->take(5) as $checkup)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ optional($checkup->scheduled_date)->format('M j, Y') }}{{ $checkup->purpose ? ' — ' . e($checkup->purpose) : '' }}</span>
                        <span class="badge bg-light text-dark border">{{ $checkup->status }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No checkups scheduled.</p>
                @endforelse
            </div></div>
        </div>
    </div>

    <div class="card mt-3"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div><h6 class="fw-bold mb-1">Postpartum Family-Planning Plan</h6>
        @if($postpartumPlan)<p class="text-muted mb-0">{{ $postpartumPlan->preferred_method ? 'Preferred method: ' . \App\Models\FamilyPlanningRecord::methodLabel($postpartumPlan->preferred_method) . '. ' : '' }}Start: {{ ucfirst(str_replace('_', ' ', $postpartumPlan->postpartum_start_timing ?? 'undecided')) }}.</p>
        @else <p class="text-muted mb-0">Plan your preferred method for after delivery with your care team.</p>@endif</div>
        <a href="{{ $postpartumPlan ? route('user.family-planning.index') : route('user.family-planning.create') }}" class="btn btn-outline-primary btn-sm">{{ $postpartumPlan ? 'View plan' : 'Create plan' }}</a>
    </div></div>

    @if($pregnancy->milestones)
    <div class="card mt-3"><div class="card-body">
        <h6 class="fw-bold mb-3">Milestones</h6>
        @foreach($pregnancy->milestones as $milestone)
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>{{ $milestone['name'] }} (Week {{ $milestone['week'] }})</span>
                <span class="badge bg-light text-dark border">{{ ucfirst($milestone['status']) }}</span>
            </div>
        @endforeach
    </div></div>
    @endif
</div>
@endsection
