@extends('user.layout')

@section('title', 'Add Health Record - ReproCare')

@section('user-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Health Record</h1>
        <a href="{{ route('user.health-records') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Records
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
            <h5 class="mb-0">Self-Record Your Health Data</h5>
        </div>
        <div class="card-body">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form method="POST" action="{{ route('user.health-records.store') }}" class="rc-adaptive-form">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="bp_systolic" class="form-label fw-bold">Systolic <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="bp_systolic" name="bp_systolic" required min="50" max="300" placeholder="120">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="bp_diastolic" class="form-label fw-bold">Diastolic <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic" required min="30" max="200" placeholder="80">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label fw-bold">Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="weight" name="weight" required min="0" max="300" step="0.1" placeholder="e.g., 65.5">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="heart_rate" class="form-label fw-bold">Heart Rate (bpm) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="heart_rate" name="heart_rate" required min="0" max="250" placeholder="e.g., 72">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="temperature" class="form-label fw-bold">Temperature (°C) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="temperature" name="temperature" required min="30" max="45" step="0.1" placeholder="e.g., 36.5">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label fw-bold">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any observations or notes..."></textarea>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> This record will be marked as self-recorded. Health workers (BHW or Midwife) can review and update the risk level assessment.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('user.health-records') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
