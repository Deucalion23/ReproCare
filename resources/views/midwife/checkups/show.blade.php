@extends('midwife.layout')

@section('title', 'Checkup Details - ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Checkup Details</h1>
        <div>
            <a href="{{ route('midwife.checkups.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Checkups
            </a>
            <a href="{{ route('midwife.checkups.edit', $checkup->id) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Checkup Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Scheduled Date & Time</label>
                            <div class="fw-bold">
                                @if($checkup->scheduled_date)
                                    @if(is_string($checkup->scheduled_date))
                                        {{ \Carbon\Carbon::parse($checkup->scheduled_date)->format('F j, Y') }}
                                    @else
                                        {{ $checkup->scheduled_date->format('F j, Y') }}
                                    @endif
                                    @if($checkup->scheduled_time)
                                        at {{ \Carbon\Carbon::parse($checkup->scheduled_time)->format('g:i A') }}
                                    @endif
                                @else
                                    Not scheduled
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if(in_array($checkup->status, ['scheduled', 'Scheduled']))
                                    <span class="badge bg-primary">Scheduled</span>
                                @elseif(in_array($checkup->status, ['completed', 'Completed']))
                                    <span class="badge bg-success">Completed</span>
                                @elseif(in_array($checkup->status, ['missed', 'Missed']))
                                    <span class="badge bg-danger">Missed</span>
                                @else
                                    <span class="badge bg-secondary">{{ $checkup->status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Purpose of Checkup</label>
                            <div class="fw-bold">{{ $checkup->purpose ?? 'Not specified' }}</div>
                        </div>
                    </div>
                    
                    @if($checkup->notes)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted">Additional Notes</label>
                                <div>{{ $checkup->notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">People Involved</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h6 class="text-muted mb-2">Patient</h6>
                            @if($checkup->patient_record)
                                <div class="mb-2 p-2 border bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-primary me-2" style="font-size:1.5rem;"></i>
                                        <div class="ms-2 flex-grow-1">
                                            <div class="fw-bold">{{ $checkup->patient_name }}</div>
                                            <div class="text-muted small">{{ $checkup->woman?->email ?? $checkup->walkInPatient?->contact_number ?? 'Unlinked woman' }}</div>
                                            @if($checkup->walkInPatient)
                                                <div class="text-muted small">{{ $checkup->walkInPatient->barangay ?? 'No barangay set' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">Patient not found</div>
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <h6 class="text-muted mb-2">Assigned Midwife</h6>
                            @if($checkup->midwife)
                                <div class="mb-2 p-2 border bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-badge text-success me-2" style="font-size:1.5rem;"></i>
                                        <div class="ms-2 flex-grow-1">
                                            <div class="fw-bold">{{ $checkup->midwife->name }}</div>
                                            <div class="text-muted small">{{ $checkup->midwife->email }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">No midwife assigned</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @if(in_array($checkup->status, ['scheduled', 'Scheduled']))
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('midwife.checkups.complete', $checkup->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Mark as Completed
                            </button>
                        </form>
                        
                        <form action="{{ route('midwife.checkups.miss', $checkup->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Mark as Missed
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
