@extends('midwife.layout')

@section('title', 'Risk Alerts - Midwife Portal | ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div style="position:relative;z-index:1;">
            <div class="page-hero-title">Risk Alerts</div>
            <p class="page-hero-subtitle">Clinical review queue — high-risk pregnancies, urgent referrals, missed checkups, records awaiting validation.</p>
        </div>
    </div>

    {{-- High-risk pregnancies --}}
    <div class="card fade-in-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">High-Risk Pregnancies ({{ $highRiskPregnancies->total() }})</h5>
            <a href="{{ route('midwife.pregnant-patients') }}" class="btn btn-sm btn-outline-primary">Open list</a>
        </div>
        <div class="card-body p-0">
            @if($highRiskPregnancies->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Patient</th><th>LMP</th><th>EDD</th><th>Risk</th><th></th></tr></thead>
                        <tbody>
                            @foreach($highRiskPregnancies as $pregnancy)
                                <tr>
                                    <td>{{ $pregnancy->patient_name ?? optional($pregnancy->woman)->name ?? '—' }}</td>
                                    <td>{{ $pregnancy->lmp?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $pregnancy->edd?->format('M d, Y') ?? '—' }}</td>
                                    <td><span class="badge bg-danger">High Risk</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('midwife.pregnancy-history', $pregnancy->id) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $highRiskPregnancies->appends(request()->except('pregnancies_page'))->links() }}</div>
            @else
                <p class="text-muted text-center py-4 mb-0">No high-risk pregnancies right now.</p>
            @endif
        </div>
    </div>

    {{-- Urgent / emergency referrals --}}
    <div class="card fade-in-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Urgent Referrals ({{ $urgentReferrals->total() }})</h5>
            <a href="{{ route('midwife.referrals.index') }}" class="btn btn-sm btn-outline-primary">Open referrals</a>
        </div>
        <div class="card-body p-0">
            @if($urgentReferrals->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Patient</th><th>Urgency</th><th>Status</th><th>From BHW</th><th></th></tr></thead>
                        <tbody>
                            @foreach($urgentReferrals as $referral)
                                <tr>
                                    <td>{{ $referral->patient_name ?? '—' }}</td>
                                    <td><span class="badge {{ $referral->urgency === 'emergency' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ ucfirst($referral->urgency) }}</span></td>
                                    <td>{{ ucfirst($referral->status) }}</td>
                                    <td>{{ $referral->referredByBhw?->name ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('midwife.referrals.show', $referral->id) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $urgentReferrals->appends(request()->except('referrals_page'))->links() }}</div>
            @else
                <p class="text-muted text-center py-4 mb-0">No urgent referrals pending.</p>
            @endif
        </div>
    </div>

    {{-- Missed checkups --}}
    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Missed Checkups ({{ $missedCheckups->total() }})</h5>
        </div>
        <div class="card-body p-0">
            @if($missedCheckups->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Patient</th><th>Scheduled</th><th>Purpose</th></tr></thead>
                        <tbody>
                            @foreach($missedCheckups as $checkup)
                                <tr>
                                    <td>{{ $checkup->patient_name ?? '—' }}</td>
                                    <td>{{ optional($checkup->scheduled_date)->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $checkup->purpose ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $missedCheckups->appends(request()->except('missed_page'))->links() }}</div>
            @else
                <p class="text-muted text-center py-4 mb-0">No missed checkups.</p>
            @endif
        </div>
    </div>

    {{-- Records awaiting validation --}}
    <div class="card fade-in-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Records Awaiting Validation ({{ $pendingRecords->total() }})</h5>
            <a href="{{ route('midwife.health-records.index') }}" class="btn btn-sm btn-outline-primary">Open records</a>
        </div>
        <div class="card-body p-0">
            @if($pendingRecords->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Patient</th><th>Recorded By</th><th>Stage</th><th></th></tr></thead>
                        <tbody>
                            @foreach($pendingRecords as $record)
                                <tr>
                                    <td>{{ $record->patient_name ?? '—' }}</td>
                                    <td>{{ $record->recordedBy?->name ?? '—' }}</td>
                                    <td>{{ str_replace('_', ' ', $record->workflow_status) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-sm btn-outline-primary">Validate</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $pendingRecords->appends(request()->except('records_page'))->links() }}</div>
            @else
                <p class="text-muted text-center py-4 mb-0">Validation queue is clear.</p>
            @endif
        </div>
    </div>
</div>
@endsection
