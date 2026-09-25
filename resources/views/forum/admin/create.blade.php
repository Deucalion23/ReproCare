@extends('midwife.layout')

@section('title', 'Create Forum Post - ReproCare')

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
                <div class="page-hero-title">Create Forum Post</div>
                <p class="page-hero-subtitle">Publish a guided update, announcement, or discussion starter for the community.</p>
            </div>
            <a href="{{ route('midwife.forum.admin.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i>Back to Admin</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="workspace-panel fade-in-card">
                <div class="workspace-panel-header">
                    <h2 class="workspace-panel-title">Post Content</h2>
                </div>
                <div class="workspace-panel-body">
                    <form method="POST" action="{{ route('midwife.forum.admin.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="content" class="form-label">Message</label>
                            <textarea class="form-control" id="content" name="content" rows="8" required placeholder="Share your update, question, or guidance with the community...">{{ old('content') }}</textarea>
                            <div class="form-text">Keep it clear, respectful, and useful for patients and staff.</div>
                            @error('content')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Posted By</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Active" readonly>
                            </div>
                        </div>

                        <div class="workspace-panel" style="background:var(--bg-card2);">
                            <div class="workspace-panel-body">
                                <label for="post_image" class="form-label">Image Attachment</label>
                                <input type="file" class="form-control @error('post_image') is-invalid @enderror" id="post_image" name="post_image" accept="image/jpeg,image/jpg,image/png" onchange="previewAdminForumImage(this)">
                                <div class="form-text">Optional. JPG and PNG files up to 5MB.</div>
                                @error('post_image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4" id="admin-image-preview-container" style="display:none;">
                            <label class="form-label">Preview</label>
                            <img id="admin-post-image-preview" src="" alt="Preview" class="forum-admin-preview">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-3" onclick="removeAdminForumImage()">
                                <i class="bi bi-trash me-1"></i>Remove Image
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Create Post</button>
                            <a href="{{ route('midwife.forum.admin.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="insight-card fade-in-card">
                <h6>Posting Checklist</h6>
                <div class="insight-list">
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Use simple language that patients and staff can understand quickly.</span></div>
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Avoid patient-identifying information and confidential case details.</span></div>
                    <div class="insight-list-item"><i class="bi bi-check2-circle"></i><span>Use images only when they help clarify the topic or announcement.</span></div>
                </div>
            </div>
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
