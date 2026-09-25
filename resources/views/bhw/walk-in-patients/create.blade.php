@extends('bhw.layout')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">Record Walk-in Patient</h2>
        <a href="{{ route('bhw.patients', ['filter' => 'unregistered']) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Women
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('bhw.walk-in-patients.store') }}">
                @csrf

                <h5 class="mb-3">Patient Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" name="middle_initial" class="form-control" maxlength="10">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Contact Number <span class="text-muted">(for SMS alerts)</span></label>
                        <input type="text" name="contact_number" class="form-control" maxlength="20" placeholder="09171234567" pattern="(\+?63|0)?9\d{9}" title="Valid PH mobile number, e.g. 09171234567">
                        <small class="text-muted">Needed so the midwife can send checkup and risk SMS alerts.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Barangay</label>
                        <input type="text" name="barangay" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sitio / Street / Purok</label>
                        <input type="text" name="purok" class="form-control" value="{{ old('purok') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Visit Information</h5>
                <div class="mb-3">
                    <label class="form-label">Reason for Visit</label>
                    <textarea name="reason_for_visit" class="form-control" rows="2" placeholder="e.g., Prenatal checkup, follow-up, consultation..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Any additional observations or notes..."></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('bhw.patients', ['filter' => 'unregistered']) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Record Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
