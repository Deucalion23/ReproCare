@extends('user.layout')

@section('title', 'Quiz Results - ReproCare')

@push('styles')
<style>
    .result-header { text-align:center; padding:2.5rem 2rem; background:var(--bg-card); border:1px solid var(--border); border-radius:20px; margin-bottom:1.5rem; }
    .score-ring { width:120px; height:120px; margin:0 auto 1.5rem; }
    .score-pct { font-family:'Plus Jakarta Sans', sans-serif; font-size:2.5rem; font-weight:800; }
    .result-item { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:1.25rem 1.5rem; margin-bottom:0.75rem; }
    .result-item.correct { border-color:color-mix(in srgb, var(--color-success) 40%, transparent); background:color-mix(in srgb, var(--color-success) 5%, transparent); }
    .result-item.wrong   { border-color:color-mix(in srgb, var(--color-danger) 40%, transparent);  background:color-mix(in srgb, var(--color-danger) 5%, transparent); }
</style>
@endpush

@section('user-content')
@php $pct = $total > 0 ? round(($score / $total) * 100) : 0; @endphp

{{-- Score Summary --}}
<div class="result-header fade-in-card">
    <div class="score-ring">
        <svg viewBox="0 0 36 36">
            <circle cx="18" cy="18" r="15" fill="none" stroke="var(--border)" stroke-width="3"/>
            <circle cx="18" cy="18" r="15" fill="none"
                    stroke="{{ $pct >= 80 ? 'var(--color-success)' : ($pct >= 60 ? 'var(--color-warning)' : 'var(--color-danger)') }}"
                    stroke-width="3"
                    stroke-dasharray="{{ round(($pct / 100) * 94.25, 1) }} 94.25"
                    stroke-linecap="round"
                    style="transform-origin:center;transform:rotate(-90deg);"/>
        </svg>
    </div>
    <div class="score-pct" style="color:{{ $pct >= 80 ? 'var(--color-success)' : ($pct >= 60 ? 'var(--color-warning)' : 'var(--color-danger)') }}">
        {{ $pct }}%
    </div>
    <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;margin:0.5rem 0;">
        {{ $score }} / {{ $total }} Correct
    </h3>
    <p style="color:var(--text-muted);font-size:0.9rem;">
        @if($pct >= 80) 🎉 Excellent! Great understanding!
        @elseif($pct >= 60) 👍 Good job! Keep learning!
        @else 📚 Review the material and try again.
        @endif
    </p>
    <div class="d-flex gap-2 justify-content-center mt-3">
        <a href="{{ route('learning.show', $material->id) }}" class="btn btn-outline-primary btn-sm">Review Material</a>
        <a href="{{ route('learning.index') }}" class="btn btn-primary btn-sm">Back to Learning</a>
    </div>
</div>

{{-- Per-question breakdown --}}
<h5 class="mb-3 fade-in-card" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Question Review
</h5>

@foreach($results as $i => $r)
    <div class="result-item {{ $r['is_right'] ? 'correct' : 'wrong' }} fade-in-card">
        <div class="d-flex align-items-start gap-2 mb-2">
            <i class="bi bi-{{ $r['is_right'] ? 'check-circle-fill' : 'x-circle-fill' }}"
               style="color:{{ $r['is_right'] ? 'var(--color-success)' : 'var(--color-danger)' }};font-size:1.1rem;flex-shrink:0;margin-top:2px;"></i>
            <div style="font-weight:700;font-size:0.9rem;">{{ $r['question'] }}</div>
        </div>
        @if(!$r['is_right'])
            <div style="font-size:0.82rem;color:var(--text-muted);">
                <span style="color:var(--color-danger-text);">Your answer: {{ $r['given'] ?? 'No answer' }}</span>
                &nbsp;·&nbsp;
                <span style="color:var(--color-success-text);">Correct: {{ $r['correct'] }}</span>
            </div>
        @else
            <div style="font-size:0.82rem;color:var(--color-success-text);">✓ {{ $r['correct'] }}</div>
        @endif
    </div>
@endforeach

@endsection
