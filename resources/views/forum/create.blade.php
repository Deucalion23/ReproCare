@php
    $forumUser = auth()->user();
    $forumLayout = $forumUser?->role === 'bhw_president' ? 'bhw-president.layout' : 'user.layout';
    $forumSection = $forumUser?->role === 'bhw_president' ? 'bhw-president-content' : 'user-content';
@endphp

@extends($forumLayout)

@section('title', 'New Post - ReproCare Forum')

@section($forumSection)

@push('styles')
<style>
    .create-post-wrap { max-width:680px; margin:0 auto; }
    .create-post-card { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:20px; overflow:hidden; box-shadow:var(--wp-shadow-sm); }
    .create-back-link { color:var(--color-text-muted); font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; font-weight:600; }
    .create-back-link:hover { color:var(--color-text); }

    /* Upload drop zone — borderless soft fill */
    .image-drop-zone {
        background:var(--color-bg); background-color:var(--color-bg);
        border:none;
        border-radius:16px;
        padding:2rem 1.75rem;
        text-align:center;
        cursor:pointer;
        transition:background 0.2s ease;
        position:relative;
    }
    .image-drop-zone:hover,
    .image-drop-zone.drag-over {
        border:none;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
    }
    .image-drop-zone input[type="file"] {
        position:absolute;
        inset:0;
        opacity:0;
        cursor:pointer;
        width:100%;
        height:100%;
    }
    .drop-zone-icon {
        width:52px; height:52px; border-radius:50%;
        background:var(--color-surface); background-color:var(--color-surface);
        color:var(--color-text);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:1.4rem;
        margin-bottom:0.75rem;
        transition:color 0.2s ease;
    }
    .image-drop-zone:hover .drop-zone-icon { color:var(--color-secondary-text); }
    .drop-zone-text { font-size:0.88rem; color:var(--color-text); font-weight:600; }
    .drop-zone-text strong { font-weight:800; }
    .drop-zone-sub  { font-size:0.76rem; color:var(--color-text-muted); margin-top:0.25rem; }

    /* Image preview */
    .forum-image-preview {
        width:100%;
        max-height:300px;
        object-fit:cover;
        border-radius:14px;
        border:none;
    }

    /* Post textarea */
    .post-textarea {
        width:100%;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        border:none;
        border-radius:14px;
        color:var(--color-text);
        font-size:0.95rem;
        line-height:1.7;
        padding:1rem 1.1rem;
        resize:vertical;
        min-height:160px;
        outline:none;
        transition:box-shadow 0.2s ease, background 0.2s ease;
    }
    .post-textarea:focus {
        border:none;
        background:var(--color-surface); background-color:var(--color-surface);
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent);
    }
    .post-textarea::placeholder { color:var(--color-text-muted); opacity:1; }

    /* Char counter */
    .char-counter { font-size:0.76rem; color:var(--color-text-muted); text-align:right; margin-top:0.3rem; font-weight:600; }
    .char-counter.over { color:var(--color-danger-text); }

    /* Card header — clean white, no gradient */
    .create-post-header {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        padding:1.4rem 1.5rem 0.4rem;
        border-radius:20px 20px 0 0;
        color:var(--color-text);
    }
    .create-post-header h5 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800;
        font-size:1.25rem;
        margin:0;
        color:var(--color-text);
        letter-spacing:-0.01em;
    }
    .create-post-header p { margin:0.3rem 0 0; font-size:0.85rem; color:var(--color-text-muted); }
    .create-author-box { background:var(--color-bg); background-color:var(--color-bg); border:none; border-radius:14px; }
    .create-author-avatar { width:40px; height:40px; border-radius:50%; background:var(--color-surface-strong); background-color:var(--color-surface-strong); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:800; color:var(--color-on-solid); flex-shrink:0; }
    .create-author-role { color:var(--color-secondary-text); font-weight:800; }
    .create-label { font-weight:800; font-size:0.86rem; color:var(--color-text); }
    .create-label i { color:var(--color-secondary-text); }
    .create-label span { color:var(--color-text-muted); font-weight:600; font-size:0.8rem; }
    .create-publish-btn { border:none; border-radius:999px; padding:0.62rem 1.4rem; font-weight:800; font-size:0.86rem; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .create-publish-btn:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; transform:translateY(-1px); }
    .create-cancel-btn { border:none; border-radius:999px; padding:0.62rem 1.3rem; font-weight:800; font-size:0.86rem; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; color:var(--color-text) !important; }
    .create-cancel-btn:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
    .create-footer { border:none; background:var(--color-surface); background-color:var(--color-surface); padding:0.9rem 1.5rem; }
    .create-footer small { color:var(--color-text-muted); font-size:0.78rem; display:flex; align-items:center; gap:0.4rem; }
    .create-footer small i { color:var(--color-success-text); }
