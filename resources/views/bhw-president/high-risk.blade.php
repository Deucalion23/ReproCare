@extends('bhw-president.layout')

@section('title', 'High-Risk Pregnancies - BHW President Portal | ReproCare')

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .page-hero { padding:0.9rem 1rem !important; border-radius:14px !important; margin-bottom:0.9rem !important; }
        .page-hero .page-hero-title { font-size:1.02rem !important; }
        .page-hero .page-hero-subtitle { font-size:0.74rem !important; }
        .card-header { padding:0.7rem 0.85rem !important; }
        .card-header h5 { font-size:0.85rem !important; }
        .card-body { padding:0.75rem 0.85rem !important; }
        .table { font-size:0.76rem !important; }
        .table th, .table td { padding:0.5rem 0.6rem !important; }
        .table .hr-patient-name { white-space:nowrap !important; font-size:0.78rem !important; }
        .table .hr-patient-brgy { white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important; max-width:7rem; font-size:0.66rem !important; }
        .table .badge { font-size:0.62rem !important; white-space:nowrap !important; }
        .table .btn { font-size:0.7rem !important; padding:0.35rem 0.65rem !important; white-space:nowrap !important; }
        .text-center.py-5 { padding-top:1.5rem !important; padding-bottom:1.5rem !important; }
        .text-center h4 { font-size:1rem !important; }
        .text-center p { font-size:0.76rem !important; }
    }
</style>
@endpush

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">High-Risk Pregnancies</div>
            <p class="page-hero-subtitle">Monitor pregnancies requiring special attention</p>
        </div>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-header">
        <h5 class="mb-0">High-Risk Cases ({{ $highRiskPregnancies->total() }})</h5>
    </div>
    <div class="card-body">
        @if($highRiskPregnancies->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-cards-mobile">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>EDD</th>
                        <th>Gestational Age</th>
                        <th>Risk Factors</th>
                        <th>Last Checkup</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($highRiskPregnancies as $pregnancy)
                    <tr>
                        <td class="no-card-label" data-label="Patient">
                            <div class="d-flex align-items-center gap-2">
                                <x-patient-avatar :patient="$pregnancy->woman" :size="32" />
                                <div style="min-width:0;">
                                    <div class="hr-patient-name" style="font-weight:500;">{{ optional($pregnancy->woman)->name ?? 'Unknown' }}</div>
                                    <small class="text-muted hr-patient-brgy d-block">{{ optional($pregnancy->woman)->barangay ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="EDD" style="white-space:nowrap;">{{ optional($pregnancy->edd)?->format('M j, Y') ?? 'N/A' }}</td>
                        <td data-label="Gestational Age" style="white-space:nowrap;">{{ $pregnancy->gestational_age ?? 'N/A' }} weeks</td>
                        <td data-label="Risk Factors">
                            <span class="badge bg-danger">High Risk</span>
                            @if($pregnancy->is_high_risk)
                            <span class="badge bg-warning ms-1">Flagged</span>
                            @endif
                        </td>
                        <td data-label="Last Checkup" style="white-space:nowrap;">{{ optional($pregnancy->checkups->first())->scheduled_date?->format('M j, Y') ?? 'No checkups' }}</td>
                        <td class="no-card-label" data-label="Actions">
                            @if($pregnancy->woman)
                            <a href="{{ route('profile.view', $pregnancy->woman->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $highRiskPregnancies->links() }}
        @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size:3rem;color:var(--success);"></i>
            <h4 class="mt-3">No High-Risk Pregnancies</h4>
            <p class="text-muted">All active pregnancies are currently low-risk.</p>
        </div>
        @endif
    </div>
</div>

@endsection
