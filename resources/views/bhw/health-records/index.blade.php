@extends('bhw.layout')

@section('title', 'Health Records - ReproCare')

@push('styles')
<style>
    /* Compact fit-no-scroll table: tighter cells, no wrapping vitals/dates. */
    #bhw-hr-table > :not(caption) > * > * {
        padding:0.55rem 0.5rem;
        font-size:0.85rem;
    }
    #bhw-hr-table .hr-nowrap {
        white-space:nowrap;
    }
    #bhw-hr-table .hr-date small {
        display:block;
        color:var(--color-text-muted);
        font-size:0.75rem;
        white-space:nowrap;
    }
    /* Forced equal action buttons: variant padding/borders can't unbalance them. */
    #bhw-hr-table .tbl-actions .btn {
        width:38px;
        height:38px;
        padding:0;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }
    #bhw-hr-table .tbl-actions .btn > i { line-height:1; }
</style>
@endpush

@section('bhw-content')
@php
    $registeredWomen = \App\Models\User::where('role', 'user')
        ->where('status', 'approved')
        ->orderBy('first_name')
        ->orderBy('middle_initial')
        ->orderBy('last_name')
        ->get();
    $walkInPatients = \App\Models\WalkInPatient::whereNull('converted_to_user_id')->orderBy('last_name')->orderBy('first_name')->get();
@endphp

