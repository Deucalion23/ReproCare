@extends('rhu.layout')

@section('title', 'Health Record Tracking - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Health Record Tracking</div>
            <p class="page-hero-subtitle">Read-only oversight of records entered or updated by midwives and health workers — vitals, pregnancy linkage, assigned worker and visit history.</p>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search woman</label>
                <input type="text" name="search" class="form-control" placeholder="Name or risk level..." value="{{ $search }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Risk level</label>
                <select name="risk_level" class="form-select">
                    @foreach(['all', 'Low', 'Medium', 'High', 'Critical'] as $level)
                        <option value="{{ $level }}" @selected($riskLevel === $level)>{{ $level === 'all' ? 'All levels' : $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Barangay</label>
                <select name="barangay" class="form-select">
                    <option value="">All barangays</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b }}" @selected($barangay === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('rhu.health-records.index') }}">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Woman</th>
                        <th>Barangay</th>
                        <th>Pregnancy</th>
                        <th>Risk</th>
                        <th>Recorded by</th>
                        <th>Created / Updated</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-700">{{ $record->patient_name }}</span><br>
                                <small class="text-muted">Record #{{ $record->id }}</small>
                            </td>
                            <td>{{ $record->woman?->barangay ?? $record->walkInPatient?->barangay ?? 'N/A' }}</td>
                            <td>
                                @if($record->pregnancy)
                                    <span style="font-size:0.85rem;">Pregnancy #{{ $record->pregnancy->id }}</span><br>
                                    <small class="text-muted">EDD {{ $record->pregnancy->edd?->format('M j, Y') ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ in_array($record->risk_level, ['High', 'Critical']) ? 'danger' : ($record->risk_level === 'Medium' ? 'warning' : 'success') }}">{{ $record->risk_level ?? 'Low' }}</span></td>
                            <td>
                                <span style="font-size:0.85rem;">{{ $record->recordedBy?->name ?? 'N/A' }}</span><br>
                                <small class="text-muted">{{ $record->recordedBy?->role ? str_replace('_', ' ', $record->recordedBy->role) : '' }}</small>
                            </td>
                            <td style="font-size:0.8rem;">
                                {{ $record->created_at?->format('M j, Y g:i A') }}<br>
                                <small class="text-muted">Upd: {{ $record->updated_at?->format('M j, Y g:i A') }}</small>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('rhu.health-records.show', $record->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No health records found for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="p-3">{{ $records->links() }}</div>
        @endif
    </div>
</div>

@endsection
