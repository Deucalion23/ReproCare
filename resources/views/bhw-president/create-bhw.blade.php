@extends('bhw-president.layout')

@section('title', 'Add BHW')

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Add New BHW</div>
            <p class="page-hero-subtitle">Create a new Barangay Health Worker account</p>
        </div>
        <a href="{{ route('bhw-president.bhws.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i> Back to BHWs
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-header">
        <h5 class="mb-0">BHW Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('bhw-president.bhws.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Personal Information -->
            <h6 class="mb-3">Personal Information</h6>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" 
                           class="form-control @error('first_name') is-invalid @enderror" 
                           id="first_name" 
                           name="first_name" 
                           value="{{ old('first_name') }}" 
                           required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2 mb-3">
                    <label for="middle_initial" class="form-label">MI</label>
                    <input type="text" 
                           class="form-control @error('middle_initial') is-invalid @enderror" 
                           id="middle_initial" 
                           name="middle_initial" 
                           value="{{ old('middle_initial') }}"
                           maxlength="2">
                    @error('middle_initial')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" 
                           class="form-control @error('last_name') is-invalid @enderror" 
                           id="last_name" 
                           name="last_name" 
                           value="{{ old('last_name') }}" 
                           required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Contact Number *</label>
                    <input type="tel" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           placeholder="09XX-XXX-XXXX" 
                           required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select class="form-select @error('gender') is-invalid @enderror" 
                            id="gender" 
                            name="gender" 
                            required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                    <input type="date" 
                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                           id="date_of_birth" 
                           name="date_of_birth" 
                           value="{{ old('date_of_birth') }}"
                           max="{{ date('Y-m-d') }}" 
                           required>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" 
                           class="form-control @error('profile_image') is-invalid @enderror" 
                           id="profile_image" 
                           name="profile_image" 
                           accept="image/*">
                    @error('profile_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Address Information -->
            <h6 class="mb-3">Address Information</h6>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="barangay" class="form-label">Barangay</label>
                    <input type="text"
                           class="form-control"
                           id="barangay"
                           value="{{ auth()->user()->barangay ? 'Barangay ' . auth()->user()->barangay : 'Not assigned' }}"
                           readonly>
                    <small class="form-text text-muted">New BHWs are assigned to your barangay automatically.</small>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="purok" class="form-label">Sitio / Street / Purok</label>
                    <input type="text"
                           class="form-control @error('purok') is-invalid @enderror"
                           id="purok"
                           name="purok"
                           value="{{ old('purok') }}"
                           placeholder="e.g. Sitio Malinis, Purok 3">
                    @error('purok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="address" class="form-label">House No. / Street Address</label>
                    <input type="text"
                           class="form-control @error('address') is-invalid @enderror"
                           id="address"
                           name="address"
                           value="{{ old('address') }}"
                           placeholder="e.g. 123 Sampaguita St">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Certification Information -->
            <h6 class="mb-3">Certification Information</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="certification_number" class="form-label">Certification Number *</label>
                    <input type="text" 
                           class="form-control @error('certification_number') is-invalid @enderror" 
                           id="certification_number" 
                           name="certification_number" 
                           value="{{ old('certification_number') }}" 
                           required
                           placeholder="BHW Certification ID">
                    @error('certification_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="certification_date" class="form-label">Certification Date *</label>
                    <input type="date" 
                           class="form-control @error('certification_date') is-invalid @enderror" 
                           id="certification_date" 
                           name="certification_date" 
                           value="{{ old('certification_date') }}"
                           max="{{ date('Y-m-d') }}" 
                           required>
                    @error('certification_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Account Credentials -->
            <h6 class="mb-3">Account Credentials</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required
                           autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                    <input type="password" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           autocomplete="new-password">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('bhw-president.bhws.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <div>
                    <button type="reset" class="btn btn-outline-warning me-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Create BHW
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