<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Health Records</div>
                <p class="page-hero-subtitle">Record vital signs quickly and keep risk screening consistent for both enrolled and unlinked patients.</p>
            </div>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Add Record
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-cyan fade-in-card">
                <i class="bi bi-clipboard-data stat-icon"></i>
                <div class="stat-label">Total Records</div>
                <div class="stat-number">{{ $healthRecords->total() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-green fade-in-card">
                <i class="bi bi-check-circle-fill stat-icon"></i>
                <div class="stat-label">Low Risk</div>
                <div class="stat-number">{{ $healthRecords->where('risk_level', 'Low')->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-danger fade-in-card">
                <i class="bi bi-exclamation-triangle-fill stat-icon"></i>
                <div class="stat-label">High Risk</div>
                <div class="stat-number">{{ $healthRecords->where('risk_level', 'High')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bhw.health-records.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Search Patient</label>
                    <input type="text" class="form-control" name="search" placeholder="Search enrolled or unlinked patient..." value="{{ request('search') }}">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Search</button>
                    @if(request('search'))
                        <a href="{{ route('bhw.health-records.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card fade-in-card">
        <div class="card-header">
            <h5 class="mb-0">Records Added by Me</h5>
        </div>
        <div class="card-body p-0">
            @if($healthRecords->count() > 0)
                <div class="table-responsive">
                        <table class="table table-hover mb-0" id="bhw-hr-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>BP</th>
                                <th>Weight</th>
                                <th>Heart Rate</th>
                                <th>Temperature</th>
                                <th>Risk</th>
                                <th>Workflow</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($healthRecords as $record)
                                @php
                                    $patient = $record->woman ?? $record->walkInPatient;
                                    $patientName = $record->patient_name ?? ($patient?->name ?? $patient?->full_name ?? 'Unknown patient');
                                    $patientMeta = $record->walkInPatient ? 'Unlinked patient' : ($patient?->email ?? 'Enrolled patient');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $patientName }}</div>
                                        <small class="text-muted">{{ $patientMeta }}</small>
                                    </td>
                                    <td class="hr-date">{{ $record->created_at->format('M j, Y') }}<small>{{ $record->created_at->format('g:i A') }}</small></td>
                                    <td class="hr-nowrap">{{ $record->bp }}</td>
                                    <td class="hr-nowrap">{{ $record->weight }} kg</td>
                                    <td class="hr-nowrap">{{ $record->heart_rate }} bpm</td>
                                    <td class="hr-nowrap">{{ $record->temperature }} C</td>
                                    <td>
                                        <span class="badge {{ $record->risk_level === 'High' ? 'bg-danger' : ($record->risk_level === 'Medium' ? 'bg-warning text-dark' : 'bg-success') }}">
                                            {{ $record->risk_level }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->workflow_status === 'recorded_by_bhw')
                                            <span class="badge bg-secondary text-nowrap">Draft</span>
                                        @elseif($record->workflow_status === 'submitted_to_bhw_president')
                                            <span class="badge bg-warning text-dark text-nowrap">Submitted to President</span>
                                        @elseif($record->workflow_status === 'approved_by_bhw_president')
                                            <span class="badge bg-info text-dark text-nowrap">Approved by President</span>
                                        @elseif($record->workflow_status === 'submitted_to_midwife')
                                            <span class="badge bg-primary text-nowrap">With Midwife</span>
                                        @elseif($record->workflow_status === 'accepted_by_midwife')
                                            <span class="badge bg-success text-nowrap">Accepted</span>
                                        @else
                                            <span class="badge bg-secondary text-nowrap">{{ str_replace('_', ' ', $record->workflow_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center gap-1 tbl-actions">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#recordModal{{ $record->id }}" title="View record" aria-label="View record">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($record->workflow_status === 'recorded_by_bhw')
                                                <form method="POST" action="{{ route('bhw.health-records.submit-to-president', $record->id) }}" onsubmit="return confirm('Submit this health record to BHW President for review?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Submit to BHW President" aria-label="Submit to BHW President">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $record->id }}" title="Archive record" aria-label="Archive record">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4 mb-4">
                    {{ $healthRecords->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x empty-state-icon"></i>
                    <h6>No health records found</h6>
                    <p>{{ request('search') ? 'No records matched your search.' : 'You have not added any health records yet.' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($healthRecords as $record)
    @php
        $patient = $record->woman ?? $record->walkInPatient;
        $patientName = $record->patient_name ?? ($patient?->name ?? $patient?->full_name ?? 'Unknown patient');
        $patientEmail = $record->walkInPatient ? 'Not available for unlinked patients' : ($patient?->email ?? 'Not available');
        $patientAddress = $patient?->address ?? $patient?->barangay ?? 'Not specified';
    @endphp
    <div class="modal fade" id="recordModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Health Record Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name</strong></td><td>{{ $patientName }}</td></tr>
                                <tr><td><strong>Type</strong></td><td>{{ $record->walkInPatient ? 'Unlinked patient' : 'Enrolled patient' }}</td></tr>
                                <tr><td><strong>Email</strong></td><td>{{ $patientEmail }}</td></tr>
                                <tr><td><strong>Address</strong></td><td>{{ $patientAddress }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Recording Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Date</strong></td><td>{{ $record->created_at->format('M j, Y g:i A') }}</td></tr>
                                <tr><td><strong>Recorded By</strong></td><td>{{ $record->recordedBy?->name ?? $record->recordedByUser?->name ?? 'System' }}</td></tr>
                                <tr><td><strong>Role</strong></td><td>{{ ucfirst($record->recordedByRole ?? 'N/A') }}</td></tr>
                                <tr><td><strong>Risk Level</strong></td><td>{{ $record->risk_level }}</td></tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 text-center">
                        <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">BP</div><h4 class="mb-0">{{ $record->bp }}</h4></div></div></div>
                        <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Weight</div><h4 class="mb-0">{{ $record->weight }} kg</h4></div></div></div>
                        <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Heart Rate</div><h4 class="mb-0">{{ $record->heart_rate }} bpm</h4></div></div></div>
                        <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Temperature</div><h4 class="mb-0">{{ $record->temperature }} C</h4></div></div></div>
                    </div>
                    @if($record->notes)
                        <hr>
                        <h6>Notes</h6>
                        <div class="card"><div class="card-body">{{ $record->notes }}</div></div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archiveModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('bhw.health-records.archive', $record->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Archive Health Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Archive the record for <strong>{{ $patientName }}</strong>?</p>
                        <label class="form-label">Reason</label>
                        <input type="text" name="archived_reason" class="form-control" placeholder="Example: duplicate entry or outdated">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Archive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addRecordModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Health Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRecordForm" method="POST" action="{{ route('bhw.health-records.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Patient Type</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="patient_type" id="patientTypeRegistered" value="registered" checked onchange="toggleRecordPatientType()">
                                <label class="form-check-label" for="patientTypeRegistered">Enrolled Patient</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="patient_type" id="patientTypeWalkIn" value="walk_in" onchange="toggleRecordPatientType()">
                                <label class="form-check-label" for="patientTypeWalkIn">Unlinked Patient</label>
                            </div>
                        </div>
                    </div>

                    <div id="registeredPatientSection" class="mb-4">
                                <label class="form-label fw-semibold">Search Enrolled Patient</label>
                        <input type="text" class="form-control mb-2" id="patientSearch" placeholder="Search by name or email..." oninput="filterSelectOptions('patientSearch', 'patientSelect')">
                        <select class="form-select" id="patientSelect" name="user_id">
                            <option value="">Choose a patient...</option>
                            @foreach($registeredWomen as $woman)
                                <option value="{{ $woman->id }}">{{ $woman->name }}{{ $woman->email ? ' - ' . $woman->email : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="walkInPatientSection" class="mb-4 d-none">
                        <label class="form-label fw-semibold">Search Unlinked Patient</label>
                        <input type="text" class="form-control mb-2" id="walkInSearch" placeholder="Search by name or contact number..." oninput="filterSelectOptions('walkInSearch', 'walkInPatientSelect')">
                        <select class="form-select" id="walkInPatientSelect" name="walk_in_patient_id">
                            <option value="">Choose an unlinked patient...</option>
                            @foreach($walkInPatients as $walkIn)
                                <option value="{{ $walkIn->id }}">{{ $walkIn->full_name }}{{ $walkIn->contact_number ? ' - ' . $walkIn->contact_number : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="bp_systolic" class="form-label">BP Systolic</label>
                            <input type="number" class="form-control" id="bp_systolic" name="bp_systolic" required min="50" max="300">
                        </div>
                        <div class="col-md-3">
                            <label for="bp_diastolic" class="form-label">BP Diastolic</label>
                            <input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic" required min="30" max="200">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BP Category</label>
                            <input type="text" class="form-control" id="bp_category" readonly placeholder="Automatic">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Suggested Risk</label>
                            <input type="text" class="form-control" id="bp_risk_hint" readonly placeholder="Automatic">
                        </div>
                        <div class="col-md-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" required min="0" max="300" step="0.1">
                        </div>
                        <div class="col-md-3">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" class="form-control" id="height" name="height" min="100" max="250" step="0.1">
                            <div class="form-text">Below 122 cm (4 ft) flags short-stature risk.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BMI</label>
                            <input type="text" class="form-control" id="bmi_value" readonly placeholder="Automatic">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BMI Status</label>
                            <input type="text" class="form-control" id="bmi_status" readonly placeholder="Automatic">
                        </div>
                        <div class="col-md-4">
                            <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                            <input type="number" class="form-control" id="heart_rate" name="heart_rate" required min="0" max="250">
                        </div>
                        <div class="col-md-4">
                            <label for="temperature" class="form-label">Temperature (C)</label>
                            <input type="number" class="form-control" id="temperature" name="temperature" required min="30" max="45" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label for="notes" class="form-label">Notes</label>
                            <input type="text" class="form-control" id="notes" name="notes" placeholder="Optional observations">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRecordPatientType() {
    const isRegistered = document.getElementById('patientTypeRegistered').checked;
    const registeredSection = document.getElementById('registeredPatientSection');
    const walkInSection = document.getElementById('walkInPatientSection');
    const patientSelect = document.getElementById('patientSelect');
    const walkInSelect = document.getElementById('walkInPatientSelect');

    registeredSection.classList.toggle('d-none', !isRegistered);
    walkInSection.classList.toggle('d-none', isRegistered);
    patientSelect.required = isRegistered;
    walkInSelect.required = !isRegistered;
    patientSelect.disabled = !isRegistered;
    walkInSelect.disabled = isRegistered;
}

function filterSelectOptions(inputId, selectId) {
    const term = document.getElementById(inputId).value.toLowerCase();
    const options = document.getElementById(selectId).options;

    for (let index = 0; index < options.length; index++) {
        const option = options[index];
        option.hidden = !(option.value === '' || option.text.toLowerCase().includes(term));
    }
}

function updateBpAndBmi() {
    const systolic = parseInt(document.getElementById('bp_systolic').value, 10);
    const diastolic = parseInt(document.getElementById('bp_diastolic').value, 10);
    const weight = parseFloat(document.getElementById('weight').value);
    const height = parseFloat(document.getElementById('height').value);
    const bpCategory = document.getElementById('bp_category');
    const bpRiskHint = document.getElementById('bp_risk_hint');
    const bmiValue = document.getElementById('bmi_value');
    const bmiStatus = document.getElementById('bmi_status');

    if (!Number.isNaN(systolic) && !Number.isNaN(diastolic)) {
        let category = 'Normal';
        let risk = 'Low';

        if (systolic >= 160 || diastolic >= 110) {
            category = 'Severe hypertension';
            risk = 'High';
        } else if (systolic >= 140 || diastolic >= 90) {
            category = 'Hypertension';
            risk = 'High';
        } else if (systolic >= 130 || diastolic >= 80) {
            category = 'Elevated';
            risk = 'Medium';
        }

        bpCategory.value = category;
        bpRiskHint.value = risk;
    } else {
        bpCategory.value = '';
        bpRiskHint.value = '';
    }

    if (!Number.isNaN(weight) && !Number.isNaN(height) && height > 0) {
        const bmi = weight / Math.pow(height / 100, 2);
        let label = 'Normal';

        if (bmi < 18.5) {
            label = 'Underweight / Malnourished';
        } else if (bmi >= 30) {
            label = 'Obese';
        } else if (bmi >= 25) {
            label = 'Overweight';
        }

        bmiValue.value = bmi.toFixed(1);
        bmiStatus.value = label;
    } else {
        bmiValue.value = '';
        bmiStatus.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    toggleRecordPatientType();
    ['bp_systolic', 'bp_diastolic', 'weight', 'height'].forEach((id) => {
        const field = document.getElementById(id);
        if (field) {
            field.addEventListener('input', updateBpAndBmi);
        }
    });
});
</script>
@endsection
