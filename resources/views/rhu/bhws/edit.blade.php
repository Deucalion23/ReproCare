@extends('rhu.layout')

@section('title', 'Edit BHW - RHU Portal | ReproCare')

@section('rhu-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit BHW — {{ $bhw->name }}</h1>
        <a href="{{ route('rhu.bhws.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card fade-in-card">
        <div class="card-body">
            <form method="POST" action="{{ route('rhu.bhws.update', $bhw->id) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $bhw->first_name) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">M.I.</label>
                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial', $bhw->middle_initial) }}" maxlength="2">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $bhw->last_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $bhw->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Number *</label>
                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $bhw->contact_number) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($bhw->date_of_birth)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="female" {{ old('gender', $bhw->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="male" {{ old('gender', $bhw->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        @foreach(['approved', 'inactive', 'archived', 'suspended'] as $s)
                            <option value="{{ $s }}" {{ old('status', $bhw->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barangay</label>
                    <select name="barangay" class="form-select">
                        <option value="">Select Barangay</option>
                        @foreach($barangays as $brgy)
                            <option value="{{ $brgy->name }}" {{ old('barangay', $bhw->barangay) === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sitio / Street / Purok</label>
                    <input type="text" name="purok" class="form-control" value="{{ old('purok', $bhw->purok?->name ?? '') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $bhw->address) }}" placeholder="e.g. 123 Sampaguita St">
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password (leave blank to keep)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                @if($errors->any())
                    <div class="col-12"><div class="alert alert-danger">{{ $errors->first() }}</div></div>
                @endif
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Update BHW
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
