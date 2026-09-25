@extends('midwife.layout')

@section('title', 'Edit Post - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Edit Post
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Update your forum post
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('forum.show', $post->id) }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Post
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     EDIT FORM
════════════════════════════════ --}}
<div class="card fade-in-card" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('forum.update', $post->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="content" class="form-label">Edit your post...</label>
                <textarea class="form-control" id="content" name="content" rows="6" required>{{ old('content', $post->content) }}</textarea>
                <small class="text-muted">Maximum 2000 characters</small>
            </div>

            <div class="mb-4">
                <label for="post_image" class="form-label">Add/Change Image (Optional)</label>
                <input type="file"
                       class="form-control @error('post_image') is-invalid @enderror"
                       id="post_image"
                       name="post_image"
                       accept="image/jpeg,image/jpg,image/png"
                       onchange="previewForumImage(this)">
                <div class="form-text">Accepted formats: JPG, PNG. Max size: 5MB</div>
                @error('post_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($post->post_image)
                <div class="mb-4">
                    <label class="form-label">Current Image</label>
                    <img src="{{ $post->post_image_url }}"
                         alt="Current post image"
                         class="img-fluid rounded"
                         style="max-height:200px; border-radius:12px;">
                </div>
            @endif

            <div class="mb-4" id="image-preview-container" style="display:none;">
                <label class="form-label">New Image Preview</label>
                <img id="post-image-preview"
                     src=""
                     alt="Preview"
                     class="img-fluid rounded"
                     style="max-height:300px; border-radius:12px;">
                <button type="button"
                        class="btn btn-sm btn-outline-danger mt-2"
                        onclick="removeForumImage()">
                    <i class="bi bi-trash"></i> Remove New Image
                </button>
            </div>

            <div class="d-flex gap-2 pt-3" style="border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save-fill me-1"></i> Update Post
                </button>
                <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
            </div>
        </form>

        <script>
        function previewForumImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('post-image-preview').src = e.target.result;
                    document.getElementById('image-preview-container').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeForumImage() {
            document.getElementById('post_image').value = '';
            document.getElementById('image-preview-container').style.display = 'none';
            document.getElementById('post-image-preview').src = '';
        }
        </script>
    </div>
</div>
@endsection
