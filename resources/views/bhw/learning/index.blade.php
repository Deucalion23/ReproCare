@extends('bhw.layout')

@section('title', 'Learning Materials - ReproCare')

@section('bhw-content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Learning Materials</h1>
            <p class="text-muted mb-0">Educational resources for your health journey</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Search -->
            <form class="d-flex" method="GET" action="{{ route('bhw.learning.index') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search materials..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <!-- Filter Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item {{ !request('type') ? 'active' : '' }}" href="{{ route('bhw.learning.index') }}">All Materials</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item {{ request('type') == 'article' ? 'active' : '' }}" href="{{ route('bhw.learning.index', ['type' => 'article']) }}">
                        <i class="bi bi-file-text text-primary"></i> Articles
                    </a></li>
                    <li><a class="dropdown-item {{ request('type') == 'link' ? 'active' : '' }}" href="{{ route('bhw.learning.index', ['type' => 'link']) }}">
                        <i class="bi bi-link-45deg text-success"></i> Links
                    </a></li>
                    <li><a class="dropdown-item {{ request('type') == 'file' ? 'active' : '' }}" href="{{ route('bhw.learning.index', ['type' => 'file']) }}">
                        <i class="bi bi-file-earmark text-warning"></i> Files
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white p-3 me-3">
                        <i class="bi bi-file-text fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-success">Articles</h6>
                        <h4 class="mb-0">{{ $materials->where('material_type', 'article')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white p-3 me-3">
                        <i class="bi bi-link-45deg fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-primary">Links</h6>
                        <h4 class="mb-0">{{ $materials->where('material_type', 'link')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-white p-3 me-3">
                        <i class="bi bi-file-earmark fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-warning">Files</h6>
                        <h4 class="mb-0">{{ $materials->where('material_type', 'file')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        @if($materials->count() > 0)
            @foreach($materials as $material)
                @php
                    $typeColors = [
                        'article' => ['bg' => 'bg-primary', 'icon' => 'bi-file-text', 'light' => 'bg-primary bg-opacity-10'],
                        'link' => ['bg' => 'bg-success', 'icon' => 'bi-link-45deg', 'light' => 'bg-success bg-opacity-10'],
                        'file' => ['bg' => 'bg-warning', 'icon' => 'bi-file-earmark', 'light' => 'bg-warning bg-opacity-10'],
                    ];
                    $typeConfig = $typeColors[$material->material_type] ?? $typeColors['article'];
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <!-- Card Header with Thumbnail or Icon -->
                        <div class="card-header border-0 {{ $typeConfig['light'] }} py-4 text-center position-relative">
                            @if($material->image_url)
                                <div class="rounded overflow-hidden shadow-sm" style="width:100%; height:120px;">
                                    <img src="{{ $material->image_url }}"
                                         alt="{{ $material->title }}"
                                         class="w-100 h-100"
                                         style="object-fit:cover;"
                                         onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='inline-block';">
                                </div>
                                <div class="d-none p-3 rounded-circle bg-white shadow-sm" style="width:80px; height:80px; margin:0 auto;">
                                    <i class="bi {{ $typeConfig['icon'] }} {{ $typeConfig['bg'] }} text-white p-2 rounded fs-3" style="display:inline-block; width:50px; height:50px; line-height:26px;"></i>
                                </div>
                            @else
                                <div class="d-inline-block p-3 rounded-circle bg-white shadow-sm">
                                    <i class="bi {{ $typeConfig['icon'] }} {{ $typeConfig['bg'] }} text-white p-2 rounded fs-3" style="display:inline-block; width:50px; height:50px; line-height:26px;"></i>
                                </div>
                            @endif
                            <span class="badge {{ $typeConfig['bg'] }} position-absolute top-0 end-0 m-3">
                                {{ ucfirst($material->material_type) }}
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column pt-4">
                            <h5 class="card-title fw-bold mb-3">{{ $material->title }}</h5>
                            <p class="card-text text-muted flex-grow-1">{{ Illuminate\Support\Str::limit($material->content, 120) }}</p>
                            
                            <div class="d-flex align-items-center text-muted small mb-3">
                                <i class="bi bi-calendar3 me-2"></i>
                                {{ $material->created_at->format('F j, Y') }}
                            </div>
                            
                            <div class="mt-auto">
                                @if($material->material_type === 'link' && $material->link_url)
                                    <a href="{{ $material->link_url }}" target="_blank" class="btn {{ $typeConfig['bg'] }} text-white w-100">
                                        <i class="bi bi-box-arrow-up-right me-2"></i> Open Link
                                    </a>
                                @else
                                    <a href="{{ route('bhw.learning.show', $material->id) }}" class="btn {{ $typeConfig['bg'] }} text-white w-100">
                                        <i class="bi bi-eye me-2"></i> Read More
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-book text-muted" style="font-size:5rem;"></i>
                    </div>
                    <h3 class="text-muted">No learning materials found</h3>
                    <p class="text-muted">{{ request('search') ? 'Try a different search term.' : 'Check back later for educational resources.' }}</p>
                    @if(request('search') || request('type'))
                        <a href="{{ route('bhw.learning.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-arrow-left me-2"></i> View All Materials
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <!-- Pagination -->
    @if($materials->count() > 0)
        <div class="d-flex justify-content-center mt-5">
            {{ $materials->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .hover-card {
        transition:all 0.3s ease;
    }
    .hover-card:hover {
        transform:translateY(-5px);
        box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important;
    }
</style>
@endsection