</style>
@endpush

@section('user-content')
@php($forumUser = auth()->user())

<div class="create-post-wrap fade-in-card">

    {{-- Back --}}
    <div class="mb-3">
        <a href="{{ route('forum.index') }}" class="create-back-link">
            <i class="bi bi-arrow-left"></i> Back to Forum
        </a>
    </div>

    <div class="card create-post-card">

        {{-- Header --}}
        <div class="create-post-header">
            <div style="position:relative;z-index:1;">
                <h5>Create New Post</h5>
                <p>
                    Share your experiences, questions, or support with the community
                </p>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">

            {{-- Flash --}}
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3 d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Author preview --}}
            <div class="d-flex align-items-center gap-3 mb-3 p-3 create-author-box">
                <div class="create-author-avatar">
                    {{ strtoupper(substr($forumUser->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:800; font-size:0.9rem; color:var(--color-text);">{{ $forumUser->name }}</div>
                    <div style="font-size:0.75rem; color:var(--color-text-muted);">
                        Posting as
                        <span class="create-author-role">
                            {{ $forumUser->role === 'midwife' ? 'Midwife' : ($forumUser->role === 'bhw' ? 'BHW' : 'Patient') }}
                        </span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('forum.store') }}" enctype="multipart/form-data" id="createPostForm">
                @csrf

                {{-- Text content --}}
                <div class="mb-3">
                    <label for="content" class="form-label create-label">
                        What's on your mind?
                    </label>
                    <textarea id="content"
                              name="content"
                              class="post-textarea"
                              placeholder="Share your thoughts, experiences, or questions with the community..."
                              maxlength="2000"
                              oninput="updateCharCount(this)"
                              required>{{ old('content') }}</textarea>
                    <div class="char-counter" id="charCounter">0 / 2000</div>
                </div>

                {{-- Image upload --}}
                <div class="mb-4">
                    <label class="form-label create-label">
                        Add Image
                        <span>(Optional)</span>
                    </label>

                    <div class="image-drop-zone" id="dropZone">
                        <input type="file"
                               id="post_image"
                               name="post_image"
                               accept="image/jpeg,image/jpg,image/png"
                               onchange="previewForumImage(this)">
                        <i class="bi bi-cloud-arrow-up drop-zone-icon"></i>
                        <div class="drop-zone-text">
                            <strong>Click to upload</strong> or drag & drop an image
                        </div>
                        <div class="drop-zone-sub">JPG, PNG up to 5MB</div>
                    </div>

                    {{-- Error --}}
                    @error('post_image')
                        <div style="font-size:0.82rem;color:var(--danger);margin-top:0.35rem;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                        </div>
                    @enderror

                    {{-- Preview --}}
                    <div id="imagePreviewContainer" style="display:none;margin-top:0.75rem;">
                        <img id="postImagePreview" src="" alt="Preview" class="forum-image-preview">
                        <button type="button"
                                class="btn btn-danger btn-sm mt-2"
                                onclick="removeForumImage()">
                            <i class="bi bi-trash me-1"></i> Remove Image
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn create-publish-btn">
                        <i class="bi bi-send-fill me-1"></i> Publish Post
                    </button>
                    <a href="{{ route('forum.index') }}" class="btn create-cancel-btn">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>

            </form>
        </div>

        {{-- Footer tip --}}
        <div class="card-footer create-footer">
            <small>
                <i class="bi bi-shield-check-fill"></i>
                Be kind, be helpful, and respect everyone's privacy.
            </small>
        </div>

    </div>
</div>

@push('scripts')
<script>
function updateCharCount(el) {
    const count   = el.value.length;
    const counter = document.getElementById('charCounter');
    counter.textContent = count + ' / 2000';
    counter.classList.toggle('over', count >= 2000);
}

// Init on load
document.addEventListener('DOMContentLoaded', function () {
    const ta = document.getElementById('content');
    if (ta && ta.value.length > 0) updateCharCount(ta);
});

function previewForumImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('postImagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
            document.getElementById('dropZone').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeForumImage() {
    document.getElementById('post_image').value = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('postImagePreview').src = '';
    document.getElementById('dropZone').style.display = 'block';
}

// Drag-over effect on drop zone
const dz = document.getElementById('dropZone');
if (dz) {
    dz.addEventListener('dragover',  () => dz.classList.add('drag-over'));
    dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
    dz.addEventListener('drop',      () => dz.classList.remove('drag-over'));
}
</script>
@endpush

@endsection
