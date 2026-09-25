@extends('bhw.layout')

@section('title', 'Create Post - ReproCare')

@section('bhw-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Create New Post
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Share your thoughts with the community
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('forum.index') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Forum
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:10px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
    </div>
@endif

{{-- ═══════════════════════════════
     CREATE FORM
════════════════════════════════ --}}
<div class="card fade-in-card" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('forum.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="content" class="form-label">Share your thoughts...</label>
                <textarea class="form-control" id="content" name="content" rows="6"
                          placeholder="What would you like to share with the community?" required>{{ old('content') }}</textarea>
                <small class="text-muted">Maximum 2000 characters</small>
            </div>

            <div class="mb-4">
                <label for="post_image" class="form-label">Add Image (Optional)</label>
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

            <div class="mb-4" id="image-preview-container" style="display:none;">
                <label class="form-label">Image Preview</label>
                <img id="post-image-preview"
                     src=""
                     alt="Preview"
                     class="img-fluid rounded"
                     style="max-height:300px; border-radius:12px;">
                <button type="button"
                        class="btn btn-sm btn-outline-danger mt-2"
                        onclick="removeForumImage()">
                    <i class="bi bi-trash"></i> Remove Image
                </button>
            </div>

            <div class="d-flex gap-2 pt-3" style="border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-fill me-1"></i> Post
                </button>
                <a href="{{ route('bhw.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
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
