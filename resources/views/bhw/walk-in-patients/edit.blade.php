@extends('bhw.layout')

@section('title', 'Edit Walk-in Patient - BHW Portal | ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Edit Walk-in Patient
            </h1>
            <p class="page-subtitle">Update patient information</p>
        </div>
        @php
            $backRoute = request('from') === 'pregnancies'
                ? route('bhw.pregnancies.index')
                : (request('from') === 'women'
                    ? route('bhw.patients')
                    : route('bhw.walk-in-patients.show', $patient->id));
        @endphp
        <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow fade-in-card">
        <div class="card-body">
            <form method="POST" action="{{ route('bhw.walk-in-patients.update', $patient->id) }}">
                @method('PUT')
                @csrf
                <input type="hidden" name="from" value="{{ request('from') }}">

                <h5 class="mb-3">Patient Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" required value="{{ $patient->first_name }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" name="middle_initial" class="form-control @error('middle_initial') is-invalid @enderror" maxlength="10" value="{{ $patient->middle_initial ?? '' }}">
                        @error('middle_initial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" required value="{{ $patient->last_name }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ $patient->date_of_birth?->format('Y-m-d') ?? '' }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" maxlength="20" value="{{ $patient->contact_number ?? '' }}">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Barangay</label>
                        <input type="text" name="barangay" class="form-control @error('barangay') is-invalid @enderror" value="{{ $patient->barangay ?? '' }}">
                        @error('barangay')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sitio / Street / Purok</label>
                        <input type="text" name="purok" class="form-control @error('purok') is-invalid @enderror" value="{{ old('purok', $patient->purok?->name ?? '') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                        @error('purok')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ $patient->address ?? '' }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Visit Information</h5>
                <div class="mb-3">
                    <label class="form-label">Reason for Visit</label>
                    <textarea name="reason_for_visit" class="form-control @error('reason_for_visit') is-invalid @enderror" rows="2" placeholder="e.g., Prenatal checkup, follow-up, consultation...">{{ $patient->reason_for_visit ?? '' }}</textarea>
                    @error('reason_for_visit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('bhw.walk-in-patients.show', $patient->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
