@extends('bhw.layout')

@section('title', 'Create Monthly Report - BHW Portal')

@section('bhw-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Create Monthly Report</div>
                <p class="page-hero-subtitle">Build a clean monthly summary from your recorded health records or pregnancy monitoring data.</p>
            </div>
            <a href="{{ route('bhw.reports.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i>Back to Reports</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="workspace-panel fade-in-card">
                <div class="workspace-panel-header">
                    <h2 class="workspace-panel-title">Report Configuration</h2>
                </div>
                <div class="workspace-panel-body">
                    <form action="{{ route('bhw.reports.store') }}" method="POST" id="reportForm">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="form-label">Report Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required placeholder="e.g., April 2026 Health Records Report" value="{{ old('title') }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Brief summary of what this report covers">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <div class="workspace-panel" style="background:var(--bg-card2);">
                                <div class="workspace-panel-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="report_type" id="type_health_records" value="health_records" {{ old('report_type', 'health_records') === 'health_records' ? 'checked' : '' }} onchange="toggleReportType()">
                                        <label class="form-check-label" for="type_health_records">
                                            <strong>Health Records</strong>
                                            <div class="text-muted small">Include patient health records recorded during the selected month.</div>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="type_pregnancies" value="pregnancies" {{ old('report_type') === 'pregnancies' ? 'checked' : '' }} onchange="toggleReportType()">
                                        <label class="form-check-label" for="type_pregnancies">
                                            <strong>Pregnancies</strong>
                                            <div class="text-muted small">Include monitored pregnancies for the selected reporting month.</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="workspace-filter-grid mb-4">
                            <div class="span-6">
                                <label for="report_month" class="form-label">Report Month <span class="text-danger">*</span></label>
                                <select class="form-select @error('report_month') is-invalid @enderror" id="report_month" name="report_month" required onchange="updatePreview()">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ old('report_month', $currentMonth) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                                @error('report_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="span-6">
                                <label for="report_year" class="form-label">Report Year <span class="text-danger">*</span></label>
                                <select class="form-select @error('report_year') is-invalid @enderror" id="report_year" name="report_year" required onchange="updatePreview()">
                                    @foreach(range(now()->year, 2020) as $y)
                                        <option value="{{ $y }}" {{ old('report_year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                                @error('report_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Patient Selection <span class="text-danger">*</span></label>
                            <div class="workspace-panel" style="background:var(--bg-card2);">
                                <div class="workspace-panel-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="patient_filter" id="filter_all" value="all" {{ old('patient_filter', 'all') === 'all' ? 'checked' : '' }} onchange="togglePatientSelection()">
                                        <label class="form-check-label" for="filter_all">
                                            <strong>All Patients</strong>
                                            <div class="text-muted small">Include every patient available for the selected report type.</div>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="patient_filter" id="filter_selected" value="selected" {{ old('patient_filter') === 'selected' ? 'checked' : '' }} onchange="togglePatientSelection()">
                                        <label class="form-check-label" for="filter_selected">
                                            <strong>Selected Patients</strong>
                                            <div class="text-muted small">Choose only the patients you want included in this report.</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="patientSelection" class="{{ old('patient_filter') === 'selected' ? '' : 'd-none' }}">
                            <label class="form-label">Select Patients</label>
                            <div class="selection-box">
                                <div id="healthRecordsPatients">
                                    @forelse($women as $woman)
                                        <label class="selection-option">
                                            <input class="form-check-input patient-checkbox" type="checkbox" name="user_ids[]" value="{{ $woman->id }}" {{ old('user_ids') && in_array($woman->id, old('user_ids')) ? 'checked' : '' }} onchange="updatePreview()">
                                            <span>
                                                <span class="selection-option-title">{{ trim($woman->first_name . ' ' . $woman->middle_initial . ' ' . $woman->last_name) }}</span>
                                                <span class="selection-option-note">{{ $woman->email }}</span>
                                            </span>
                                        </label>
                                    @empty
                                        <div class="text-muted p-3">No patients available yet for health record reports.</div>
                                    @endforelse
                                </div>

                                <div id="pregnanciesPatients" class="d-none">
                                    @forelse($pregnantWomen as $woman)
                                        <label class="selection-option">
                                            <input class="form-check-input patient-checkbox" type="checkbox" name="user_ids[]" value="{{ $woman->id }}" {{ old('user_ids') && in_array($woman->id, old('user_ids')) ? 'checked' : '' }} onchange="updatePreview()">
                                            <span>
                                                <span class="selection-option-title">{{ trim($woman->first_name . ' ' . $woman->middle_initial . ' ' . $woman->last_name) }}</span>
                                                <span class="selection-option-note">{{ $woman->email }}</span>
                                            </span>
                                        </label>
                                    @empty
                                        <div class="text-muted p-3">No pregnant patients available for this report type.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('user_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                            <a href="{{ route('bhw.reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-plus me-1"></i>Create Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="workspace-panel fade-in-card">
                <div class="workspace-panel-header">
                    <h2 class="workspace-panel-title">Preview</h2>
                </div>
                <div class="workspace-panel-body">
                    <div id="previewContent" class="text-muted">Calculating preview...</div>
                </div>
            </div>

            <div class="insight-card fade-in-card">
                <h6>Before You Submit</h6>
                <div class="insight-list">
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Make the title specific so the president can recognize the period and report type quickly.</span></div>
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Use selected patients only when the report should cover a limited set of cases.</span></div>
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Review the preview panel to confirm the month, year, and patient scope before creating the report.</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePatientSelection() {
    const filterSelected = document.getElementById('filter_selected').checked;
    const patientSelection = document.getElementById('patientSelection');
    patientSelection.classList.toggle('d-none', !filterSelected);
    updatePreview();
}

function toggleReportType() {
    const reportType = document.querySelector('input[name="report_type"]:checked').value;
    document.getElementById('healthRecordsPatients').classList.toggle('d-none', reportType !== 'health_records');
    document.getElementById('pregnanciesPatients').classList.toggle('d-none', reportType !== 'pregnancies');
    updatePreview();
}

function updatePreview() {
    const month = document.getElementById('report_month').value;
    const year = document.getElementById('report_year').value;
    const reportType = document.querySelector('input[name="report_type"]:checked').value;
    const allPatients = document.getElementById('filter_all').checked;
    const selectedPatients = Array.from(document.querySelectorAll('.patient-checkbox:checked')).length;
    const monthName = new Date(year, month - 1, 1).toLocaleDateString('en-US', { month: 'long' });
    const reportLabel = reportType === 'health_records' ? 'Health Records' : 'Pregnancies';
    const poolCount = reportType === 'health_records' ? {{ $women->count() }} : {{ $pregnantWomen->count() }};
    const patientLabel = allPatients ? 'All available patients (' + poolCount + ')' : selectedPatients + ' selected patients';

    document.getElementById('previewContent').innerHTML = `
        <div class="table-meta-stack">
            <div><strong>Report type:</strong> ${reportLabel}</div>
            <div><strong>Reporting period:</strong> ${monthName} ${year}</div>
            <div><strong>Patient scope:</strong> ${patientLabel}</div>
            <div><strong>Expected workflow:</strong> Draft -> President review -> Midwife review</div>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {
    toggleReportType();
    togglePatientSelection();
    updatePreview();
});
</script>
@endpush
@endsection
