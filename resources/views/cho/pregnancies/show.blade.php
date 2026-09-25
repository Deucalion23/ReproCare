@extends(($portal ?? 'cho').'.layout')

@section('title', 'Pregnancy Details | ReproCare')

@section(($portal ?? 'cho').'-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <x-patient-avatar :patient="$pregnancy->woman ?? $pregnancy->walkInPatient" :name="$pregnancy->patient_name" :size="56" />
            <div>
            <div class="page-hero-title">Pregnancy Record — {{ $pregnancy->patient_name }}</div>
            <p class="page-hero-subtitle">LMP {{ optional($pregnancy->lmp)->format('M j, Y') ?? '—' }} · EDD {{ optional($pregnancy->edd)->format('M j, Y') ?? '—' }} · AOG {{ $pregnancy->aog ?? '—' }} weeks</p>
            </div>
        </div>
        <a href="{{ route(($portal ?? 'cho') === 'rhu' ? 'rhu.analytics' : 'cho.pregnancies.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card fade-in-card"><div class="card-body">
            <h6 class="fw-bold mb-3">Clinical Summary</h6>
            <p class="mb-1"><strong>Risk Level:</strong> {{ $pregnancy->risk_level ?? 'Unassessed' }}</p>
            <p class="mb-1"><strong>Status:</strong> {{ $pregnancy->ended_at ? 'Completed' : 'Active' }}</p>
            <p class="mb-1"><strong>Gravida / Para:</strong> {{ $pregnancy->gravida ?? '—' }} / {{ $pregnancy->para ?? '—' }}</p>
            <p class="mb-0"><strong>Notes:</strong> {{ $pregnancy->notes ?? '—' }}</p>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card fade-in-card"><div class="card-header"><h6 class="mb-0">Linked Health Records ({{ $pregnancy->healthRecords->count() }})</h6></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="px-4">Date</th><th>BP</th><th>Weight</th><th>Risk</th><th>Recorded By</th></tr></thead>
                    <tbody>
                        @forelse($pregnancy->healthRecords as $record)
                            <tr>
                                <td class="px-4">{{ optional($record->created_at)->format('M j, Y') }}</td>
                                <td>{{ $record->bp ?? '—' }}</td>
                                <td>{{ $record->weight ?? '—' }}</td>
                                <td>{{ $record->risk_level ?? '—' }}</td>
                                <td><x-record-author :record="$record" :barangay="$pregnancy->woman?->barangay ?? $pregnancy->walkInPatient?->barangay" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No linked health records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</div>
@endsection
