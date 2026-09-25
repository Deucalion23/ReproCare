@extends('bhw-president.layout')

@section('title', 'Assignment Details - BHW President Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Assignment Details</h1>
            <p class="page-subtitle">{{ $assignment->bhw->name }} - {{ $assignment->purok->name }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('bhw-president.assignments.edit', $assignment->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('bhw-president.assignments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Assignment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">BHW</p>
                        <h4>{{ $assignment->bhw->name }}</h4>
                        <p class="text-muted">{{ $assignment->bhw->barangay }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Purok</p>
                        <h4>{{ $assignment->purok->name }}</h4>
                        <p class="text-muted">{{ $assignment->purok->barangay }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($assignment->notes)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p>{{ $assignment->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Assignment Status</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-1">Status</p>
                <h5>
                    @if($assignment->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </h5>
                <hr>
                <p class="text-muted mb-1">Assigned By</p>
                <h5>{{ $assignment->assignedBy->name }}</h5>
                <hr>
                <p class="text-muted mb-1">Assigned At</p>
                <h5>{{ $assignment->assigned_at->format('M d, Y g:i A') }}</h5>
                <hr>
                <p class="text-muted mb-1">Created At</p>
                <h5>{{ $assignment->created_at->format('M d, Y g:i A') }}</h5>
            </div>
        </div>
    </div>
</div>
@endsection
