@extends('bhw-president.layout')

@section('title', 'Create Task - BHW President Portal | ReproCare')

@section('bhw-president-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Create Task</div>
                <p class="page-hero-subtitle mb-0">Assign work to a BHW — they are notified on creation.</p>
            </div>
            <a href="{{ route('bhw-president.tasks.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card fade-in-card">
    <div class="card-body p-4">
        <form action="{{ route('bhw-president.tasks.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Assign To BHW *</label>
                        <select name="assigned_to_id" class="form-select" required>
                            <option value="">Select BHW...</option>
                            @foreach($bhws as $bhw)
                            <option value="{{ $bhw->id }}">{{ $bhw->name }} ({{ $bhw->barangay }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Task Type *</label>
                        <select name="task_type" class="form-select" required>
                            <option value="">Select Type...</option>
                            <option value="patient_visit">Patient Visit</option>
                            <option value="data_collection">Data Collection</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="report_submission">Report Submission</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Task
                </button>
                <a href="{{ route('bhw-president.tasks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    </div>
</div>
@endsection
