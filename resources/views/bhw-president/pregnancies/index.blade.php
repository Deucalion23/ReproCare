@extends('bhw-president.layout')

@section('title', 'Pregnancy Review Queue - BHW President Portal | ReproCare')

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .page-hero { padding:0.9rem 1rem !important; border-radius:14px !important; margin-bottom:0.9rem !important; }
        .page-hero .page-hero-title { font-size:1.02rem !important; }
        .page-hero .page-hero-subtitle { font-size:0.74rem !important; }
        .preg-queue-tabs { display:grid !important; grid-template-columns:repeat(3, minmax(0, 1fr)) !important; gap:0 !important; width:100% !important; align-items:stretch !important; }
        .preg-queue-tabs .btn {
            font-size:0.72rem !important; font-weight:800 !important; line-height:1.2 !important;
            padding:0.55rem 0.4rem !important; min-height:2.5rem !important;
            width:100% !important; text-align:center !important;
            display:inline-flex !important; align-items:center !important; justify-content:center !important;
            border-radius:0 !important; margin-left:-1px !important; white-space:nowrap !important;
        }
        .preg-queue-tabs .btn:first-child { border-radius:10px 0 0 10px !important; margin-left:0 !important; }
        .preg-queue-tabs .btn:last-child { border-radius:0 10px 10px 0 !important; }
        .preg-queue-search { width:100% !important; }
        .preg-queue-search .form-control { font-size:0.78rem !important; min-height:2.5rem; }
        .card-header { padding:0.75rem 0.9rem !important; gap:0.6rem !important; }
        .table { font-size:0.76rem !important; }
        .table th, .table td { padding:0.5rem 0.6rem !important; }
        .table .preg-patient-name { white-space:nowrap !important; font-size:0.78rem !important; }
        .table .preg-patient-brgy { white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important; max-width:7rem; font-size:0.66rem !important; }
        .table td.preg-nowrap, .table th.preg-nowrap { white-space:nowrap !important; }
        .table .badge { font-size:0.62rem !important; white-space:nowrap !important; }
        .table small { font-size:0.66rem !important; white-space:nowrap !important; }
        .table .btn { font-size:0.7rem !important; padding:0.35rem 0.65rem !important; white-space:nowrap !important; }
    }
</style>
@endpush

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Pregnancy Review Queue</div>
            <p class="page-hero-subtitle">Review submissions from BHWs — {{ $pendingCount }} awaiting your decision</p>
        </div>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 preg-queue-tabs">
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
        <form method="GET" action="{{ route('bhw-president.pregnancies.index') }}" class="d-flex gap-2 preg-queue-search">
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
                                <div style="min-width:0;">
                                    <div class="preg-patient-name" style="font-weight:500;">{{ $pregnancy->patient_name ?? optional($pregnancy->woman)->name ?? 'Unknown' }}</div>
                                    <small class="text-muted preg-patient-brgy d-block">{{ optional($pregnancy->woman)->barangay ?? $pregnancy->walkInPatient?->barangay ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="preg-nowrap">{{ optional($pregnancy->edd)?->format('M j, Y') ?? 'N/A' }}</td>
                        <td class="preg-nowrap">{{ $pregnancy->aog_weeks ?? $pregnancy->gestational_age ?? 'N/A' }} wks</td>
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
