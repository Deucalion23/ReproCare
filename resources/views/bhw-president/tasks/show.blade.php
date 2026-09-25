@extends('bhw-president.layout')

@section('title', 'Task Details - BHW President Portal | ReproCare')

@section('bhw-president-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Task Details</div>
                <p class="page-hero-subtitle mb-0">{{ $task->title }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if($task->status != 'completed')
                <a href="{{ route('bhw-president.tasks.edit', $task->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endif
                <a href="{{ route('bhw-president.tasks.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card fade-in-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Information</h5>
            </div>
            <div class="card-body">
                <h4>{{ $task->title }}</h4>
                <p class="text-muted">{{ $task->description }}</p>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Assigned To</p>
                        <h5>{{ $task->assignedTo->name }}</h5>
                        <p class="text-muted">{{ $task->assignedTo->barangay }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Task Type</p>
                        <h5>{{ ucfirst(str_replace('_', ' ', $task->task_type)) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Status</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-1">Status</p>
                <h5>
                    @switch($task->status)
                        @case('pending')
                            <span class="badge bg-primary">Pending</span>
                            @break
                        @case('in_progress')
                            <span class="badge bg-warning">In Progress</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success">Completed</span>
                            @break
                        @case('cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                            @break
                    @endswitch
                </h5>
                <hr>
                <p class="text-muted mb-1">Due Date</p>
                <h5>{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</h5>
                @if($task->due_date && $task->due_date < now() && $task->status != 'completed')
                    <span class="badge bg-danger">Overdue</span>
                @endif
                <hr>
                <p class="text-muted mb-1">Assigned By</p>
                <h5>{{ $task->assignedBy->name }}</h5>
                <hr>
                <p class="text-muted mb-1">Created At</p>
                <h5>{{ $task->created_at->format('M d, Y g:i A') }}</h5>
                @if($task->completed_at)
                    <hr>
                    <p class="text-muted mb-1">Completed At</p>
                    <h5>{{ $task->completed_at->format('M d, Y g:i A') }}</h5>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
