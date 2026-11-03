@extends('rhu.layout')

@section('title', 'Edit Maternal Death Record - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Edit Maternal Death Record
            </div>
            <p class="page-hero-subtitle">
                Modify details for DOH Maternal Death Surveillance and Response (MDSR).
            </p>
        </div>
        <a href="{{ route('rhu.maternal-deaths.show', $death->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Cancel & Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <h6 class="alert-heading fw-bold mb-2">Please resolve the following errors:</h6>
        <ul class="mb-0 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body">
        <form method="POST" action="{{ route('rhu.maternal-deaths.update', $death->id) }}">
            @csrf
            @method('PUT')

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Patient Information
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Link Enrolled Patient (Optional)</label>
                    <select name="user_id" id="patientSelect" class="form-select">
                        <option value="">None (Unlinked / External Patient)</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                    data-name="{{ $patient->name }}"
                                    data-dob="{{ $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '' }}"
                                    data-barangay="{{ $patient->barangay }}"
                                    {{ $death->user_id == $patient->id ? 'selected' : '' }}>
                                {{ $patient->name }} ({{ $patient->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Patient Name</label>
                    <input type="text" name="patient_name" id="patientNameInput" class="form-control" value="{{ old('patient_name', $death->patient_name) }}" placeholder="Full Name" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Age at Death</label>
                    <input type="number" name="age_at_death" id="ageInput" class="form-control" value="{{ old('age_at_death', $death->age_at_death) }}" min="1" max="120" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Barangay</label>
                    <select name="barangay" id="barangaySelect" class="form-select" required>
                        <option value="" disabled>Select Barangay</option>
                        @forelse(($barangays ?? collect()) as $brgy)
                            <option value="{{ $brgy->name }}" {{ old('barangay', $death->barangay) === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @empty
                            <option value="Burgos" {{ old('barangay', $death->barangay) === 'Burgos' ? 'selected' : '' }}>Barangay Burgos</option>
                            <option value="Padlan" {{ old('barangay', $death->barangay) === 'Padlan' ? 'selected' : '' }}>Barangay Padlan</option>
                        @endforelse
                        @if(!empty($death->barangay) && !(($barangays ?? collect())->contains('name', $death->barangay)))
                            <option value="{{ $death->barangay }}" selected>{{ $death->barangay }} (legacy)</option>
                        @endif
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Purok</label>
                    <select name="purok_id" class="form-select" required>
                        <option value="" disabled>Select Purok</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id', $death->purok_id) == $purok->id ? 'selected' : '' }}>
                                {{ $purok->name }} ({{ $purok->barangay }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Event Details
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Date of Death</label>
                    <input type="date" name="death_date" class="form-control" value="{{ old('death_date', $death->death_date ? $death->death_date->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Time of Death (Optional)</label>
                    <input type="time" name="death_time" class="form-control" value="{{ old('death_time', $death->death_time) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Place of Death</label>
                    @php($currentPlace = old('place_of_death', $death->place_of_death))
                    <select name="place_of_death" class="form-select" required>
                        @if(!array_key_exists((string) $currentPlace, \App\Models\MaternalDeath::placeLabels()))
                            <option value="{{ $currentPlace }}" selected>{{ $currentPlace }} (legacy)</option>
                        @endif
                        @foreach(\App\Models\MaternalDeath::placeLabels() as $value => $label)
                            <option value="{{ $value }}" {{ $currentPlace === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Timing of Death</label>
                    @php($currentTiming = old('death_timing', $death->death_timing))
                    <select name="death_timing" class="form-select" required>
                        @if(!array_key_exists((string) $currentTiming, \App\Models\MaternalDeath::timingLabels()))
                            <option value="{{ $currentTiming }}" selected>{{ $currentTiming }} (legacy)</option>
                        @endif
                        @foreach(\App\Models\MaternalDeath::timingLabels() as $value => $label)
                            <option value="{{ $value }}" {{ $currentTiming === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Primary Cause Category</label>
                    @php($currentCause = old('cause_category', $death->cause_category))
                    <select name="cause_category" class="form-select" required>
                        @if(!array_key_exists((string) $currentCause, \App\Models\MaternalDeath::causeLabels()))
                            <option value="{{ $currentCause }}" selected>{{ $currentCause }} (legacy)</option>
                        @endif
                        @foreach(\App\Models\MaternalDeath::causeLabels() as $value => $label)
                            <option value="{{ $value }}" {{ $currentCause === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label required-label">Clinical Cause of Death Details</label>
                <textarea name="cause_of_death" rows="3" class="form-control" placeholder="Describe clinical symptoms, complications, and medical diagnosis leading to death." required>{{ old('cause_of_death', $death->cause_of_death) }}</textarea>
            </div>

            <div class="mb-5">
                <label class="form-label">Additional Surveillance Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional notes on prenatal history, delay in care, or community audit.">{{ old('notes', $death->notes) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4 text-dark fw-600">
                    <i class="bi bi-save me-1"></i> Update Record
                </button>
                <a href="{{ route('rhu.maternal-deaths.show', $death->id) }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientSelect = document.getElementById('patientSelect');
    const nameInput = document.getElementById('patientNameInput');
    const dobInput = document.getElementById('ageInput');
    const barangaySelect = document.getElementById('barangaySelect');

    patientSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            nameInput.value = option.dataset.name;
            barangaySelect.value = option.dataset.barangay;
            
            // Calculate age from DOB if available
            const dob = option.dataset.dob;
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                dobInput.value = age > 0 ? age : '';
            } else {
                dobInput.value = '';
            }
        }
    });
});
</script>
@endpush

@endsection
