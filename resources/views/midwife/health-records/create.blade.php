@extends('midwife.layout')

@section('title', 'Add Health Record - ReproCare')

@push('styles')
<style>
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance:none;
        margin:0;
    }
    input[type=number] {
        -moz-appearance:textfield;
        appearance:textfield;
    }
    .health-form .form-control,
    .health-form .form-select {
        height:42px;
        border-radius:12px;
        border:1px solid var(--color-border);
        background:var(--color-bg);
        color:var(--color-text);
        font-size:0.875rem;
    }
    .health-form .form-control:focus,
    .health-form .form-select:focus {
        border-color:var(--color-primary);
        background:var(--color-surface);
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent);
    }
    .health-form textarea.form-control {
        height:auto;
    }
    .health-form .input-group-text {
        height:42px;
        border-radius:0 12px 12px 0;
        background:var(--color-primary-soft);
        border:1px solid var(--color-border);
        color:var(--color-primary-text);
        font-weight:600;
        font-size:0.825rem;
    }
    .health-form .input-group .form-control {
        border-radius:12px 0 0 12px !important;
    }
</style>
@endpush

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">@if($woman)
                    Add Health Record for {{ $woman->name }}
                @else
                    Add Health Record
                @endif
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Complete patient vital signs and physical assessments
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.health-records.archived') }}" class="btn-hero-secondary">
                <i class="bi bi-archive-fill me-1"></i> Archived
            </a>
            @if($woman)
                <a href="{{ route('midwife.patient-details', $woman->id) }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Patient
                </a>
            @else
                <a href="{{ route('midwife.health-records.index') }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Records
                </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
         style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:16px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════════════════════════
     HEALTH RECORD FORM CARD
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4" style="border:1px solid var(--color-border); border-radius:24px; background:var(--color-surface); box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent); overflow:hidden;">
    <div class="card-header" style="background:var(--color-surface); border-bottom:1px solid var(--color-border); padding:1.25rem 1.5rem;">
        <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text);">Health Record Information
        </h5>
        <small style="color:var(--color-text-muted);">Input patient vital signs, measurements, and clinical observations</small>
    </div>
    <div class="card-body p-4 health-form">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form action="{{ route('midwife.health-records.store') }}" method="POST" class="rc-adaptive-form">
                @csrf
                
                {{-- ═══════════════════════════════
                     PATIENT INFORMATION
                ════════════════════════════════ --}}
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                            <i class="bi bi-person-fill me-1" style="color:var(--primary);"></i> Patient Information
                        </div>
                    </div>
                    
                    @if($woman)
                        <input type="hidden" name="patient_type" value="registered">
                        <input type="hidden" name="user_id" value="{{ $woman->id }}">
                        <div class="col-12">
                            <div class="p-3 mb-3" style="background:linear-gradient(135deg, color-mix(in srgb, var(--color-info) 10%, transparent), color-mix(in srgb, var(--color-primary) 5%, transparent)); border:1px solid color-mix(in srgb, var(--color-info) 30%, transparent); border-radius:12px;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--info),var(--primary));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="bi bi-person" style="color:var(--color-on-solid);font-size:1.2rem;"></i>
                                    </div>
                                    <div>
                                        <small style="color:var(--text-muted); text-transform:uppercase; font-size:0.7rem; letter-spacing:0.5px;">Patient</small>
                                        <div style="font-weight:600; color:var(--text);">{{ $woman->name }}</div>
                                        <small style="color:var(--text-muted);">{{ $woman->email }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-12 mb-3">
                            <label class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                                Patient Type <span style="color:var(--danger);">*</span>
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <input type="radio" class="btn-check" name="patient_type" id="record_patient_type_registered" value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="record_patient_type_registered">Enrolled</label>
                                <input type="radio" class="btn-check" name="patient_type" id="record_patient_type_unregistered" value="walk_in" {{ old('patient_type') === 'walk_in' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="record_patient_type_unregistered">Unlinked</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="user_id" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                                Select Patient <span style="color:var(--danger);">*</span>
                            </label>
                            <div class="mb-2">
                                <input type="text" class="form-control" id="patientSearch" 
                                       placeholder="Search patients by name or email..."
                                       style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                            </div>
                            <select class="form-select @error('user_id') is-invalid @enderror"
                                    id="user_id" name="user_id" required
                                    style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                                <option value="" style="background:var(--bg-card); color:var(--text);">Choose a patient...</option>
                                @if($women)
                                    @foreach($women as $woman)
                                        <option value="{{ $woman->id }}"
                                                data-search="{{ strtolower($woman->name . ' ' . $woman->email) }}"
                                                {{ old('user_id') == $woman->id ? 'selected' : '' }}
                                                style="background:var(--bg-card); color:var(--text);">
                                            {{ $woman->name }} - {{ $woman->email }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3 d-none" id="walkInRecordSearchWrap">
                            <label for="walkInPatientSearch" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                                Search Unlinked Woman
                            </label>
                            <input type="text" class="form-control" id="walkInPatientSearch"
                                   placeholder="Search unlinked women by name, contact, or barangay..."
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                        </div>
                        <div class="col-md-12 mb-3 d-none" id="unregisteredRecordSelectWrap">
                            <label for="walk_in_patient_id" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                                Select Unlinked Woman <span style="color:var(--danger);">*</span>
                            </label>
                            <select class="form-select @error('walk_in_patient_id') is-invalid @enderror"
                                    id="walk_in_patient_id" name="walk_in_patient_id"
                                    style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                                <option value="">Choose an unlinked woman...</option>
                                @foreach(($walkInPatients ?? collect()) as $patient)
                                    <option value="{{ $patient->id }}" data-search="{{ strtolower(($patient->full_name ?? '') . ' ' . ($patient->contact_number ?? '') . ' ' . ($patient->barangay ?? '')) }}" {{ old('walk_in_patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}{{ $patient->barangay ? ' - ' . $patient->barangay : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('walk_in_patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <hr style="border-color:var(--border-color); margin:1.5rem 0;">

                {{-- ═══════════════════════════════
                     VITAL SIGNS
                ════════════════════════════════ --}}
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                            <i class="bi bi-heart-pulse-fill me-1" style="color:var(--danger);"></i> Vital Signs
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                            Blood Pressure <span style="color:var(--danger);">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control @error('bp_systolic') is-invalid @enderror"
                                       id="bp_systolic" name="bp_systolic" value="{{ old('bp_systolic') }}" required
                                       placeholder="Systolic"
                                       style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                                @error('bp_systolic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control @error('bp_diastolic') is-invalid @enderror"
                                       id="bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic') }}" required
                                       placeholder="Diastolic"
                                       style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                                @error('bp_diastolic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="heart_rate" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                            Heart Rate <span style="color:var(--danger);">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('heart_rate') is-invalid @enderror" 
                                   id="heart_rate" name="heart_rate" value="{{ old('heart_rate') }}" required
                                   min="40" max="200" placeholder="72"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">bpm</span>
                        </div>
                        @error('heart_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="temperature" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                            Temperature <span style="color:var(--danger);">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('temperature') is-invalid @enderror" 
                                   id="temperature" name="temperature" value="{{ old('temperature') }}" required
                                   min="35" max="42" step="0.1" placeholder="36.5"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">°C</span>
                        </div>
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label for="weight" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">
                            Weight <span style="color:var(--danger);">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                   id="weight" name="weight" value="{{ old('weight') }}" required
                                   min="20" max="300" step="0.1" placeholder="65"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">kg</span>
                        </div>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="height" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Height</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('height') is-invalid @enderror" 
                                   id="height" name="height" value="{{ old('height') }}"
                                   min="100" max="250" placeholder="160"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">cm</span>
                        </div>
                        <div class="form-text">Below 122 cm (4 ft) flags short-stature risk.</div>
                        @error('height')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="oxygen_saturation" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Oxygen Saturation</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('oxygen_saturation') is-invalid @enderror" 
                                   id="oxygen_saturation" name="oxygen_saturation" value="{{ old('oxygen_saturation', 98) }}"
                                   min="70" max="100" placeholder="98"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">%</span>
                        </div>
                        @error('oxygen_saturation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="bmi_display" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">BMI</label>
                        <input type="text" class="form-control" id="bmi_display" readonly
                               style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                        <small id="bmi_category" style="color:var(--text-muted); font-size:0.8rem;">Waiting for weight and height</small>
                    </div>
                </div>

                <hr style="border-color:var(--border-color); margin:1.5rem 0;">

                {{-- ═══════════════════════════════
                     LAB & HEALTH DATA
                ════════════════════════════════ --}}
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                            <i class="bi bi-clipboard-data-fill me-1" style="color:var(--warning);"></i> Laboratory & Health Data
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="hemoglobin" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Hemoglobin (g/dL)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('hemoglobin') is-invalid @enderror" 
                                   id="hemoglobin" name="hemoglobin" value="{{ old('hemoglobin') }}"
                                   min="1" max="25" step="0.1" placeholder="12.5"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">g/dL</span>
                        </div>
                        @error('hemoglobin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="immunization_status" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Immunization Status</label>
                        <select class="form-select @error('immunization_status') is-invalid @enderror" 
                                id="immunization_status" name="immunization_status"
                                style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                            <option value="" style="background:var(--bg-card); color:var(--text);">— Select —</option>
                            <option value="Complete" {{ old('immunization_status') == 'Complete' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Complete</option>
                            <option value="Incomplete" {{ old('immunization_status') == 'Incomplete' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Incomplete</option>
                            <option value="Up to date" {{ old('immunization_status') == 'Up to date' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Up to date</option>
                            <option value="Not started" {{ old('immunization_status') == 'Not started' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Not started</option>
                        </select>
                        @error('immunization_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="contraceptive_use" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Contraceptive Use</label>
                        <select class="form-select @error('contraceptive_use') is-invalid @enderror" 
                                id="contraceptive_use" name="contraceptive_use"
                                style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                            <option value="" style="background:var(--bg-card); color:var(--text);">— Select —</option>
                            <option value="None" {{ old('contraceptive_use') == 'None' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">None</option>
                            <option value="Pills" {{ old('contraceptive_use') == 'Pills' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Pills</option>
                            <option value="IUD" {{ old('contraceptive_use') == 'IUD' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">IUD</option>
                            <option value="Condom" {{ old('contraceptive_use') == 'Condom' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Condom</option>
                            <option value="Injectable" {{ old('contraceptive_use') == 'Injectable' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Injectable</option>
                            <option value="Implant" {{ old('contraceptive_use') == 'Implant' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Implant</option>
                            <option value="Natural" {{ old('contraceptive_use') == 'Natural' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Natural Method</option>
                            <option value="Other" {{ old('contraceptive_use') == 'Other' ? 'selected' : '' }} style="background:var(--bg-card); color:var(--text);">Other</option>
                        </select>
                        @error('contraceptive_use')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="lab_results" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Lab Results</label>
                        <input type="text" class="form-control @error('lab_results') is-invalid @enderror" 
                               id="lab_results" name="lab_results" value="{{ old('lab_results') }}"
                               placeholder="e.g., CBC normal, urinalysis clear"
                               style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">
                        @error('lab_results')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="gestational_age" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Gestational Age (weeks)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('gestational_age') is-invalid @enderror" 
                                   id="gestational_age" name="gestational_age" value="{{ old('gestational_age') }}"
                                   min="1" max="45" placeholder="28"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">weeks</span>
                        </div>
                        @error('gestational_age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="fetal_heart_rate" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Fetal Heart Rate</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('fetal_heart_rate') is-invalid @enderror" 
                                   id="fetal_heart_rate" name="fetal_heart_rate" value="{{ old('fetal_heart_rate') }}"
                                   min="100" max="180" placeholder="140"
                                   style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px 0 0 10px;">
                            <span class="input-group-text" style="background:var(--bg-card2); border:1px solid var(--input-border); color:var(--text-muted);">bpm</span>
                        </div>
                        @error('fetal_heart_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr style="border-color:var(--border-color); margin:1.5rem 0;">

                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                            <i class="bi bi-check2-square me-1" style="color:var(--primary);"></i> Lifestyle Factors
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_smoking" name="lifestyle_smoking" value="1"
                                {{ old('lifestyle_smoking') || in_array(old('smoking_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_smoking">Smoking</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_alcohol" name="lifestyle_alcohol" value="1"
                                {{ old('lifestyle_alcohol') || in_array(old('alcohol_use'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_alcohol">Drinking Alcohol</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_drugs" name="lifestyle_drugs" value="1"
                                {{ old('lifestyle_drugs') || in_array(old('drug_use'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_drugs">Drugs</label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="lifestyle_other" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Other Lifestyle Factors</label>
                        <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="2"
                                  style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">{{ old('lifestyle_other', old('lifestyle_notes')) }}</textarea>
                    </div>
                </div>

                <hr style="border-color:var(--border-color); margin:1.5rem 0;">

                {{-- ═══════════════════════════════
                     NOTES & OBSERVATIONS
                ════════════════════════════════ --}}
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.75rem;">
                            <i class="bi bi-chat-square-text-fill me-1" style="color:var(--success);"></i> Notes & Observations
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Clinical Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" placeholder="Enter clinical observations, symptoms..."
                                  style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <label for="recommendations" class="form-label" style="font-size:0.8rem; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Recommendations</label>
                        <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                  id="recommendations" name="recommendations" rows="2" placeholder="Medications, follow-up appointments..."
                                  style="background:var(--bg-input); border:1px solid var(--input-border); color:var(--text); border-radius:10px;">{{ old('recommendations') }}</textarea>
                        @error('recommendations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="p-3 mb-4" style="background:linear-gradient(135deg, color-mix(in srgb, var(--color-info) 10%, transparent), color-mix(in srgb, var(--color-primary) 5%, transparent)); border:1px solid color-mix(in srgb, var(--color-info) 30%, transparent); border-radius:12px;">
{{-- ═══════════════════════════════
                     FORM ACTIONS
                ════════════════════════════════ --}}
                <div class="d-flex justify-content-between align-items-center pt-3 rc-form-actions" style="border-top:1px solid var(--border-color);">
                    <a href="{{ route('midwife.health-records.index') }}" 
                       class="btn d-flex align-items-center" 
                       style="background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text-muted); border-radius:12px; height:42px; padding:0 1.25rem; font-weight:600;">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <div class="d-flex gap-2">
                        <button type="reset" 
                                class="btn d-flex align-items-center" 
                                style="background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning-text); border-radius:12px; height:42px; padding:0 1.25rem; font-weight:600;">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </button>
                        <button type="submit" 
                                class="btn d-flex align-items-center"
                                style="background:linear-gradient(135deg, var(--color-primary), var(--color-primary-text)); color:var(--color-on-solid); border:none; border-radius:12px; height:42px; padding:0 1.75rem; font-weight:600; box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);">
                            <i class="bi bi-check-circle me-1"></i> Save Health Record
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const searchInput = document.getElementById('patientSearch');
    const patientSelect = document.getElementById('user_id');
    const registeredType = document.getElementById('record_patient_type_registered');
    const unregisteredType = document.getElementById('record_patient_type_unregistered');
    const unregisteredRecordSelectWrap = document.getElementById('unregisteredRecordSelectWrap');
    const walkInSelect = document.getElementById('walk_in_patient_id');
    const walkInSearchInput = document.getElementById('walkInPatientSearch');
    const walkInRecordSearchWrap = document.getElementById('walkInRecordSearchWrap');
    const weightInput = document.getElementById('weight');
    const heightInput = document.getElementById('height');
    const bmiDisplay = document.getElementById('bmi_display');
    const bmiCategory = document.getElementById('bmi_category');

    if (searchInput && patientSelect) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = patientSelect.querySelectorAll('option');

            options.forEach(function(option) {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const searchData = option.getAttribute('data-search') || '';
                option.style.display = searchData.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    function syncPatientType() {
        if (!registeredType || !unregisteredType || !patientSelect || !unregisteredRecordSelectWrap) {
            return;
        }

        const isRegistered = registeredType.checked;
        patientSelect.closest('.col-md-12').classList.toggle('d-none', !isRegistered);
        unregisteredRecordSelectWrap.classList.toggle('d-none', isRegistered);
        walkInRecordSearchWrap?.classList.toggle('d-none', isRegistered);
        patientSelect.disabled = !isRegistered;
        patientSelect.required = isRegistered;
        if (searchInput) {
            searchInput.closest('.mb-2').classList.toggle('d-none', !isRegistered);
        }
        if (walkInSelect) {
            walkInSelect.disabled = isRegistered;
            walkInSelect.required = !isRegistered;
        }
    }

    if (walkInSearchInput && walkInSelect) {
        walkInSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = walkInSelect.querySelectorAll('option');

            options.forEach(function(option) {
                if (option.value === '') {
                    option.hidden = false;
                    return;
                }

                const searchData = option.getAttribute('data-search') || '';
                option.hidden = !searchData.includes(searchTerm);
            });
        });
    }

    function updateBmi() {
        const weight = parseFloat(weightInput?.value || '');
        const height = parseFloat(heightInput?.value || '');

        if (!weight || !height) {
            if (bmiDisplay) bmiDisplay.value = '';
            if (bmiCategory) bmiCategory.textContent = 'Waiting for weight and height';
            return;
        }

        const bmi = weight / Math.pow(height / 100, 2);
        let label = 'Normal';
        if (bmi < 18.5) label = 'Malnourished';
        else if (bmi >= 30) label = 'Obese';

        if (bmiDisplay) bmiDisplay.value = bmi.toFixed(2);
        if (bmiCategory) bmiCategory.textContent = `${bmi.toFixed(2)} BMI - ${label}`;
    }

    registeredType?.addEventListener('change', syncPatientType);
    unregisteredType?.addEventListener('change', syncPatientType);
    weightInput?.addEventListener('input', updateBmi);
    heightInput?.addEventListener('input', updateBmi);
    syncPatientType();
    updateBmi();
</script>
@endpush

@endsection
