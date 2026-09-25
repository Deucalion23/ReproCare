@extends('midwife.layout')

@section('title', 'Edit Checkup - ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Checkup</h1>
        <div>
            <a href="{{ route('midwife.checkups.show', $checkup->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Details
            </a>
            <a href="{{ route('midwife.checkups.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Checkups
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Edit Checkup for {{ $checkup->patient_name }}
            </h5>
        </div>
        <div class="card-body">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form action="{{ route('midwife.checkups.update', $checkup->id) }}" method="POST" class="rc-adaptive-form">
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
                            <strong>Patient:</strong> {{ $checkup->patient_name }}
                            @if($checkup->woman)
                                ({{ $checkup->woman->email }})
                            @elseif($checkup->walkInPatient?->contact_number)
                                ({{ $checkup->walkInPatient->contact_number }})
                            @endif
                        </div>
                        <input type="hidden" name="patient_type" value="{{ $checkup->walk_in_patient_id ? 'walk_in' : 'registered' }}">
                        @if($checkup->walk_in_patient_id)
                            <input type="hidden" name="walk_in_patient_id" value="{{ $checkup->walk_in_patient_id }}">
                        @else
                            <input type="hidden" name="user_id" value="{{ $checkup->user_id }}">
                        @endif
                        <input type="hidden" name="midwife_id" value="{{ $checkup->midwife_id }}">
                    </div>
                </div>

                <!-- Checkup Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Checkup Details</h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror" 
                               id="scheduled_date" name="scheduled_date" 
                               value="{{ old('scheduled_date', $checkup->scheduled_date ? $checkup->scheduled_date->format('Y-m-d') : '') }}" 
                               min="{{ now()->format('Y-m-d') }}" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Cannot be in the past</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="scheduled_time" class="form-label">Scheduled Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('scheduled_time') is-invalid @enderror" 
                               id="scheduled_time" name="scheduled_time" 
                               value="{{ old('scheduled_time', $checkup->scheduled_time ? \Carbon\Carbon::parse($checkup->scheduled_time)->format('H:i') : '') }}" required>
                        @error('scheduled_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Working hours: 8:00 AM - 5:00 PM</small>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="purpose" class="form-label">Purpose of Checkup <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                  id="purpose" name="purpose" rows="3" required
                                  placeholder="Describe the reason for this checkup...">{{ old('purpose', $checkup->purpose) }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Additional Notes</h6>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label">Special Instructions</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Any special instructions or preparations needed...">{{ old('notes', $checkup->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Status</h6>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="status" class="form-label">Checkup Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status">
                            <option value="Scheduled" {{ old('status', $checkup->status) == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="Completed" {{ old('status', $checkup->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Missed" {{ old('status', $checkup->status) == 'Missed' ? 'selected' : '' }}>Missed</option>
                            <option value="Cancelled" {{ old('status', $checkup->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="Rescheduled" {{ old('status', $checkup->status) == 'Rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('midwife.checkups.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <div>
                        <button type="reset" class="btn btn-outline-warning me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Checkup
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
