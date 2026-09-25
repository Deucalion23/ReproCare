@extends('bhw-president.layout')

@section('title', 'BHW Details - BHW President Portal | ReproCare')

@section('bhw-president-content')
@php($assignedPurok = $bhw->purok ?? $bhw->activeBhwAssignment?->purok)

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">{{ $bhw->name }}</div>
            <p class="page-hero-subtitle">{{ $bhw->email }} · {{ $bhw->barangay ?? 'No barangay assigned' }}</p>
        </div>
        <a href="{{ route('bhw-president.bhws.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i> Back to BHWs
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4 fade-in-card">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-file-medical-fill"></i></div>
            <div class="stat-label">Health Records</div>
            <div class="stat-number">{{ $healthRecords->total() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stat-label">Checkups Scheduled</div>
            <div class="stat-number">{{ $checkups->total() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green fade-in-card">
            <div class="stat-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
            <div class="stat-label">Monthly Reports</div>
            <div class="stat-number">{{ $monthlyReports->total() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-amber fade-in-card">
            <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-label">Status</div>
            <div class="stat-number" style="font-size:1.25rem;">{{ ucfirst($bhw->status) }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0">Account Actions</h5>
            </div>
            <div class="card-body">
                @if(($bhw->status ?? 'approved') === 'approved')
                    <div class="alert alert-warning mb-0">Active BHW accounts stay on the roster. Mark this BHW inactive first, then archive.</div>
                @else
                    <x-archive-form :action="route('bhw-president.bhws.delete', $bhw->id)" label="Archive BHW" title="Archive BHW (retained for audit)" btnClass="btn btn-outline-warning" icon="bi bi-archive" :confirmText="'Archive ' . $bhw->name . '? Sessions are revoked and the account is retained for audit.'" />
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0">Assigned Work Purok</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('bhw-president.bhws.assign-purok', $bhw->id) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-8">
                        <label for="purok_id" class="form-label fw-semibold">Assign Purok</label>
                        <select class="form-select @error('purok_id') is-invalid @enderror" id="purok_id" name="purok_id" required>
                            <option value="">Select purok</option>
                            @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}" {{ (string) old('purok_id', $assignedPurok?->id ?? $bhw->purok_id) === (string) $purok->id ? 'selected' : '' }}>
                                    {{ $purok->name }}{{ $purok->barangay ? ' - ' . $purok->barangay : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('purok_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2-circle me-1"></i> Save Assignment
                        </button>
                    </div>
                </form>
                <div class="mt-3 text-muted" style="font-size:0.85rem;">
                    Current assignment: <strong>{{ $assignedPurok?->name ?? 'Not assigned' }}</strong>. Saving here also syncs the assignment record and sends a notification to the BHW.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Health Records</h5>
            </div>
            <div class="card-body p-0">
                @if($healthRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($healthRecords as $record)
                                    <tr>
                                        <td>{{ optional($record->woman)->name ?? 'Unknown' }}</td>
                                        <td>{{ $record->created_at->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->risk_level === 'high' ? 'danger' : ($record->risk_level === 'medium' ? 'warning' : 'success') }}">
                                                {{ ucfirst($record->risk_level ?? 'low') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $healthRecords->links() }}
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-medical" style="font-size:2rem;color:var(--text-muted);"></i>
                        <p class="mb-0 mt-2">No health records</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Checkups Scheduled</h5>
            </div>
            <div class="card-body p-0">
                @if($checkups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checkups as $checkup)
                                    <tr>
                                        <td>{{ optional($checkup->woman)->name ?? 'Unknown' }}</td>
                                        <td>{{ $checkup->scheduled_date->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $checkup->status === 'Completed' ? 'success' : ($checkup->status === 'Missed' ? 'danger' : 'primary') }}">
                                                {{ $checkup->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $checkups->links() }}
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x" style="font-size:2rem;color:var(--text-muted);"></i>
                        <p class="mb-0 mt-2">No checkups scheduled</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
