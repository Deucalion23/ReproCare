@extends('rhu.layout')

@section('title', 'Edit Midwife Profile - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Edit Midwife Profile
            </div>
            <p class="page-hero-subtitle">
                Update account details and contact information for {{ $midwife->name }}.
            </p>
        </div>
        <a href="{{ route('rhu.midwives.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Midwives
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
        <form method="POST" action="{{ route('rhu.midwives.update', $midwife->id) }}">
            @csrf
            @method('PUT')

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Credentials
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $midwife->email) }}" required>
                </div>
            </div>

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Profile Details
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label required-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $midwife->first_name) }}" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial', $midwife->middle_initial) }}" maxlength="1">
                </div>

                <div class="col-md-5">
                    <label class="form-label required-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $midwife->last_name) }}" required>
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <label class="form-label required-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $midwife->date_of_birth ? $midwife->date_of_birth->format('Y-m-d') : '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="female" {{ old('gender', $midwife->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="male" {{ old('gender', $midwife->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" value="{{ old('contact_number', $midwife->contact_number) }}" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Update Profile
                </button>
                <a href="{{ route('rhu.midwives.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
