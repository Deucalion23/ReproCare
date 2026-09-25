@extends('bhw.layout')

@section('title', 'Add Health Record - ReproCare')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Health Record</h1>
        <a href="{{ route('bhw.patient-details', $woman->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add Health Record for {{ $woman->name }}</h5>
        </div>
        <div class="card-body">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form method="POST" action="{{ route('bhw.health-records.store', $woman->id) }}" class="rc-adaptive-form">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient Name</label>
                        <input type="text" class="form-control" value="{{ $woman->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Patient Email</label>
                        <input type="text" class="form-control" value="{{ $woman->email }}" readonly>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Blood Pressure</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control" id="bp_systolic" name="bp_systolic"
                                       value="{{ old('bp_systolic') }}" required placeholder="Systolic">
                                @error('bp_systolic')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic"
                                       value="{{ old('bp_diastolic') }}" required placeholder="Diastolic">
                                @error('bp_diastolic')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               value="{{ old('weight') }}" required min="0" max="300" step="0.1" placeholder="e.g., 65.5">
                        @error('weight')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                        <input type="number" class="form-control" id="heart_rate" name="heart_rate" 
                               value="{{ old('heart_rate') }}" required min="0" max="250" placeholder="e.g., 72">
                        @error('heart_rate')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="temperature" class="form-label">Temperature (°C)</label>
                        <input type="number" class="form-control" id="temperature" name="temperature" 
                               value="{{ old('temperature') }}" required min="30" max="45" step="0.1" placeholder="e.g., 36.5">
                        @error('temperature')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Extended Clinical Fields --}}
                <hr class="my-3" style="border-color:var(--border);">
                <p style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:1rem;">
                    <i class="bi bi-clipboard2-pulse me-1" style="color:var(--primary-light);"></i>Clinical Assessment
                </p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hemoglobin" class="form-label">Hemoglobin Level (g/dL)</label>
                        <input type="number" class="form-control" id="hemoglobin" name="hemoglobin"
                               value="{{ old('hemoglobin') }}" min="1" max="25" step="0.1"
                               placeholder="e.g., 12.5 — Normal ≥ 11 g/dL">
                        @error('hemoglobin')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gestational_age" class="form-label">Gestational Age (weeks)</label>
                        <input type="number" class="form-control" id="gestational_age" name="gestational_age"
                               value="{{ old('gestational_age') }}" min="1" max="45"
                               placeholder="e.g., 28">
                        @error('gestational_age')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="immunization_status" class="form-label">Immunization Status</label>
                        <select class="form-select" id="immunization_status" name="immunization_status">
                            <option value="">— Select —</option>
                            <option value="Complete" {{ old('immunization_status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                            <option value="Incomplete" {{ old('immunization_status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                            <option value="Up to date" {{ old('immunization_status') == 'Up to date' ? 'selected' : '' }}>Up to date</option>
                            <option value="Not started" {{ old('immunization_status') == 'Not started' ? 'selected' : '' }}>Not started</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contraceptive_use" class="form-label">Contraceptive Use</label>
                        <select class="form-select" id="contraceptive_use" name="contraceptive_use">
                            <option value="">— Select —</option>
                            <option value="None" {{ old('contraceptive_use') == 'None' ? 'selected' : '' }}>None</option>
                            <option value="Pills" {{ old('contraceptive_use') == 'Pills' ? 'selected' : '' }}>Pills</option>
                            <option value="IUD" {{ old('contraceptive_use') == 'IUD' ? 'selected' : '' }}>IUD</option>
                            <option value="Condom" {{ old('contraceptive_use') == 'Condom' ? 'selected' : '' }}>Condom</option>
                            <option value="Injectable" {{ old('contraceptive_use') == 'Injectable' ? 'selected' : '' }}>Injectable</option>
                            <option value="Implant" {{ old('contraceptive_use') == 'Implant' ? 'selected' : '' }}>Implant</option>
                            <option value="Natural" {{ old('contraceptive_use') == 'Natural' ? 'selected' : '' }}>Natural Method</option>
                            <option value="Other" {{ old('contraceptive_use') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="lab_results" class="form-label">Lab Results Summary <span style="font-weight:400;">(optional)</span></label>
                    <input type="text" class="form-control" id="lab_results" name="lab_results"
                           value="{{ old('lab_results') }}" placeholder="e.g., CBC normal, urinalysis clear">
                </div>

                <hr class="my-3" style="border-color:var(--border);">
                <p style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:1rem;">
                    <i class="bi bi-check2-square me-1" style="color:var(--primary-light);"></i>Lifestyle Factors
                </p>

                <div class="row">
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
                </div>

                <div class="mb-3">
                    <label for="lifestyle_other" class="form-label">Other Lifestyle Factors</label>
                    <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="2">{{ old('lifestyle_other', old('lifestyle_notes')) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="obstetric_history" class="form-label">Obstetric History</label>
                    <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="3">{{ old('obstetric_history') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Clinical Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                              placeholder="Observations, symptoms, patient concerns...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Risk Level</label>
                        <input type="text" class="form-control" value="Auto-assessed after save" readonly>
                        <small class="form-text text-muted">System will evaluate risk automatically.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Recorded By</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                    </div>
                </div>
                
                <div class="d-flex gap-2 rc-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Health Record
                    </button>
                    <a href="{{ route('bhw.patient-details', $woman->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                    <a href="{{ route('bhw.patients') }}" class="btn btn-outline-info">
                        <i class="bi bi-list"></i> Back to Patients
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">Health Record Guidelines</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Vital Signs</h6>
                    <ul class="mb-0">
                        <li><strong>Blood Pressure:</strong> Normal range 90-120 / 60-80 mmHg</li>
                        <li><strong>Heart Rate:</strong> Normal range 60-100 bpm</li>
                        <li><strong>Temperature:</strong> Normal range 36.1-37.2°C</li>
                        <li><strong>Weight:</strong> Record in kilograms with decimal</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Recording Best Practices</h6>
                    <ul class="mb-0">
                        <li>Be accurate and consistent with measurements</li>
                        <li>Note any unusual symptoms or concerns</li>
                        <li>Include patient complaints or observations</li>
                        <li>Document any medications or treatments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
