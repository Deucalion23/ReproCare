@php
    $layout = auth()->user()?->isCho() ? 'cho.layout' : 'midwife.layout';
    $section = auth()->user()?->isCho() ? 'cho-content' : 'midwife-content';
@endphp

@extends($layout)

@section('title', 'Publish Learning Material - ReproCare')

@section($section)
<div class="py-3" style="width:100%; max-width:100%;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-800 text-dark mb-1" style="font-family:'Plus Jakarta Sans',sans-serif;">Publish Learning Material
            </h2>
            <p class="text-muted mb-0" style="font-size:0.9rem;">Paste a YouTube link for video lessons, or publish articles, links, and training guides.</p>
        </div>
        <a href="{{ route('midwife.learning.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" style="border-radius:10px;">
                <i class="bi bi-arrow-left"></i> Back to Learning Materials
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius:14px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('midwife.learning.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="row g-4">
            <!-- Left Column - Main Content Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm border mb-4" style="border-radius:16px; background:var(--bg-card); border-color:var(--border) !important;">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <h6 class="fw-800 mb-0 text-dark">Media Content Information</h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" 
                                   value="{{ old('title') }}" required placeholder="e.g., Essential Antenatal Care & Nutrition in Third Trimester" style="border-radius:10px;">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Material Type --}}
                        <div class="mb-3">
                            <label for="material_type" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Material Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('material_type') is-invalid @enderror" id="material_type" name="material_type" required onchange="handleTypeChange(this.value)" style="border-radius:10px;">
                                <option value="">-- Select Content Format --</option>
                                <option value="video" {{ old('material_type', 'video') === 'video' ? 'selected' : '' }}>🎬 YouTube Video (Embed Link)</option>
                                <option value="article" {{ old('material_type') === 'article' ? 'selected' : '' }}>📄 Educational Article</option>
                                <option value="link" {{ old('material_type') === 'link' ? 'selected' : '' }}>🔗 External Resource Link</option>
                                <option value="file" {{ old('material_type') === 'file' ? 'selected' : '' }}>📁 Downloadable Clinical Document (PDF / PPT / Doc)</option>
                            </select>
                        </div>

                        {{-- YouTube Video Link + Live Preview --}}
                        <div class="mb-3" id="video_url_group">
                            <label for="video_url" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">
                                YouTube Video Link <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-youtube text-danger"></i></span>
                                <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url"
                                       value="{{ old('video_url') }}" placeholder="e.g., https://www.youtube.com/watch?v=..." style="border-radius:0 10px 10px 0;">
                            </div>
                            <small class="text-muted">Watch, Shorts, share (youtu.be), live, or embed links — a live preview appears once a valid link is pasted.</small>
                            @error('video_url')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @include('learning.partials.youtube-live-preview')
                        </div>

                        {{-- Document File Upload (videos use YouTube embeds) --}}
                        <div class="mb-3" id="file_upload_group">
                            <label for="file" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">
                                Upload Document (PDF, DOCX, PPT, Images, Audio)
                            </label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.webp,.mp3,.wav" style="border-radius:10px;">
                            <small class="text-muted">
                                <i class="bi bi-file-earmark-play me-1"></i>Video lessons are embedded from YouTube — no file upload needed.
                            </small>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- External Link URL --}}
                        <div class="mb-3" id="link_url_group" style="display:none;">
                            <label for="link_url" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">External Resource URL</label>
                            <input type="url" class="form-control" id="link_url" name="link_url" value="{{ old('link_url') }}" placeholder="https://doh.gov.ph/maternal-health" style="border-radius:10px;">
                        </div>
                        
                        {{-- Content / Description --}}
                        <div class="mb-3">
                            <label for="content" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Description &amp; Key Teaching Points <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required
                                      placeholder="Provide clinical counseling guidelines, key discussion points, or overview of the material..." style="border-radius:10px;">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Categories & Thumbnail -->
            <div class="col-lg-4">
                <div class="card shadow-sm border mb-4" style="border-radius:16px; background:var(--bg-card); border-color:var(--border) !important;">
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <h6 class="fw-800 mb-0 text-dark">Category &amp; Target</h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Category --}}
                        <div class="mb-3">
                            <label for="category" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Topic Category</label>
                            <select class="form-select" id="category" name="category" style="border-radius:10px;">
                                <option value="prenatal-care" {{ old('category') === 'prenatal-care' ? 'selected' : '' }}>🤰 Prenatal Care</option>
                                <option value="nutrition" {{ old('category') === 'nutrition' ? 'selected' : '' }}>🥗 Maternal Nutrition</option>
                                <option value="warning-signs" {{ old('category') === 'warning-signs' ? 'selected' : '' }}>⚠️ Warning Signs &amp; Preeclampsia</option>
                                <option value="family-planning" {{ old('category') === 'family-planning' ? 'selected' : '' }}>👨‍👩‍👧 Family Planning &amp; Contraception</option>
                                <option value="postpartum" {{ old('category') === 'postpartum' ? 'selected' : '' }}>👶 Postpartum &amp; Newborn Care</option>
                                <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General Health Education</option>
                            </select>
                        </div>

                        {{-- Week Number (Optional for Pregnancy Guide) --}}
                        <div class="mb-3">
                            <label for="week_number" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Pregnancy Week # (Optional)</label>
                            <input type="number" class="form-control" id="week_number" name="week_number" value="{{ old('week_number') }}" min="1" max="42" placeholder="e.g. 12" style="border-radius:10px;">
                            <small class="text-muted">Target specific gestational week guide</small>
                        </div>

                        {{-- Cover Image Thumbnail --}}
                        <div class="mb-4">
                            <label for="image" class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Cover Thumbnail Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" style="border-radius:10px;">
                            <small class="text-muted">PNG, JPG, or WEBP up to 4MB.</small>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-700 py-2.5" style="border-radius:10px;">
                                <i class="bi bi-upload me-1"></i> Publish Material
                            </button>
                            <a href="{{ route('midwife.learning.index') }}" class="btn btn-light border py-2" style="border-radius:10px;">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function handleTypeChange(val) {
    const videoGrp = document.getElementById('video_url_group');
    const fileGrp = document.getElementById('file_upload_group');
    const linkGrp = document.getElementById('link_url_group');

    if (val === 'video') {
        videoGrp.style.display = 'block';
        fileGrp.style.display = 'none';
        linkGrp.style.display = 'none';
    } else if (val === 'link') {
        videoGrp.style.display = 'none';
        fileGrp.style.display = 'none';
        linkGrp.style.display = 'block';
    } else if (val === 'file') {
        videoGrp.style.display = 'none';
        fileGrp.style.display = 'block';
        linkGrp.style.display = 'none';
    } else {
        videoGrp.style.display = 'none';
        fileGrp.style.display = 'none';
        linkGrp.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    handleTypeChange(document.getElementById('material_type').value);
});
</script>
@endpush

@endsection
