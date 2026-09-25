@extends('midwife.layout')

@section('title', 'Woman Health Records - ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Woman Health Records</h1>
            <p class="text-muted mb-0">{{ $woman->name }} ({{ $woman->email }})</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.patient-details', $woman->id) }}" class="btn btn-outline-info">
                <i class="bi bi-person"></i> Woman Profile
            </a>
            <a href="{{ route('midwife.health-records.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to All Records
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $healthRecords->count() }}</h4>
                    <p class="mb-0">Total Records</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $healthRecords->where('risk_level', 'Low')->count() }}</h4>
                    <p class="mb-0">Low Risk</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $healthRecords->where('risk_level', 'Medium')->count() }}</h4>
                    <p class="mb-0">Medium Risk</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>{{ $healthRecords->where('risk_level', 'High')->count() }}</h4>
                    <p class="mb-0">High Risk</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Health Records for {{ $woman->name }}</h5>
        </div>
        <div class="card-body">
            @if($healthRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Vital Signs</th>
                                <th>Clinical Data</th>
                                <th>Risk Level</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($healthRecords as $record)
                                <tr>
                                    <td>
                                        <strong>{{ $record->created_at->format('M j, Y') }}</strong>
                                        <br><small class="text-muted">{{ $record->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><strong>BP:</strong> {{ $record->bp }}</div>
                                            <div><strong>HR:</strong> {{ $record->heart_rate }} bpm</div>
                                            <div><strong>Temp:</strong> {{ $record->temperature }}°C</div>
                                            <div><strong>Weight:</strong> {{ $record->weight }} kg</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($record->hemoglobin)
                                                <div><strong>Hgb:</strong> {{ $record->hemoglobin }} g/dL</div>
                                            @endif
                                            @if($record->gestational_age)
                                                <div><strong>GA:</strong> {{ $record->gestational_age }}</div>
                                            @endif
                                            @if($record->immunization_status)
                                                <div><strong>Imm:</strong> {{ $record->immunization_status }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @switch($record->risk_level)
                                            @case('Low')
                                                <span class="badge bg-success">Low Risk</span>
                                                @break
                                            @case('Medium')
                                                <span class="badge bg-warning">Medium Risk</span>
                                                @break
                                            @case('High')
                                                <span class="badge bg-danger">High Risk</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $record->risk_level }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($record->recordedBy)
                                            <div>
                                                <strong>{{ $record->recordedBy->name }}</strong>
                                                <br><small class="text-muted">{{ $record->recordedBy->role }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('midwife.health-records.edit', $record->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-pulse text-muted" style="font-size:4rem;"></i>
                    <h3 class="text-muted mt-3">No Health Records Found</h3>
                    <p class="text-muted">This patient has no health records yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
