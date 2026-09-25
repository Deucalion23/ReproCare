@extends('midwife.layout')

@section('title', 'Create Post - ReproCare')

@section('midwife-content')

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
<div class="card fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border)!important; border-radius:20px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent); max-width:820px; margin:0 auto;">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="{{ route('forum.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="content" class="form-label fw-bold" style="color:var(--color-text); font-size:0.95rem;">Share your thoughts...</label>
                <textarea class="form-control" id="content" name="content" rows="6"
                          placeholder="What would you like to share with the community?" 
                          style="border-radius:14px; border:1px solid var(--color-border); padding:1rem; font-size:0.95rem;" required>{{ old('content') }}</textarea>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted">Maximum 2,000 characters</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold" style="color:var(--color-text); font-size:0.95rem;">Add Image (Optional)</label>
                <div id="drop-zone" class="p-4 text-center" 
                     style="border:2px dashed var(--color-primary-soft); border-radius:16px; background:var(--color-primary-soft); cursor:pointer; transition:all 0.2s ease;"
                     onclick="document.getElementById('post_image').click()"
                     ondragover="event.preventDefault(); this.style.borderColor='var(--color-primary)'; this.style.background='var(--color-primary-soft)';"
                     ondragleave="this.style.borderColor='var(--color-border)'; this.style.background='var(--color-bg)';"
                     ondrop="handleDrop(event)">
                    <i class="bi bi-cloud-arrow-up-fill" style="font-size:2.2rem; color:var(--color-primary-text);"></i>
                    <div class="mt-2 fw-semibold" style="color:var(--color-text); font-size:0.95rem;">
                        Click to upload or drag &amp; drop
                    </div>
                    <div class="text-muted" style="font-size:0.8rem;">JPG, JPEG or PNG (Max 5MB)</div>
                    <input type="file"
                           class="d-none @error('post_image') is-invalid @enderror"
                           id="post_image"
                           name="post_image"
                           accept="image/jpeg,image/jpg,image/png"
                           onchange="previewForumImage(this)">
                </div>
                @error('post_image')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4" id="image-preview-container" style="display:none;">
                <label class="form-label fw-bold" style="color:var(--color-text); font-size:0.88rem;">Image Preview</label>
                <div class="position-relative d-inline-block">
                    <img id="post-image-preview"
                         src=""
                         alt="Preview"
                         class="img-fluid rounded shadow-sm"
                         style="max-height:280px; border-radius:14px; border:1px solid var(--color-border);">
                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"
                            style="width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;"
                            onclick="removeForumImage()"
                            title="Remove Image">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid var(--color-border);">
                <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius:12px; font-weight:600; border-color:var(--color-border);">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius:12px; font-weight:700;">
                    <i class="bi bi-send-fill me-1"></i> Publish Post
                </button>
            </div>
        </form>

        <script>
        function previewForumImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('post-image-preview').src = e.target.result;
                    document.getElementById('image-preview-container').style.display = 'block';
                    document.getElementById('drop-zone').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleDrop(event) {
            event.preventDefault();
            const dropZone = document.getElementById('drop-zone');
            dropZone.style.borderColor = 'var(--color-border)';
            dropZone.style.background = 'var(--color-bg)';
            if (event.dataTransfer.files && event.dataTransfer.files[0]) {
                const fileInput = document.getElementById('post_image');
                fileInput.files = event.dataTransfer.files;
                previewForumImage(fileInput);
            }
        }

        function removeForumImage() {
            document.getElementById('post_image').value = '';
            document.getElementById('image-preview-container').style.display = 'none';
            document.getElementById('post-image-preview').src = '';
            document.getElementById('drop-zone').style.display = 'block';
        }
        </script>
    </div>
</div>
@endsection
