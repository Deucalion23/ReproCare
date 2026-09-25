@extends('midwife.layout')

@section('title', 'Manage Learning Materials - ReproCare')

@section('midwife-content')
<div class="py-2">
    {{-- Page Hero --}}
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">
                    Learning Materials
                </h1>
                <p class="page-hero-subtitle">
                    Educational resources, patient care guidelines, and training materials.
                </p>
            </div>
            <a href="{{ route('midwife.learning.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> Add Material
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
             style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:16px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- White Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(124,58,237,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-primary-text);">
                        <i class="bi bi-collection-play-fill"></i>
                    </div>
                    <span style="background:var(--color-primary-soft);color:var(--color-primary-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Total</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Total Materials</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $materials->total() }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-book"></i> Published library
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(2,132,199,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-info-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-info-text);">
                        <i class="bi bi-file-text-fill"></i>
                    </div>
                    <span style="background:var(--color-info-soft);color:var(--color-info-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Guides</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Articles</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $materials->where('material_type', 'article')->count() }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-journal-text"></i> Reading guides
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(4,120,87,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-success-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-success-text);">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <span style="background:var(--color-success-soft);color:var(--color-success-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Media</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Videos / Links</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $materials->whereIn('material_type', ['video', 'link'])->count() }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-camera-video"></i> Video training
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(217,119,6,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-warning-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-warning-text);">
                        <i class="bi bi-file-earmark-arrow-down-fill"></i>
                    </div>
                    <span style="background:var(--color-warning-soft);color:var(--color-warning-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Docs</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Downloadables</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $materials->where('material_type', 'file')->count() }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-download"></i> PDF pamphlets
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="card fade-in-card mb-4" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px;">
        <div class="card-body p-3">
            <form action="{{ route('midwife.learning.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-12">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="search" class="form-control form-control-sm ps-5" name="search" placeholder="Search by title or topic..." value="{{ request('search') }}" style="height:42px; border-radius:12px; border:1px solid var(--color-border);">
                    </div>
                </div>
                <div class="col-md-4 col-8">
                    <select class="form-select form-select-sm" name="type" onchange="this.form.submit()" style="height:42px; border-radius:12px; border:1px solid var(--color-border);">
                        <option value="">All Material Types</option>
                        <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>Articles &amp; Guides</option>
                        <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>Links &amp; Online Streams</option>
                        <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>Files &amp; Documents</option>
                        <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Playable Videos</option>
                    </select>
                </div>
                <div class="col-md-2 col-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="height:42px; border-radius:12px; font-weight:700;">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    @if(request('search') || request('type'))
                        <a href="{{ route('midwife.learning.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px; width:42px; border-radius:12px; border:1px solid var(--color-border);" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Materials Grid --}}
    <div class="row g-4">
        @if($materials->count() > 0)
            @foreach($materials as $material)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border)!important; border-radius:20px; overflow:hidden; box-shadow:0 4px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);">
                        @php $adminYtId = $material->youtube_id; @endphp
                        <div class="position-relative" style="height:190px; background:var(--color-bg);">
                            @if($adminYtId)
                                <img src="{{ $material->youtube_thumbnail_url }}"
                                     alt="{{ $material->title }}"
                                     class="w-100 h-100"
                                     style="object-fit:cover;"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                <div class="d-none align-items-center justify-content-center w-100 h-100 text-muted">
                                    <i class="bi bi-youtube" style="font-size:3rem; color:var(--color-danger-text);"></i>
                                </div>
                                <span class="position-absolute top-50 start-50 translate-middle rounded-circle d-flex align-items-center justify-content-center text-white shadow"
                                      style="width:52px; height:52px; background:color-mix(in srgb, var(--color-danger) 92%, transparent);">
                                    <i class="bi bi-play-fill fs-4 ms-0.5"></i>
                                </span>
                                <span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white" style="border-radius:8px; font-size:0.7rem;">
                                    <i class="bi bi-youtube text-danger me-1"></i>YouTube
                                </span>
                            @else
                            @if($material->image_url)
                                <img src="{{ $material->image_url }}"
                                     alt="{{ $material->title }}"
                                     class="w-100 h-100"
                                     style="object-fit:cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            @endif
                            <div class="d-{{ $material->image_url ? 'none' : 'flex' }} align-items-center justify-content-center w-100 h-100 text-muted">
                                <i class="bi bi-journal-richtext" style="font-size:3rem; color:var(--color-primary-text);"></i>
                            </div>
                            @endif
                            <span class="position-absolute top-0 end-0 m-3 badge rounded-pill" 
                                  style="background:color-mix(in srgb, var(--color-surface) 92%, transparent); color:var(--color-text); backdrop-filter:blur(4px); font-weight:700; border:1px solid var(--color-border); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent);">
                                {{ ucfirst($material->material_type) }}
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-2" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text); font-size:1.1rem; line-height:1.4;">
                                {{ $material->title }}
                            </h5>
                            <p class="card-text text-muted small mb-3" style="line-height:1.6;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($material->content), 120) }}
                            </p>

                            <div class="mt-auto pt-3 d-flex justify-content-between align-items-center" style="border-top:1px solid var(--color-border);">
                                <small class="text-muted" style="font-size:0.78rem;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $material->created_at->format('M d, Y') }}
                                </small>
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <a href="{{ route('midwife.learning.show', $material->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       style="border-radius:10px; padding:0.4rem 0.85rem; font-size:0.82rem; font-weight:700;">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" 
                                                style="border-radius:10px; border:1px solid var(--color-border); width:34px; height:34px; padding:0; display:flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-three-dots-vertical" style="color:var(--color-text-muted);"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:12px; border:1px solid var(--color-border); font-size:0.85rem;">
                                            <li>
                                                <a href="{{ route('midwife.learning.edit', $material->id) }}" class="dropdown-item">
                                                    <i class="bi bi-pencil me-2"></i> Edit Material
                                                </a>
                                            </li>
                                            <li>
                                                <x-archive-form :action="route('midwife.learning.destroy', $material->id)" label="Archive" btnClass="dropdown-item text-warning" icon="bi bi-archive" confirmText="Archive this material? It will be retained in the archives." />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="card text-center py-5 fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:20px;">
                    <i class="bi bi-journal-x text-muted" style="font-size:3.5rem;"></i>
                    <h4 class="mt-3 fw-bold" style="color:var(--color-text);">No learning materials found</h4>
                    <p class="text-muted">Start creating educational content for mothers and community health workers.</p>
                    <div class="mt-2">
                        <a href="{{ route('midwife.learning.create') }}" class="btn btn-primary px-4 py-2" style="border-radius:12px; font-weight:700;">
                            <i class="bi bi-plus-circle me-1"></i> Add First Material
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    {{-- Pagination --}}
    @if($materials->count() > 0)
        <div class="d-flex justify-content-center mt-4">
            {{ $materials->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
