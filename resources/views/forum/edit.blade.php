@php
    $forumUser = auth()->user();
    $forumLayout = $forumUser?->role === 'bhw_president' ? 'bhw-president.layout' : 'user.layout';
    $forumSection = $forumUser?->role === 'bhw_president' ? 'bhw-president-content' : 'user-content';
@endphp

@extends($forumLayout)

@section('title', 'Edit Post - ReproCare Forum')

@section($forumSection)

@push('styles')
<style>
    .edit-post-wrap { max-width:680px; margin:0 auto; }
    .post-textarea {
        width:100%; background:var(--bg-input); border:1.5px solid var(--border);
        border-radius:14px; color:var(--text); font-size:0.95rem; line-height:1.7;
        padding:1rem 1.1rem; resize:vertical; min-height:160px; outline:none;
        transition:border-color 0.2s ease, box-shadow 0.2s ease, background 0.3s ease;
    }
    .post-textarea:focus { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-glow); }
    .post-textarea::placeholder { color:var(--text-muted); opacity:0.65; }
    .char-counter { font-size:0.78rem; color:var(--text-muted); text-align:right; margin-top:0.3rem; }
    .char-counter.over { color:var(--danger); }
    .image-drop-zone {
        background:var(--bg-input); border:2px dashed var(--border); border-radius:14px;
        padding:1.5rem; text-align:center; cursor:pointer;
        transition:border-color 0.2s ease, background 0.2s ease; position:relative;
    }
    .image-drop-zone:hover, .image-drop-zone.drag-over {
        border-color:var(--primary); background:var(--primary-subtle);
    }
    .image-drop-zone input[type="file"] {
        position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
    }
    .forum-image-preview, .forum-current-image {
        width:100%; max-height:300px; object-fit:cover;
        border-radius:12px; border:1px solid var(--border);
    }
    .current-image-wrap {
        background:var(--bg-card2); border:1px solid var(--border);
        border-radius:14px; padding:1rem; margin-bottom:1rem;
        transition:background 0.4s ease;
    }
    .current-image-label {
        font-size:0.75rem; font-weight:700; text-transform:uppercase;
        letter-spacing:0.8px; color:var(--text-muted); margin-bottom:0.6rem;
        display:flex; align-items:center; gap:0.4rem;
    }
    .edit-post-header {
        background:linear-gradient(135deg, var(--color-primary), var(--primary), var(--accent-pink));
        padding:1.25rem 1.5rem; border-radius:18px 18px 0 0; color:var(--color-on-solid);
    }
    .edit-post-header h5 {
        font-family:'Plus Jakarta Sans', sans-serif; font-weight:800;
        font-size:1.1rem; margin:0;
    }
</style>
@endpush

@section('user-content')
@php($forumUser = auth()->user())
<div class="edit-post-wrap fade-in-card">

    {{-- Back --}}
    <div class="mb-3">
        <a href="{{ route('forum.show', $post->id) }}"
           style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;transition:color 0.2s;">
            <i class="bi bi-arrow-left"></i> Back to Post
        </a>
    </div>

    <div class="card" style="border-radius:20px;overflow:hidden;">

        {{-- Header --}}
        <div class="edit-post-header">
            <h5>Edit Post</h5>
            <p style="margin:0.2rem 0 0;font-size:0.8rem;opacity:0.8;">Update your post below</p>
        </div>

        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger mb-3 d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            @endif

            {{-- Original post info --}}
            <div class="d-flex align-items-center gap-3 p-3 mb-4"
                 style="background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:700;color:var(--color-on-solid);flex-shrink:0;">
                    {{ strtoupper(substr($forumUser->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:0.85rem;font-weight:700;color:var(--text);">{{ $forumUser->name }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">
                        Originally posted {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('forum.update', $post->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Content --}}
                <div class="mb-3">
                    <label for="content" class="form-label">
                        <i class="bi bi-pencil-fill me-1" style="color:var(--primary-light);"></i> Post Content
                    </label>
                    <textarea id="content" name="content" class="post-textarea"
                              maxlength="2000"
                              oninput="updateCharCount(this)"
                              required>{{ old('content', $post->content) }}</textarea>
                    <div class="char-counter" id="charCounter">0 / 2000</div>
                </div>

                {{-- Current image --}}
                @if($post->post_image)
                <div class="current-image-wrap mb-3">
                    <div class="current-image-label">
                        <i class="bi bi-image" style="color:var(--primary-light);"></i>
                        Current Image
                    </div>
                    <img src="{{ $post->post_image_url }}" alt="Current post image" class="forum-current-image mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox"
                               id="remove_post_image" name="remove_post_image" value="1"
                               style="width:1.1rem;height:1.1rem;cursor:pointer;">
                        <label for="remove_post_image"
                               style="font-size:0.85rem;color:var(--danger);cursor:pointer;font-weight:500;">
                            <i class="bi bi-trash me-1"></i> Remove current image
                        </label>
                    </div>
                </div>
                @endif

                {{-- Upload new image --}}
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-cloud-arrow-up-fill me-1" style="color:var(--primary-light);"></i>
                        {{ $post->post_image ? 'Replace Image' : 'Add Image' }}
                        <span style="color:var(--text-muted);font-weight:400;font-size:0.78rem;">(Optional)</span>
                    </label>
                    <div class="image-drop-zone" id="dropZone">
                        <input type="file" id="post_image" name="post_image"
                               accept="image/jpeg,image/jpg,image/png"
                               onchange="previewForumImage(this)">
                        <i class="bi bi-cloud-arrow-up" style="font-size:1.8rem;color:var(--text-muted);display:block;margin-bottom:0.4rem;"></i>
                        <div style="font-size:0.85rem;color:var(--text-muted);">
                            <strong>Click to upload</strong> or drag & drop
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-muted);opacity:0.7;margin-top:0.2rem;">
                            JPG, PNG up to 5MB
                        </div>
                    </div>
                    @error('post_image')
                        <div style="font-size:0.82rem;color:var(--danger);margin-top:0.35rem;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                        </div>
                    @enderror
                    {{-- Preview --}}
                    <div id="imagePreviewContainer" style="display:none;margin-top:0.75rem;">
                        <img id="postImagePreview" src="" alt="Preview" class="forum-image-preview">
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeNewImage()">
                            <i class="bi bi-trash me-1"></i> Remove New Image
                        </button>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('forum.show', $post->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Post
                    </a>
                    <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-grid me-1"></i> Forum
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateCharCount(el) {
    const count = el.value.length;
    const ctr = document.getElementById('charCounter');
    ctr.textContent = count + ' / 2000';
    ctr.classList.toggle('over', count >= 2000);
}
document.addEventListener('DOMContentLoaded', function () {
    const ta = document.getElementById('content');
    if (ta) updateCharCount(ta);
    const dz = document.getElementById('dropZone');
    if (dz) {
        dz.addEventListener('dragover',  () => dz.classList.add('drag-over'));
        dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
        dz.addEventListener('drop',      () => dz.classList.remove('drag-over'));
    }
});
function previewForumImage(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            document.getElementById('postImagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
            document.getElementById('dropZone').style.display = 'none';
        };
        r.readAsDataURL(input.files[0]);
    }
}
function removeNewImage() {
    document.getElementById('post_image').value = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('postImagePreview').src = '';
    document.getElementById('dropZone').style.display = 'block';
}
</script>
@endpush

@endsection
