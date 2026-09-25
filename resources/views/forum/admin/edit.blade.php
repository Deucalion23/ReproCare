@extends('midwife.layout')

@section('title', 'Edit Forum Post - ReproCare')

@push('styles')
<style>
    .forum-admin-preview {
        width:100%;
        max-height:320px;
        object-fit:cover;
        border-radius:18px;
        border:1px solid var(--border);
    }
</style>
@endpush

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Edit Forum Post</div>
                <p class="page-hero-subtitle">Update the message, attachment, or details before the community sees the latest version.</p>
            </div>
            <div class="workspace-toolbar-actions">
                <a href="{{ route('midwife.forum.admin.show', $post->id) }}" class="btn-hero-secondary"><i class="bi bi-eye"></i>View Post</a>
                <a href="{{ route('midwife.forum.admin.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i>Back to Admin</a>
            </div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Edit Details</h2>
        </div>
        <div class="workspace-panel-body">
            <form method="POST" action="{{ route('midwife.forum.admin.update', $post->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="8" required>{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="workspace-filter-grid mb-4">
                    <div class="span-4">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" value="{{ $post->user->name }}" readonly>
                    </div>
                    <div class="span-4">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="{{ ucfirst($post->status) }}" readonly>
                    </div>
                    <div class="span-4">
                        <label class="form-label">Created</label>
                        <input type="text" class="form-control" value="{{ $post->created_at->format('M j, Y g:i A') }}" readonly>
                    </div>
                </div>

                <div class="workspace-panel" style="background:var(--bg-card2);">
                    <div class="workspace-panel-body">
                        <label for="post_image" class="form-label">Replace Image</label>
                        <input type="file" class="form-control @error('post_image') is-invalid @enderror" id="post_image" name="post_image" accept="image/jpeg,image/jpg,image/png" onchange="previewAdminForumImage(this)">
                        <div class="form-text">Optional. Upload a new image if you want to replace the current one.</div>
                        @error('post_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($post->post_image_url)
                    <div class="mt-4">
                        <label class="form-label">Current Image</label>
                        <img src="{{ $post->post_image_url }}" alt="Current post image" class="forum-admin-preview">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="remove_post_image" name="remove_post_image" value="1">
                            <label class="form-check-label" for="remove_post_image">Remove current image</label>
                        </div>
                    </div>
                @endif

                <div class="mt-4" id="admin-image-preview-container" style="display:none;">
                    <label class="form-label">New Image Preview</label>
                    <img id="admin-post-image-preview" src="" alt="Preview" class="forum-admin-preview">
                    <button type="button" class="btn btn-sm btn-outline-danger mt-3" onclick="removeAdminForumImage()">
                        <i class="bi bi-trash me-1"></i>Remove New Image
                    </button>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Post</button>
                    <a href="{{ route('midwife.forum.admin.show', $post->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAdminForumImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('admin-post-image-preview').src = event.target.result;
            document.getElementById('admin-image-preview-container').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeAdminForumImage() {
    document.getElementById('post_image').value = '';
    document.getElementById('admin-post-image-preview').src = '';
    document.getElementById('admin-image-preview-container').style.display = 'none';
}
</script>
@endpush
@endsection
