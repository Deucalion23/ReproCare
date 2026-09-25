@extends('bhw-president.layout')

@section('title', 'Tasks - BHW President Portal | ReproCare')

@section('bhw-president-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Tasks</div>
                <p class="page-hero-subtitle mb-0">Assign work to BHWs and track progress to completion.</p>
            </div>
            <a href="{{ route('bhw-president.tasks.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Task
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-cyan fade-in-card">
                <i class="bi bi-hourglass-split stat-icon"></i>
                <div class="stat-label">Pending</div>
                <div class="stat-number">{{ $tasks->where('status', 'pending')->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-amber fade-in-card">
                <i class="bi bi-arrow-repeat stat-icon"></i>
                <div class="stat-label">In Progress</div>
                <div class="stat-number">{{ $tasks->where('status', 'in_progress')->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-green fade-in-card">
                <i class="bi bi-check-circle-fill stat-icon"></i>
                <div class="stat-label">Completed</div>
                <div class="stat-number">{{ $tasks->where('status', 'completed')->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-rose fade-in-card">
                <i class="bi bi-exclamation-triangle-fill stat-icon"></i>
                <div class="stat-label">Overdue</div>
                <div class="stat-number">{{ $tasks->where('due_date', '<', now())->where('status', '!=', 'completed')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-body p-0">
        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Type</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->assignedTo->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $task->task_type)) }}</td>
                            <td>{{ $task->due_date ? $task->due_date->format('M d, Y') : '-' }}</td>
                            <td>
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
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1 tbl-actions">
                                    <a href="{{ route('bhw-president.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary" title="View task" aria-label="View task">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('bhw-president.tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit task" aria-label="Edit task">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($task->status != 'completed')
                                    <form action="{{ route('bhw-president.tasks.complete', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Complete" aria-label="Mark complete">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('bhw-president.tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')" title="Delete task" aria-label="Delete task">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-clipboard-task fs-1 text-muted"></i>
                <p class="text-muted mt-3">No tasks found</p>
                <a href="{{ route('bhw-president.tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create First Task
                </a>
            </div>
        @endif
        </div>
    </div>
</div>
@endsection
