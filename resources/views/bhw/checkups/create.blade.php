@extends('bhw.layout')

@section('title', 'Schedule Checkup - ReproCare')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Schedule Checkup</h1>
        <a href="{{ route('bhw.patients') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
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
            <h5 class="mb-0">Schedule Checkup for {{ $woman->name }}</h5>
        </div>
        <div class="card-body">
            {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
            <form method="POST" action="{{ route('bhw.checkups.store') }}" class="rc-adaptive-form">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient Name</label>
                        <input type="text" class="form-control" value="{{ $woman->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Patient Email</label>
                        <input type="text" class="form-control" value="{{ $woman->email }}" readonly>
                    </div>
                </div>
                
                <input type="hidden" name="user_id" value="{{ $woman->id }}">

                <div class="mb-3">
                    <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="scheduled_date" name="scheduled_date"
                           value="{{ old('scheduled_date') }}" required min="{{ today()->format('Y-m-d') }}">
                    @error('scheduled_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label">Purpose</label>
                    <input type="text" class="form-control" id="purpose" name="purpose"
                           value="{{ old('purpose') }}" placeholder="e.g., Regular prenatal check-up, Blood pressure monitoring">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                              placeholder="Preparation instructions or special considerations...">{{ old('notes') }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Scheduled By (BHW)</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="Scheduled" readonly>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calendar-check"></i> Schedule Checkup
                    </button>
                    <a href="{{ route('bhw.patient-details', $woman->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                    <a href="{{ route('bhw.patients') }}" class="btn btn-outline-info">
                        <i class="bi bi-list"></i> Back to Patients
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">Checkup Guidelines</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Schedule checkups at least 1 day in advance</li>
                <li>Include preparation instructions in notes</li>
                <li>Consider patient availability and preferences</li>
                <li>Follow up with health records after checkup completion</li>
                <li>Monitor patient progress between checkups</li>
            </ul>
        </div>
    </div>
</div>
@endsection
