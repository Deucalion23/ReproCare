@extends('user.layout')

@section('title', 'Pregnancy Week ' . $week . ' Guide - ReproCare')

@section('user-content')

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-card">
    <div>
        <h1 class="page-title">Week {{ $week }} Pregnancy Guide
        </h1>
        <p class="page-subtitle">What's happening with you and your baby at week {{ $week }}</p>
    </div>
    <a href="{{ route('learning.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> All Materials
    </a>
</div>

{{-- Week navigator --}}
<div class="card fade-in-card mb-4" style="padding:1rem 1.5rem;">
    <div class="d-flex align-items-center justify-content-between">
        @if($week > 1)
            <a href="{{ route('learning.week-guide', $week - 1) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left me-1"></i> Week {{ $week - 1 }}
            </a>
        @else
            <span></span>
        @endif
        <div class="d-flex gap-1 flex-wrap justify-content-center">
            @for($w = 1; $w <= 40; $w++)
                <a href="{{ route('learning.week-guide', $w) }}"
                   class="btn btn-sm {{ $w == $week ? 'btn-primary' : 'btn-outline-secondary' }}"
                   style="min-width:36px;font-size:0.75rem;padding:0.25rem 0.4rem;">
                   {{ $w }}
                </a>
            @endfor
        </div>
        @if($week < 40)
            <a href="{{ route('learning.week-guide', $week + 1) }}" class="btn btn-sm btn-outline-secondary">
                Week {{ $week + 1 }} <i class="bi bi-chevron-right ms-1"></i>
            </a>
        @else
            <span></span>
        @endif
    </div>
</div>

@if($materials->count() > 0)
    <div class="row g-3">
        @foreach($materials as $material)
            <div class="col-md-6 fade-in-card">
                <div class="card h-100" style="border-radius:18px;border:1px solid var(--border);background:var(--bg-card);">
                    <div class="card-body" style="padding:1.5rem;">
                        @php
                            $typeIcons = ['article'=>'bi-file-text','video'=>'bi-play-circle','quiz'=>'bi-patch-question','link'=>'bi-link-45deg','file'=>'bi-file-earmark'];
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi {{ $typeIcons[$material->material_type] ?? 'bi-file' }}" style="color:var(--primary-light);font-size:1.1rem;"></i>
                            <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">{{ ucfirst($material->material_type) }}</span>
                        </div>
                        <h5 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;color:var(--text);margin-bottom:0.75rem;">{{ $material->title }}</h5>
                        <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:1rem;">{{ \Illuminate\Support\Str::limit($material->content, 120) }}</p>
                    </div>
                    <div class="card-footer" style="background:var(--bg-card2);border-top:1px solid var(--border);border-radius:0 0 18px 18px;padding:0.75rem 1.5rem;">
                        <a href="{{ route('learning.show', $material->id) }}" class="btn btn-sm btn-primary w-100">
                            @if($material->material_type === 'quiz')
                                <i class="bi bi-patch-question-fill me-1"></i> Take Quiz
                            @elseif($material->material_type === 'video')
                                <i class="bi bi-play-fill me-1"></i> Watch
                            @else
                                <i class="bi bi-eye me-1"></i> View
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card fade-in-card">
        <div class="empty-state">
            <i class="bi bi-calendar-x empty-state-icon"></i>
            <h6>No Content for Week {{ $week }} Yet</h6>
            <p>Check other weeks or browse all materials.</p>
            <a href="{{ route('learning.index') }}" class="btn btn-primary btn-sm px-4">Browse All</a>
        </div>
    </div>
@endif

@endsection
