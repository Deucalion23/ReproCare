@extends('bhw.layout')

@section('title', $material->title . ' - ReproCare')

@section('bhw-content')
<div class="container-fluid py-4">
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge bg-success">{{ ucfirst($material->material_type) }}</span>
                    <a href="{{ route('bhw.learning.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Materials
                    </a>
                </div>
                <div class="card-body">
                    @if($material->image_url)
                        <div class="mb-4">
                            <img src="{{ $material->image_url }}"
                                 alt="{{ $material->title }}"
                                 class="img-fluid rounded"
                                 style="width:100%; height:300px; object-fit:cover;"
                                 onerror="this.style.display='none'">
                        </div>
                    @endif
                    <h2 class="card-title mb-4">{{ $material->title }}</h2>
                    
                    @if($material->material_type === 'link' && $material->link_url)
                        <div class="alert alert-info">
                            <i class="bi bi-box-arrow-up-right"></i> 
                            This is an external resource. 
                            <a href="{{ $material->link_url }}" target="_blank" class="alert-link">Click here to visit the resource</a>.
                        </div>
                    @endif
                    
                    <div class="content-section">
                        @if($material->material_type === 'article')
                            <div class="prose">
                                {!! nl2br(e($material->content)) !!}
                            </div>
                        @elseif($material->material_type === 'file')
                            <div class="alert bg-light">
                                <i class="bi bi-file-earmark-text"></i> 
                                <strong>Downloadable Resource:</strong><br>
                                {!! nl2br(e($material->content)) !!}
                            </div>
                            <div class="text-center mt-4">
                                <a href="{{ $material->file_url ?: '#' }}" class="btn btn-success" {{ $material->file_url ? 'target=_blank' : '' }}>
                                    <i class="bi bi-download"></i> Download Material
                                </a>
                            </div>
                        @else
                            <div class="alert bg-light">
                                <strong>Resource Description:</strong><br>
                                {!! nl2br(e($material->content)) !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="bi bi-calendar"></i> Added on {{ $material->created_at->format('F j, Y') }}
                        @if($material->updated_at->gt($material->created_at))
                            | Updated {{ $material->updated_at->diffForHumans() }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('bhw.learning.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-arrow-left"></i> Back to Materials
                        </a>
                        @if($material->material_type === 'link' && $material->link_url)
                            <a href="{{ $material->link_url }}" target="_blank" class="btn btn-success">
                                <i class="bi bi-box-arrow-up-right"></i> Visit Resource
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">Related Topics</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Explore more learning materials on related topics to enhance your knowledge.</p>
                    <a href="{{ route('bhw.learning.index') }}" class="btn btn-outline-success btn-sm">
                        Browse All Materials
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
