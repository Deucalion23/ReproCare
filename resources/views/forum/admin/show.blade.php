@extends('midwife.layout')

@section('title', 'Forum Post - ReproCare')

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Forum Post Administration</div>
                <p class="page-hero-subtitle">Review the post content, engagement, and comment activity from the moderation view.</p>
            </div>
            <div class="workspace-toolbar-actions">
                @if($post->user_id === auth()->id() && $post->user_type === 'midwife')
                    <a href="{{ route('midwife.forum.admin.edit', $post->id) }}" class="btn-hero-secondary"><i class="bi bi-pencil"></i>Edit</a>
                @endif
                <a href="{{ route('midwife.forum.admin.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i>Back to Admin</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="info-strip">
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-person-circle"></i>Author</div>
            <div class="info-pill-value">{{ optional($post->user)->name ?? 'Unknown' }}</div>
        </div>
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-heart-fill"></i>Likes</div>
            <div class="info-pill-value">{{ $post->likes->count() }}</div>
        </div>
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-chat-left-text-fill"></i>Comments</div>
            <div class="info-pill-value">{{ $post->comments->count() }}</div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <div class="workspace-toolbar">
                <div>
                    <h2 class="workspace-panel-title">Post Details</h2>
                    <p class="workspace-panel-subtitle">Published {{ $post->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="workspace-toolbar-actions">
                    <span class="summary-chip {{ $post->status === 'active' ? 'chip-success' : 'chip-danger' }}">{{ ucfirst($post->status) }}</span>
                    <form method="POST" action="{{ route('midwife.forum.admin.destroy', $post->id) }}" onsubmit="return confirm('Are you sure you want to delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="workspace-panel-body">
            <div class="workspace-panel" style="background:var(--bg-card2);">
                <div class="workspace-panel-body">
                    <div class="table-meta-stack">
                        <div><strong>Author:</strong> {{ optional($post->user)->name ?? 'Unknown' }}</div>
                        <div><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $post->user_type ?? 'member')) }}</div>
                        <div><strong>Status:</strong> {{ ucfirst($post->status) }}</div>
                    </div>
                    <div class="mt-4" style="white-space:pre-wrap;line-height:1.8;">{{ $post->content }}</div>
                </div>
            </div>

            @if($post->post_image_url)
                <div class="mt-4">
                    <label class="form-label">Attached Image</label>
                    <div class="rounded-4 overflow-hidden border" style="border-color:var(--border)!important;">
                        <img src="{{ $post->post_image_url }}" alt="Forum post image" class="img-fluid w-100" style="max-height:420px;object-fit:cover;">
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Comments</h2>
            <p class="workspace-panel-subtitle">{{ $post->comments->count() }} replies on this thread.</p>
        </div>
        <div class="workspace-panel-body">
            @forelse($post->comments as $comment)
                <div class="p-3 rounded-4 mb-3" style="background:var(--bg-card2);border:1px solid var(--border);">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="table-title">{{ $comment->user->name }}</div>
                        <div class="table-subtitle">{{ $comment->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                    <div class="mt-2 text-muted">{{ $comment->content }}</div>
                </div>
            @empty
                <div class="empty-state-panel">
                    <i class="bi bi-chat-square"></i>
                    <h5>No comments yet</h5>
                    <p>This post does not have any replies at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
