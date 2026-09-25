@extends('midwife.layout')

@section('title', 'Forum Admin - ReproCare')

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Forum Administration</div>
                <p class="page-hero-subtitle">Moderate forum activity, review engagement, and manage community-facing posts.</p>
            </div>
            <div class="workspace-toolbar-actions">
                <a href="{{ route('forum.index') }}" class="btn-hero-secondary"><i class="bi bi-chat-dots"></i>View Forum</a>
                <a href="{{ route('midwife.forum.admin.create') }}" class="btn-hero-primary"><i class="bi bi-plus-circle-fill"></i>Create Post</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="metric-grid">
        <div class="metric-card metric-card-primary fade-in-card">
            <i class="bi bi-chat-square-text-fill metric-card-icon"></i>
            <div class="metric-card-label">Posts</div>
            <div class="metric-card-value">{{ $posts->total() }}</div>
            <div class="metric-card-note">Forum entries in the current moderation list.</div>
        </div>
        <div class="metric-card metric-card-green fade-in-card">
            <i class="bi bi-hand-thumbs-up-fill metric-card-icon"></i>
            <div class="metric-card-label">Likes</div>
            <div class="metric-card-value">{{ $posts->getCollection()->sum(fn($post) => $post->likes->count()) }}</div>
            <div class="metric-card-note">Combined engagement across visible posts.</div>
        </div>
        <div class="metric-card metric-card-cyan fade-in-card">
            <i class="bi bi-chat-left-text-fill metric-card-icon"></i>
            <div class="metric-card-label">Comments</div>
            <div class="metric-card-value">{{ $posts->getCollection()->sum(fn($post) => $post->comments->count()) }}</div>
            <div class="metric-card-note">Replies visible in this moderation batch.</div>
        </div>
        <div class="metric-card metric-card-rose fade-in-card">
            <i class="bi bi-trash3-fill metric-card-icon"></i>
            <div class="metric-card-label">Bulk Actions</div>
            <div class="metric-card-value" id="selectedCount">0</div>
            <div class="metric-card-note">Selected posts ready for cleanup.</div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <div class="workspace-toolbar">
                <div>
                    <h2 class="workspace-panel-title">Forum Posts</h2>
                    <p class="workspace-panel-subtitle">Review post content, author activity, and moderation status.</p>
                </div>
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display:none;" onclick="bulkDelete()">
                    <i class="bi bi-trash me-1"></i>Delete Selected
                </button>
            </div>
        </div>
        <div class="workspace-panel-body pt-3">
            @if($posts->count() > 0)
                <form id="bulkDeleteForm" method="POST" action="{{ route('midwife.forum.admin.bulk-delete') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modern-table-wrap">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th style="width:56px;">
                                        <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>ID</th>
                                    <th>Author</th>
                                    <th>Content</th>
                                    <th>Likes</th>
                                    <th>Comments</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $post)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input post-checkbox" name="posts[]" value="{{ $post->id }}" onchange="updateBulkDeleteBtn()">
                                        </td>
                                        <td>#{{ $post->id }}</td>
                                        <td>
                                            <div class="table-title">{{ optional($post->user)->name ?? 'Unknown' }}</div>
                                            <div class="table-subtitle">{{ ucfirst(str_replace('_', ' ', $post->user_type ?? 'member')) }}</div>
                                        </td>
                                        <td>
                                            <div class="table-title">{{ \Illuminate\Support\Str::limit($post->content, 72) }}</div>
                                            <div class="table-subtitle">{{ $post->post_image ? 'Includes image attachment' : 'Text-only post' }}</div>
                                        </td>
                                        <td>{{ $post->likes->count() }}</td>
                                        <td>{{ $post->comments->count() }}</td>
                                        <td>
                                            <span class="summary-chip {{ $post->status === 'active' ? 'chip-success' : 'chip-danger' }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $post->created_at->format('M j, Y') }}</td>
                                        <td class="text-end">
                                            <div class="table-actions d-inline-flex align-items-center gap-1 tbl-actions">
                                                <a href="{{ route('midwife.forum.admin.show', $post->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                                @if($post->user_id === auth()->id() && $post->user_type === 'midwife')
                                                    <a href="{{ route('midwife.forum.admin.edit', $post->id) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                                @endif
                                                <form method="POST" action="{{ route('midwife.forum.admin.destroy', $post->id) }}" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @if($post->status !== 'active')
                                                    <form method="POST" action="{{ route('midwife.forum.admin.restore', $post->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="d-flex justify-content-center pt-4">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="empty-state-panel">
                    <i class="bi bi-shield-exclamation"></i>
                    <h3>No forum posts yet</h3>
                    <p>When community activity starts, moderation tools and summaries will appear here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.post-checkbox').forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkDeleteBtn();
}

function updateBulkDeleteBtn() {
    const checked = document.querySelectorAll('.post-checkbox:checked').length;
    const btn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    selectedCount.textContent = checked;

    if (checked > 0) {
        btn.style.display = 'inline-flex';
        btn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Selected (' + checked + ')';
    } else {
        btn.style.display = 'none';
    }
}

function bulkDelete() {
    const checked = document.querySelectorAll('.post-checkbox:checked').length;
    if (!checked) return;
    if (confirm('Are you sure you want to delete ' + checked + ' post(s)?')) {
        document.getElementById('bulkDeleteForm').submit();
    }
}
</script>
@endpush
@endsection
