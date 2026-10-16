@extends('rhu.layout')

@section('title', 'Health Record #' . $record->id . ' - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Health Record #{{ $record->id }}</div>
            <p class="page-hero-subtitle">{{ $record->patient_name }} · {{ $record->woman?->barangay ?? $record->walkInPatient?->barangay ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('rhu.health-records.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to tracking</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card fade-in-card h-100">
            <div class="card-header"><strong>Visit details</strong></div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size:0.9rem;">
                    <dt class="col-sm-5">Woman</dt><dd class="col-sm-7">{{ $record->patient_name }}</dd>
                    <dt class="col-sm-5">Pregnancy</dt><dd class="col-sm-7">{{ $record->pregnancy ? 'Pregnancy #' . $record->pregnancy->id . ' (EDD ' . ($record->pregnancy->edd?->format('M j, Y') ?? 'N/A') . ')' : 'Not linked' }}</dd>
                    <dt class="col-sm-5">BP / Weight</dt><dd class="col-sm-7">{{ $record->bp ?? 'N/A' }} · {{ $record->weight ?? 'N/A' }} kg</dd>
                    <dt class="col-sm-5">Risk level</dt><dd class="col-sm-7"><span class="badge bg-{{ in_array($record->risk_level, ['High', 'Critical']) ? 'danger' : ($record->risk_level === 'Medium' ? 'warning' : 'success') }}">{{ $record->risk_level ?? 'Low' }}</span></dd>
                    <dt class="col-sm-5">Notes</dt><dd class="col-sm-7">{{ $record->notes ?? $record->risk_notes ?? '—' }}</dd>
                    <dt class="col-sm-5">Assigned worker</dt><dd class="col-sm-7">{{ $record->recordedBy?->name ?? 'N/A' }} ({{ $record->recordedBy?->role ? str_replace('_', ' ', $record->recordedBy->role) : '—' }})</dd>
                    <dt class="col-sm-5">Created</dt><dd class="col-sm-7">{{ $record->created_at?->format('M j, Y g:i A') }}</dd>
                    <dt class="col-sm-5">Last updated</dt><dd class="col-sm-7">{{ $record->updated_at?->format('M j, Y g:i A') }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card fade-in-card h-100">
            <div class="card-header"><strong>Visit history for this woman (latest 20)</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:0.82rem;">
                        <thead><tr><th class="ps-3">Date</th><th>Risk</th><th>Recorded by</th><th>BP</th></tr></thead>
                        <tbody>
                            @forelse($history as $h)
                                <tr class="{{ $h->id === $record->id ? 'table-primary' : '' }}">
                                    <td class="ps-3">{{ $h->created_at?->format('M j, Y') }}</td>
                                    <td>{{ $h->risk_level ?? 'Low' }}</td>
                                    <td>{{ $h->recordedBy?->name ?? 'N/A' }}</td>
                                    <td>{{ $h->bp ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No visit history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
