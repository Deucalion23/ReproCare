@extends('cho.layout')

@section('title', 'Register Staff - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Register New Staff
            </div>
            <p class="page-hero-subtitle">
                Create accounts for RHU, Midwife, and Barangay Health Worker personnel.
            </p>
        </div>
        <a href="{{ route('cho.users.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to User Management
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
        <form method="POST" action="{{ route('cho.users.store') }}">
            @csrf

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Account Credentials & Role
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Account Role</label>
                    <select name="role" id="roleSelect" class="form-select" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="rhu" {{ old('role') === 'rhu' ? 'selected' : '' }}>RHU Admin</option>
                        <option value="midwife" {{ old('role') === 'midwife' ? 'selected' : '' }}>Midwife</option>
                        <option value="bhw_president" {{ old('role') === 'bhw_president' ? 'selected' : '' }}>BHW President</option>
                        <option value="bhw" {{ old('role') === 'bhw' ? 'selected' : '' }}>BHW</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="username@reprocare.gov.ph" required>
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

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Personal Profile
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label required-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="e.g. Maria" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial') }}" placeholder="e.g. S" maxlength="1">
                </div>

                <div class="col-md-5">
                    <label class="form-label required-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" placeholder="e.g. Santos" required>
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

            <h5 class="fw-700 mb-4" id="assignmentTitle" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Office & Station Assignment
            </h5>

            <div class="row g-3 mb-5" id="assignmentFields">
                <div class="col-md-6" id="choOfficeContainer">
                    <label class="form-label">CHO Office Unit (For CHO staff)</label>
                    <input type="text" name="cho_office" class="form-control" value="{{ old('cho_office') }}" placeholder="e.g. Maternal Health Division">
                </div>

                <div class="col-md-6" id="rhuAssignmentContainer">
                    <label class="form-label">Assigned RHU Station</label>
                    <select name="rhu_assignment" class="form-select">
                        <option value="" selected>Not assigned</option>
                        <option value="RHU 1" {{ old('rhu_assignment') === 'RHU 1' ? 'selected' : '' }}>RHU 1 (16 barangays)</option>
                        <option value="RHU 2" {{ old('rhu_assignment') === 'RHU 2' ? 'selected' : '' }}>RHU 2 (15 barangays)</option>
                        <option value="RHU 3" {{ old('rhu_assignment') === 'RHU 3' ? 'selected' : '' }}>RHU 3 (20 barangays)</option>
                        <option value="RHU 4" {{ old('rhu_assignment') === 'RHU 4' ? 'selected' : '' }}>RHU 4 (16 barangays)</option>
                        <option value="RHU 5" {{ old('rhu_assignment') === 'RHU 5' ? 'selected' : '' }}>RHU 5 (19 barangays)</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Register Account
                </button>
                <a href="{{ route('cho.users.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const choContainer = document.getElementById('choOfficeContainer');
    const rhuContainer = document.getElementById('rhuAssignmentContainer');
    const assignmentTitle = document.getElementById('assignmentTitle');

    function toggleFields() {
        const role = roleSelect.value;
        if (role === 'rhu') {
            assignmentTitle.style.display = 'block';
            choContainer.style.display = 'none';
            rhuContainer.style.display = 'block';
        } else if (role === 'cho') {
            assignmentTitle.style.display = 'block';
            choContainer.style.display = 'block';
            rhuContainer.style.display = 'none';
        } else {
            // Midwife or BHW typically belong to a specific Barangay/RHU which is done upon their local login or can be set, but we keep it optional
            assignmentTitle.style.display = 'block';
            choContainer.style.display = 'none';
            rhuContainer.style.display = 'block';
        }
    }

    roleSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial run
});
</script>
@endpush

@endsection
