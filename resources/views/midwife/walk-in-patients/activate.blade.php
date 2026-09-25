@extends('midwife.layout')

@section('title', 'Activate Portal Account - Midwife Portal | ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Activate Portal Account
            </h1>
            <p class="page-subtitle">Upgrade an Unlinked Profile (BHW-Managed · Field Record Only) into an Enrolled Account (Portal-Active · Direct Access Patient)</p>
        </div>
        <a href="{{ route('midwife.walk-in-patients.show', $walkInPatient->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Field Record
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow fade-in-card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Account Credentials</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This will create a portal login for <strong>{{ $walkInPatient->full_name }}</strong>.
                        The account is <strong>approved immediately</strong> (verified in person — no approval queue),
                        and the field record links via <code>user_id</code> with portal access enabled.
                    </div>

                    <form method="POST" action="{{ route('midwife.walk-in-patients.store-activate', $walkInPatient->id) }}" class="rc-adaptive-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="{{ $walkInPatient->first_name }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Middle Initial</label>
                                <input type="text" class="form-control" value="{{ $walkInPatient->middle_initial ?? '' }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="{{ $walkInPatient->last_name }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required placeholder="patient@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number *</label>
                                <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" required value="{{ $walkInPatient->contact_number ?? '' }}">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" placeholder="Min 8 characters">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required minlength="8" placeholder="Re-enter password">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address *</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" required value="{{ $walkInPatient->address ?? '' }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barangay *</label>
                                <input type="text" name="barangay" class="form-control @error('barangay') is-invalid @enderror" required value="{{ $walkInPatient->barangay ?? '' }}">
                                @error('barangay')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ $walkInPatient->date_of_birth?->format('Y-m-d') ?? '' }}" readonly>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 rc-form-actions">
                            <a href="{{ route('midwife.walk-in-patients.show', $walkInPatient->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-check me-1"></i> Activate Enrolled Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow fade-in-card">
                <div class="card-header">
                    <h5 class="mb-0">Field Record Info</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $walkInPatient->full_name }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-warning">Unlinked Profile · BHW-Managed</span></p>
                    <p><strong>Contact:</strong> {{ $walkInPatient->contact_number ?? '—' }}</p>
                    <p><strong>Address:</strong> {{ $walkInPatient->address ?? '—' }}</p>
                    <p><strong>Barangay:</strong> {{ $walkInPatient->barangay ?? '—' }}</p>
                    <p><strong>Purok:</strong> {{ $walkInPatient->purok?->name ?? '—' }}</p>
                    <p><strong>Reason for Visit:</strong> {{ $walkInPatient->reason_for_visit ?? '—' }}</p>
                    <hr>
                    <p><strong>Recorded By:</strong> {{ $walkInPatient->recordedBy?->name ?? '—' }}</p>
                    <p><strong>Recorded On:</strong> {{ $walkInPatient->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
