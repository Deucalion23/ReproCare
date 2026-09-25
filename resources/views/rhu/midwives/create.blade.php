@extends('rhu.layout')

@section('title', 'Register Midwife - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Register Midwife
            </div>
            <p class="page-hero-subtitle">
                Create a new midwife staff account for the RHU clinic.
            </p>
        </div>
        <a href="{{ route('rhu.midwives.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Midwife Management
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
        <form method="POST" action="{{ route('rhu.midwives.store') }}">
            @csrf

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Credentials
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="midwife@reprocare.gov.ph" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Profile Information
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label required-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="e.g. Elena" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial') }}" placeholder="e.g. M" maxlength="1">
                </div>

                <div class="col-md-5">
                    <label class="form-label required-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" placeholder="e.g. Perez" required>
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <label class="form-label required-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" value="{{ old('contact_number') }}" placeholder="e.g. 09123456789" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Home Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="e.g. 123 Sampaguita St, Barangay Burgos">
                </div>
            </div>

            <h6 class="fw-800 mt-4 mb-2">Credential Provisioning</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label required-label">PRC / DOH License No.</label>
                    <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}" placeholder="e.g. 0123456" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">License Expiry</label>
                    <input type="date" name="license_expiry" class="form-control" value="{{ old('license_expiry') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="e.g. Maternal Health">
                </div>
            </div>

            <h6 class="fw-800 mt-4 mb-2">Facility &amp; Catchment Assignment</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Facility Assignment</label>
                    <input type="text" name="rhu_assignment" class="form-control" value="{{ old('rhu_assignment', 'Rural Health Unit 1') }}" placeholder="e.g. RHU I Padlan">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Primary Barangay</label>
                    <select name="assigned_barangay" class="form-select">
                        <option value="">— Select —</option>
                        @foreach(($barangays ?? collect()) as $b)
                            <option value="{{ $b->name }}" {{ old('assigned_barangay') === $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catchment Barangays (oversight)</label>
                    <select name="catchment_barangays[]" class="form-select" multiple size="4">
                        @foreach(($barangays ?? collect()) as $b)
                            <option value="{{ $b->name }}" {{ in_array($b->name, old('catchment_barangays', [])) ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Register Account
                </button>
                <a href="{{ route('rhu.midwives.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
