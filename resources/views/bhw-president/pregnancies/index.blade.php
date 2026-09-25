@extends('bhw-president.layout')

@section('title', 'Pregnancy Review Queue - BHW President Portal | ReproCare')

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Pregnancy Review Queue</div>
            <p class="page-hero-subtitle">Review submissions from BHWs — {{ $pendingCount }} awaiting your decision</p>
        </div>
        <a href="{{ route('bhw-president.dashboard') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('bhw-president.pregnancies.index', ['filter' => 'pending']) }}"
               class="btn btn-sm {{ $filter === 'pending' ? 'btn-warning text-white' : 'btn-outline-secondary' }}">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('bhw-president.pregnancies.index', ['filter' => 'all']) }}"
               class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                All Active
            </a>
            <a href="{{ route('bhw-president.pregnancies.index', ['filter' => 'high-risk']) }}"
               class="btn btn-sm {{ $filter === 'high-risk' ? 'btn-danger' : 'btn-outline-secondary' }}">
                High-Risk
            </a>
        </div>
        <form method="GET" action="{{ route('bhw-president.pregnancies.index') }}" class="d-flex gap-2">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search patient..."
                   value="{{ $search }}" style="border-radius:10px;">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
    <div class="card-body">
        @if($pregnancies->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>EDD</th>
                        <th>AOG</th>
                        <th>Risk</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pregnancies as $pregnancy)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <x-patient-avatar :patient="$pregnancy->woman" :size="32" />
                                <div>
                                    <div style="font-weight:500;">{{ $pregnancy->patient_name ?? optional($pregnancy->woman)->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ optional($pregnancy->woman)->barangay ?? $pregnancy->walkInPatient?->barangay ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ optional($pregnancy->edd)?->format('M j, Y') ?? 'N/A' }}</td>
                        <td>{{ $pregnancy->aog_weeks ?? $pregnancy->gestational_age ?? 'N/A' }} wks</td>
                        <td>
                            <span class="badge bg-{{ ($pregnancy->risk_level ?? 'Low') === 'High' ? 'danger' : (($pregnancy->risk_level ?? 'Low') === 'Medium' ? 'warning' : 'success') }}">
                                {{ $pregnancy->risk_level ?? 'Low' }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ str_replace('_', ' ', $pregnancy->workflow_status ?? '—') }}</small></td>
                        <td>
                            <a href="{{ route('bhw-president.pregnancies.review', $pregnancy->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i> Review
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $pregnancies->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size:3rem; color:var(--color-success-text);"></i>
            <h5 class="mt-3 text-muted">Queue Clear</h5>
            <p class="text-muted">No pregnancies match this filter.</p>
        </div>
        @endif
    </div>
</div>

@endsection
