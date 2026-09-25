@extends('bhw-president.layout')

@section('bhw-president-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Review Pregnancy Record</h1>
            <p class="text-muted mb-0">Review and approve/reject pregnancy record submitted for approval</p>
        </div>
        <a href="{{ route('bhw-president.pregnancies.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Pregnancies
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pregnancy Record Details</h5>
                </div>
                <div class="card-body">
                    <!-- Patient Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Patient Information</h6>
                        @if($pregnancy->woman)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <x-patient-avatar :patient="$pregnancy->woman" :size="56" />
                            <a href="{{ route('profile.view', $pregnancy->woman->id) }}">View patient profile</a>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Name:</strong> {{ $pregnancy->woman->first_name }} {{ $pregnancy->woman->middle_initial }} {{ $pregnancy->woman->last_name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> {{ $pregnancy->woman->email }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Contact:</strong> {{ $pregnancy->woman->contact_number ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Barangay:</strong> {{ $pregnancy->woman->barangay ?? 'N/A' }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Pregnancy Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Pregnancy Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>LMP:</strong> {{ $pregnancy->lmp ? $pregnancy->lmp->format('M d, Y') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>EDD:</strong> {{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Age of Gestation:</strong> {{ $pregnancy->aog ?? 'N/A' }} weeks
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Current Trimester:</strong> {{ $pregnancy->trimester_name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- OB History -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Obstetric History</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>Gravida:</strong> {{ $pregnancy->gravida ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Para:</strong> {{ $pregnancy->para ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>GTPAL:</strong> 
                                T: {{ $pregnancy->gtpal_term ?? 0 }},
                                P: {{ $pregnancy->gtpal_preterm ?? 0 }},
                                A: {{ $pregnancy->gtpal_abortions ?? 0 }},
                                L: {{ $pregnancy->gtpal_living_children ?? 0 }}
                            </div>
                        </div>
                    </div>

                    <!-- Health Measurements -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Health Measurements</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>Blood Pressure:</strong> {{ $pregnancy->blood_pressure ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Weight:</strong> {{ $pregnancy->weight ? $pregnancy->weight . ' kg' : 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Height:</strong> {{ $pregnancy->height ? $pregnancy->height . ' cm' : 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>BMI:</strong> {{ $pregnancy->bmi ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Risk Assessment -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Risk Assessment</h6>
                        @if($pregnancy->is_high_risk)
                        <div class="alert alert-danger">
                            <strong>High Risk Pregnancy</strong>
                            @if($pregnancy->risk_notes)
                            <br><strong>Notes:</strong> {{ $pregnancy->risk_notes }}
                            @endif
                        </div>
                        @else
                        <div class="alert alert-success">
                            <strong>Normal Risk Pregnancy</strong>
                        </div>
                        @endif
                    </div>

                    <!-- Health Conditions -->
                    @if($pregnancy->health_conditions)
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Health Conditions</h6>
                        <div class="row">
                            @if($pregnancy->health_conditions)
                            @foreach($pregnancy->health_conditions as $condition)
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info">{{ $condition }}</span>
                            </div>
                            @endforeach
                            @endif
                            @if($pregnancy->health_condition_other)
                            <div class="col-md-12 mb-2">
                                <strong>Other:</strong> {{ $pregnancy->health_condition_other }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Lifestyle Factors -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Lifestyle Factors</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>Smoking:</strong> {{ $pregnancy->smoking_status ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Alcohol:</strong> {{ $pregnancy->alcohol_status ?? 'N/A' }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Drug Use:</strong> {{ $pregnancy->drug_use_status ?? 'N/A' }}
                            </div>
                        </div>
                        @if($pregnancy->lifestyle_notes)
                        <div class="mt-2">
                            <strong>Lifestyle Notes:</strong> {{ $pregnancy->lifestyle_notes }}
                        </div>
                        @endif
                    </div>

                    <!-- Obstetric History Notes -->
                    @if($pregnancy->obstetric_history)
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Obstetric History Notes</h6>
                        <p class="text-muted">{{ $pregnancy->obstetric_history }}</p>
                    </div>
                    @endif

                    <!-- General Notes -->
                    @if($pregnancy->notes)
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Notes</h6>
                        <p class="text-muted">{{ $pregnancy->notes }}</p>
                    </div>
                    @endif

                    <!-- Workflow Information -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3">Workflow Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Status:</strong> 
                                <span class="badge badge-{{ $pregnancy->workflow_status === 'submitted_to_bhw_president' ? 'warning' : 'info' }}">
                                    {{ ucfirst(str_replace('_', ' ', $pregnancy->workflow_status)) }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Submitted At:</strong> {{ $pregnancy->submitted_to_bhw_president_at ? $pregnancy->submitted_to_bhw_president_at->format('M d, Y g:i A') : 'N/A' }}
                            </div>
                        </div>
                        @if($pregnancy->workflow_notes)
                        <div class="mt-2">
                            <strong>Workflow Notes:</strong> {{ $pregnancy->workflow_notes }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Associated Health Records -->
            @if($pregnancy->healthRecords->count() > 0)
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Associated Health Records ({{ $pregnancy->healthRecords->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>BP</th>
                                    <th>Weight</th>
                                    <th>Risk Level</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pregnancy->healthRecords as $record)
                                <tr>
                                    <td>{{ $record->created_at->format('M d, Y') }}</td>
                                    <td>{{ $record->bp ?? 'N/A' }}</td>
                                    <td>{{ $record->weight ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $record->risk_level === 'High' ? 'danger' : ($record->risk_level === 'Medium' ? 'warning' : 'success') }}">
                                            {{ $record->risk_level ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $record->recordedBy->name ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Pregnancy Status -->
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Pregnancy Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Status:</strong> 
                        <span class="badge badge-{{ $pregnancy->is_active ? 'success' : 'secondary' }}">
                            {{ $pregnancy->status }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Created At:</strong> {{ $pregnancy->created_at->format('M d, Y g:i A') }}
                    </div>
                    @if($pregnancy->ended_at)
                    <div class="mb-2">
                        <strong>Ended At:</strong> {{ $pregnancy->ended_at->format('M d, Y') }}
                    </div>
                    @endif
                    @if($pregnancy->outcome)
                    <div class="mb-2">
                        <strong>Outcome:</strong> {{ $pregnancy->outcome }}
                    </div>
                    @endif
                    @if($pregnancy->is_overdue)
                    <div class="alert alert-warning mt-2">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Pregnancy is overdue!</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Milestones -->
            <div class="card fade-in-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Pregnancy Milestones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Milestone</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pregnancy->milestones ?? [] as $milestone)
                                <tr>
                                    <td>{{ $milestone['name'] }}</td>
                                    <td>{{ $milestone['date']->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $milestone['status'] === 'completed' ? 'success' : ($milestone['status'] === 'current' ? 'primary' : 'secondary') }}">
                                            {{ ucfirst($milestone['status']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Review Actions -->
            <div class="card fade-in-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Review Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('bhw-president.pregnancies.approve', $pregnancy->id) }}" method="POST">
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

                    <form action="{{ route('bhw-president.pregnancies.reject', $pregnancy->id) }}" method="POST">
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
