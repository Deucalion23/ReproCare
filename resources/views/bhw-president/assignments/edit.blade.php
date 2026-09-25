@extends('bhw-president.layout')

@section('title', 'Edit Assignment - BHW President Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Edit Assignment</h1>
            <p class="page-subtitle">{{ $assignment->bhw->name }} - {{ $assignment->purok->name }}</p>
        </div>
        <a href="{{ route('bhw-president.assignments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('bhw-president.assignments.update', $assignment->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">BHW *</label>
                        <select name="bhw_id" class="form-select" required>
                            <option value="">Select BHW...</option>
                            @foreach($bhws as $bhw)
                            <option value="{{ $bhw->id }}" {{ old('bhw_id', $assignment->bhw_id) == $bhw->id ? 'selected' : '' }}>{{ $bhw->name }} ({{ $bhw->barangay }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Purok *</label>
                        <select name="purok_id" class="form-select" required>
                            <option value="">Select Purok...</option>
                            @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id', $assignment->purok_id) == $purok->id ? 'selected' : '' }}>{{ $purok->name }} ({{ $purok->barangay }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $assignment->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('is_active', $assignment->is_active) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $assignment->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Assignment
                </button>
                <a href="{{ route('bhw-president.assignments.show', $assignment->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
