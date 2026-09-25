@extends('bhw-president.layout')

@section('bhw-president-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Review Health Record</h1>
            <p class="text-muted mb-0">Review and approve/reject health record submitted for approval</p>
        </div>
        <a href="{{ route('bhw-president.health-records.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Records
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Health Record Details</h5>
                </div>
                <div class="card-body">
                    <!-- Patient Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Patient Information</h6>
                        @if($healthRecord->woman)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <x-patient-avatar :patient="$healthRecord->woman" :size="56" />
                            <a href="{{ route('profile.view', $healthRecord->woman->id) }}">View patient profile</a>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Name:</strong> {{ $healthRecord->woman->first_name }} {{ $healthRecord->woman->middle_initial }} {{ $healthRecord->woman->last_name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> {{ $healthRecord->woman->email }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Contact:</strong> {{ $healthRecord->woman->contact_number ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Barangay:</strong> {{ $healthRecord->woman->barangay ?? 'N/A' }}
                            </div>
                        </div>
                        @elseif($healthRecord->walkInPatient)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Name:</strong> {{ $healthRecord->walkInPatient->name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Contact:</strong> {{ $healthRecord->walkInPatient->contact ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Barangay:</strong> {{ $healthRecord->walkInPatient->barangay ?? 'N/A' }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Health Measurements -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Health Measurements</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>Blood Pressure:</strong> {{ $healthRecord->bp ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Weight:</strong> {{ $healthRecord->weight ? $healthRecord->weight . ' kg' : 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Height:</strong> {{ $healthRecord->height ? $healthRecord->height . ' cm' : 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>BMI:</strong> {{ $healthRecord->bmi ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Heart Rate:</strong> {{ $healthRecord->heart_rate ? $healthRecord->heart_rate . ' bpm' : 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Temperature:</strong> {{ $healthRecord->temperature ? $healthRecord->temperature . ' °C' : 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Risk Assessment -->
                    @if($healthRecord->risk_level)
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Risk Assessment</h6>
                        <div class="alert alert-{{ $healthRecord->risk_level === 'High' ? 'danger' : ($healthRecord->risk_level === 'Medium' ? 'warning' : 'success') }}">
                            <strong>Risk Level:</strong> {{ $healthRecord->risk_level }}
                            @if($healthRecord->risk_notes)
                            <br><strong>Notes:</strong> {{ $healthRecord->risk_notes }}
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Additional Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Additional Information</h6>
                        <div class="row">
                            @if($healthRecord->hemoglobin)
                            <div class="col-md-6 mb-2">
                                <strong>Hemoglobin:</strong> {{ $healthRecord->hemoglobin }}
                            </div>
                            @endif
                            @if($healthRecord->gestational_age)
                            <div class="col-md-6 mb-2">
                                <strong>Gestational Age:</strong> {{ $healthRecord->gestational_age }} weeks
                            </div>
                            @endif
                            @if($healthRecord->immunization_status)
                            <div class="col-md-6 mb-2">
                                <strong>Immunization Status:</strong> {{ $healthRecord->immunization_status }}
                            </div>
                            @endif
                            @if($healthRecord->contraceptive_use)
                            <div class="col-md-6 mb-2">
                                <strong>Contraceptive Use:</strong> {{ $healthRecord->contraceptive_use }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($healthRecord->notes)
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Notes</h6>
                        <p class="text-muted">{{ $healthRecord->notes }}</p>
                    </div>
                    @endif

                    <!-- Workflow Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Workflow Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Status:</strong> 
                                <span class="badge badge-{{ $healthRecord->workflow_status === 'submitted_to_bhw_president' ? 'warning' : 'info' }}">
                                    {{ ucfirst(str_replace('_', ' ', $healthRecord->workflow_status)) }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Submitted At:</strong> {{ $healthRecord->submitted_to_bhw_president_at ? $healthRecord->submitted_to_bhw_president_at->format('M d, Y g:i A') : 'N/A' }}
                            </div>
                        </div>
                        @if($healthRecord->workflow_notes)
                        <div class="mt-2">
                            <strong>Workflow Notes:</strong> {{ $healthRecord->workflow_notes }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recorder Information -->
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Recorded By</h5>
                </div>
                <div class="card-body">
                    @if($healthRecord->recordedBy)
                    <div class="mb-2">
                        <strong>Recorded By:</strong> {{ $healthRecord->recordedBy->first_name }} {{ $healthRecord->recordedBy->last_name }}
                    </div>
                    @endif
                    <div class="mb-2">
                        <strong>Recorded At:</strong> {{ $healthRecord->created_at->format('M d, Y g:i A') }}
                    </div>
                </div>
            </div>

            <!-- Review Actions -->
            <div class="card fade-in-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Review Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('bhw-president.health-records.approve', $healthRecord->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Approval Notes (Optional)</label>
                            <textarea name="bhw_president_notes" class="form-control" rows="3" placeholder="Add any notes for approval..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-check me-2"></i>Approve Record
                        </button>
                    </form>

                    <hr>

                    <form action="{{ route('bhw-president.health-records.reject', $healthRecord->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times me-2"></i>Reject Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
