@extends('user.layout')

@section('title', '{{ $material->title }} - Quiz | ReproCare')

@push('styles')
<style>
    .quiz-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:20px;
        padding:2rem;
        margin-bottom:1.5rem;
        box-shadow:var(--shadow-sm);
        transition:transform 0.2s;
    }
    .quiz-card:hover { transform:translateY(-2px); }
    .question-number {
        font-size:0.75rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:1px;
        color:var(--primary-light);
        margin-bottom:0.5rem;
    }
    .question-text { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:1.25rem; font-family:'Plus Jakarta Sans', sans-serif; }
    .option-label {
        display:flex;
        align-items:center;
        gap:0.75rem;
        padding:0.75rem 1rem;
        border:1.5px solid var(--border);
        border-radius:12px;
        margin-bottom:0.5rem;
        cursor:pointer;
        transition:all 0.15s;
        background:var(--bg-card2);
        font-size:0.9rem;
    }
    .option-label:hover { border-color:var(--primary); background:var(--primary-subtle); }
    input[type="radio"]:checked + .option-label { border-color:var(--primary); background:var(--primary-subtle); color:var(--primary-light); font-weight:600; }
    input[type="radio"] { display:none; }
    .submit-btn {
        background:linear-gradient(135deg, var(--primary), var(--primary-dark));
        border:none; border-radius:14px; color:var(--color-on-solid);
        font-weight:700; font-size:1rem; padding:0.85rem 2.5rem;
        box-shadow:0 4px 16px var(--primary-glow); cursor:pointer;
        transition:transform 0.2s, box-shadow 0.2s;
    }
    .submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px var(--primary-glow); }
</style>
@endpush

@section('user-content')

<div class="d-flex align-items-center gap-3 mb-4 fade-in-card">
    <a href="{{ route('learning.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    <div>
        <h1 class="page-title mb-0">Quiz: {{ $material->title }}
        </h1>
        <p class="page-subtitle">Answer all questions and submit when done</p>
    </div>
</div>

@php $questions = $material->quiz_data ?? []; @endphp

@if(empty($questions))
    <div class="card fade-in-card">
        <div class="empty-state">
            <i class="bi bi-patch-question empty-state-icon"></i>
            <h6>No Questions Available</h6>
            <p>This quiz doesn't have questions yet. Check back later.</p>
        </div>
    </div>
@else
    <form action="{{ route('learning.quiz.submit', $material->id) }}" method="POST">
        @csrf
        @foreach($questions as $i => $q)
            <div class="quiz-card fade-in-card">
                <div class="question-number">Question {{ $i + 1 }} of {{ count($questions) }}</div>
                <div class="question-text">{{ $q['question'] }}</div>

                <div>
                    @foreach($q['options'] as $optIndex => $option)
                        <div>
                            <input type="radio" id="q{{ $i }}_opt{{ $optIndex }}"
                                   name="answers[{{ $i }}]" value="{{ $option }}" required>
                            <label for="q{{ $i }}_opt{{ $optIndex }}" class="option-label">
                                <span style="width:24px;height:24px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;">
                                    {{ chr(65 + $optIndex) }}
                                </span>
                                {{ $option }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="text-center mt-4 mb-5 fade-in-card">
            <button type="submit" class="submit-btn">
                <i class="bi bi-check2-circle me-2"></i> Submit Quiz
            </button>
        </div>
    </form>
@endif

@endsection
