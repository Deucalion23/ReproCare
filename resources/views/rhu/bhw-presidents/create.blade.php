@extends('rhu.layout')

@section('title', 'Register BHW President - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Register BHW President
            </div>
            <p class="page-hero-subtitle">
                Create a new Barangay Health Worker President account.
            </p>
        </div>
        <a href="{{ route('rhu.bhw-presidents.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to BHW Presidents
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

@include('includes.president-conflict-alert')

<div class="card fade-in-card">
    <div class="card-body">
        <form method="POST" action="{{ route('rhu.bhw-presidents.store') }}">
            @csrf

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Credentials
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="president@reprocare.gov.ph" required>
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
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="e.g. Patricia" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial') }}" placeholder="e.g. L" maxlength="1">
                </div>

                <div class="col-md-5">
                    <label class="form-label required-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" placeholder="e.g. Cruz" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
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

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Location Assignment
            </h5>

            <div class="row g-3 mb-5">
                <div class="col-md-6">
                    <label class="form-label required-label">Assigned Barangay</label>
                    <select name="barangay" class="form-select" required>
                        <option value="" disabled selected>Select Barangay</option>
                        @forelse(($barangays ?? collect()) as $brgy)
                            <option value="{{ $brgy->name }}" {{ old('barangay') === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @empty
                            <option value="Burgos" {{ old('barangay') === 'Burgos' ? 'selected' : '' }}>Barangay Burgos</option>
                            <option value="Padlan" {{ old('barangay') === 'Padlan' ? 'selected' : '' }}>Barangay Padlan</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Register Account
                </button>
                <a href="{{ route('rhu.bhw-presidents.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

{{-- ── Promote an existing BHW instead of registering a new account ── --}}
<div class="card fade-in-card mt-4">
    <div class="card-body">
        <h5 class="fw-700 mb-2" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Or Promote an Existing BHW
        </h5>
        <p class="text-muted small mb-3">
            Select a BHW (e.g. Ana M. Malasan) and the Barangay lookup. The system blocks the
            promotion when that barangay already holds an active President.
        </p>
        <form method="POST" action="{{ route('rhu.bhw-presidents.promote') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required-label">BHW to Promote</label>
                    <select name="bhw_id" class="form-select" required>
                        <option value="" disabled selected>Select BHW</option>
                        @foreach($promotableBhws as $bhw)
                            <option value="{{ $bhw->id }}" {{ (string) old('bhw_id') === (string) $bhw->id ? 'selected' : '' }}>
                                {{ $bhw->name }}{{ $bhw->barangay ? ' — ' . $bhw->barangay : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required-label">Barangay Lookup</label>
                    <select name="barangay" class="form-select" required>
                        <option value="" disabled selected>Select Barangay</option>
                        @forelse(($barangays ?? collect()) as $brgy)
                            <option value="{{ $brgy->name }}" {{ old('barangay') === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @empty
                            <option value="Burgos" {{ old('barangay') === 'Burgos' ? 'selected' : '' }}>Barangay Burgos</option>
                            <option value="Padlan" {{ old('barangay') === 'Padlan' ? 'selected' : '' }}>Barangay Padlan</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"
                        onclick="return confirm('Promote this BHW to President? This is blocked if the barangay already has an active President.')">
                        <i class="bi bi-arrow-up-circle me-1"></i> Promote
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
