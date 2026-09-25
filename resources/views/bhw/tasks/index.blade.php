@extends('bhw.layout')

@section('title', 'My Tasks - ReproCare')

@section('bhw-content')
<div class="page-hero fade-in-card mb-4">
    <div style="position:relative;z-index:1;">
        <div class="page-hero-title">My Tasks
        </div>
        <p class="page-hero-subtitle">
            <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
            &nbsp;·&nbsp; Tasks assigned by your BHW President
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card" style="border:none; background:var(--bg-card);">
    <div class="card-body p-4">
        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $task->title }}</div>
                                    @if($task->description)
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($task->description, 80) }}</small>
                                    @endif
                                    <div><small class="text-muted">From: {{ $task->assignedBy?->name ?? '—' }}</small></div>
                                </td>
                                <td>{{ str_replace('_', ' ', $task->task_type) }}</td>
                                <td>{{ optional($task->due_date)->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : ($task->status === 'cancelled' ? 'secondary' : 'warning text-dark')) }}">
                                        {{ str_replace('_', ' ', $task->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @if($task->status === 'pending')
                                            <form method="POST" action="{{ route('bhw.tasks.start', $task->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Start task">
                                                    <i class="bi bi-play"></i> Start
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($task->status, ['pending', 'in_progress']))
                                            <form method="POST" action="{{ route('bhw.tasks.complete', $task->id) }}" onsubmit="return confirm('Mark this task as completed?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Complete task">
                                                    <i class="bi bi-check-lg"></i> Done
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $tasks->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-list-task empty-state-icon"></i>
                <h6>No Tasks Assigned</h6>
                <p>Your BHW President hasn't assigned you any tasks yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
