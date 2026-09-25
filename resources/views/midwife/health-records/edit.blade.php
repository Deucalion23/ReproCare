@extends('midwife.layout')

@section('title', 'Edit Health Record - ReproCare')

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
</style>
@endpush

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Health Record</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.health-records.show', $healthRecord->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Record
            </a>
            <a href="{{ route('midwife.health-records.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Records
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Edit Patient Health Record</h5>
        </div>
        <div class="card-body">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form action="{{ route('midwife.health-records.update', $healthRecord->id) }}" method="POST" class="rc-adaptive-form">
                @csrf
                @method('PUT')
                
                <!-- Patient Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Patient Information</h6>
                    </div>
                    
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Patient:</strong> {{ $healthRecord->patient_name }}
                            @if($healthRecord->woman)
                                ({{ $healthRecord->woman->email }})
                            @elseif($healthRecord->walkInPatient?->contact_number)
                                ({{ $healthRecord->walkInPatient->contact_number }})
                            @endif
                            <br><small class="text-muted">Original record date: {{ $healthRecord->created_at->format('M j, Y h:i A') }}</small>
                        </div>
                        <input type="hidden" name="patient_type" value="{{ $healthRecord->walk_in_patient_id ? 'walk_in' : 'registered' }}">
                        @if($healthRecord->walk_in_patient_id)
                            <input type="hidden" name="walk_in_patient_id" value="{{ $healthRecord->walk_in_patient_id }}">
                        @else
                            <input type="hidden" name="user_id" value="{{ $healthRecord->user_id }}">
                        @endif
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Vital Signs</h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Blood Pressure <span class="text-danger">*</span></label>
                        @php([$storedSystolic, $storedDiastolic] = array_pad(explode('/', (string) $healthRecord->bp), 2, ''))
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control @error('bp_systolic') is-invalid @enderror"
                                       id="bp_systolic" name="bp_systolic" value="{{ old('bp_systolic', $storedSystolic) }}" required
                                       placeholder="Systolic">
                                @error('bp_systolic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control @error('bp_diastolic') is-invalid @enderror"
                                       id="bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic', $storedDiastolic) }}" required
                                       placeholder="Diastolic">
                                @error('bp_diastolic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <small class="form-text text-muted">Enter systolic and diastolic separately.</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="heart_rate" class="form-label">Heart Rate <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('heart_rate') is-invalid @enderror" 
                                   id="heart_rate" name="heart_rate" value="{{ old('heart_rate', $healthRecord->heart_rate) }}" required
                                   min="40" max="200" placeholder="72">
                            <span class="input-group-text">bpm</span>
                        </div>
                        @error('heart_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="temperature" class="form-label">Temperature <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('temperature') is-invalid @enderror" 
                                   id="temperature" name="temperature" value="{{ old('temperature', $healthRecord->temperature) }}" required
                                   min="35" max="42" step="0.1" placeholder="36.5">
                            <span class="input-group-text">°C</span>
                        </div>
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                   id="weight" name="weight" value="{{ old('weight', $healthRecord->weight) }}" required
                                   min="20" max="300" step="0.1" placeholder="65">
                            <span class="input-group-text">kg</span>
                        </div>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </div>

                <!-- Clinical Assessment -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Clinical Assessment</h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="hemoglobin" class="form-label">Hemoglobin Level (g/dL)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('hemoglobin') is-invalid @enderror" 
                                   id="hemoglobin" name="hemoglobin" value="{{ old('hemoglobin', $healthRecord->hemoglobin) }}"
                                   min="1" max="25" step="0.1" placeholder="12.5">
                            <span class="input-group-text">g/dL</span>
                        </div>
                        @error('hemoglobin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Normal ≥ 11 g/dL</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="immunization_status" class="form-label">Immunization Status</label>
                        <select class="form-select @error('immunization_status') is-invalid @enderror" 
                                id="immunization_status" name="immunization_status">
                            <option value="">— Select —</option>
                            <option value="Complete" {{ old('immunization_status', $healthRecord->immunization_status) == 'Complete' ? 'selected' : '' }}>Complete</option>
                            <option value="Incomplete" {{ old('immunization_status', $healthRecord->immunization_status) == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                            <option value="Up to date" {{ old('immunization_status', $healthRecord->immunization_status) == 'Up to date' ? 'selected' : '' }}>Up to date</option>
                            <option value="Not started" {{ old('immunization_status', $healthRecord->immunization_status) == 'Not started' ? 'selected' : '' }}>Not started</option>
                        </select>
                        @error('immunization_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="contraceptive_use" class="form-label">Contraceptive Use</label>
                        <select class="form-select @error('contraceptive_use') is-invalid @enderror" 
                                id="contraceptive_use" name="contraceptive_use">
                            <option value="">— Select —</option>
                            <option value="None" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'None' ? 'selected' : '' }}>None</option>
                            <option value="Pills" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Pills' ? 'selected' : '' }}>Pills</option>
                            <option value="IUD" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'IUD' ? 'selected' : '' }}>IUD</option>
                            <option value="Condom" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Condom' ? 'selected' : '' }}>Condom</option>
                            <option value="Injectable" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Injectable' ? 'selected' : '' }}>Injectable</option>
                            <option value="Implant" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Implant' ? 'selected' : '' }}>Implant</option>
                            <option value="Natural" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Natural' ? 'selected' : '' }}>Natural Method</option>
                            <option value="Other" {{ old('contraceptive_use', $healthRecord->contraceptive_use) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('contraceptive_use')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="lab_results" class="form-label">Lab Results Summary</label>
                        <input type="text" class="form-control @error('lab_results') is-invalid @enderror" 
                               id="lab_results" name="lab_results" value="{{ old('lab_results', $healthRecord->lab_results) }}"
                               placeholder="e.g., CBC normal, urinalysis clear">
                        @error('lab_results')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Pregnancy Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Pregnancy Information (if applicable)</h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="gestational_age" class="form-label">Gestational Age (weeks)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('gestational_age') is-invalid @enderror" 
                                   id="gestational_age" name="gestational_age" value="{{ old('gestational_age', $healthRecord->gestational_age) }}"
                                   min="1" max="45" placeholder="28">
                            <span class="input-group-text">weeks</span>
                        </div>
                        @error('gestational_age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">e.g., 28 weeks</small>
                    </div>
                    
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Lifestyle Factors</h6>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_smoking" name="lifestyle_smoking" value="1"
                                {{ old('lifestyle_smoking', in_array(old('smoking_status', $healthRecord->smoking_status), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_smoking">Smoking</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_alcohol" name="lifestyle_alcohol" value="1"
                                {{ old('lifestyle_alcohol', in_array(old('alcohol_use', $healthRecord->alcohol_use), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_alcohol">Drinking Alcohol</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="lifestyle_drugs" name="lifestyle_drugs" value="1"
                                {{ old('lifestyle_drugs', in_array(old('drug_use', $healthRecord->drug_use), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_drugs">Drugs</label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="lifestyle_other" class="form-label">Other Lifestyle Factors</label>
                        <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="2">{{ old('lifestyle_other', $healthRecord->lifestyle_notes) }}</textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="obstetric_history" class="form-label">Obstetric History</label>
                        <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="3">{{ old('obstetric_history', $healthRecord->obstetric_history) }}</textarea>
                    </div>
                </div>

                <!-- Risk Assessment (Automated) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Risk Assessment</h6>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Risk level is automatically calculated</strong> based on blood pressure, hemoglobin, missed checkups, and pregnancy status. Current risk level: <strong>{{ $healthRecord->risk_level }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Additional Information</h6>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label">Clinical Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4" placeholder="Enter clinical observations, symptoms, recommendations...">{{ old('notes', $healthRecord->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="recommendations" class="form-label">Recommendations</label>
                        <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                  id="recommendations" name="recommendations" rows="3" placeholder="Medications, follow-up appointments, lifestyle changes...">{{ old('recommendations', $healthRecord->recommendations) }}</textarea>
                        @error('recommendations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Edit Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Edit Notice:</strong> You are editing a health record originally created on {{ $healthRecord->created_at->format('M j, Y h:i A') }}. 
                            Any changes will be logged and the original creation timestamp will be preserved.
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between rc-form-actions">
                    <div>
                        <a href="{{ route('midwife.health-records.show', $healthRecord->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <a href="{{ route('midwife.health-records.create', $healthRecord->user_id) }}" class="btn btn-outline-info">
                            <i class="bi bi-plus"></i> Add New Record Instead
                        </a>
                    </div>
                    <div>
                        <button type="reset" class="btn btn-outline-warning me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Changes
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Health Record
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
