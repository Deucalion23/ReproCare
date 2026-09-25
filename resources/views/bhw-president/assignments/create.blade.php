@extends('bhw-president.layout')

@section('title', 'Create Assignment - BHW President Portal | ReproCare')

@section('bhw-president-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Create Assignment
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Assign BHW to a Purok
            </p>
        </div>
        <a href="{{ route('bhw-president.assignments.index') }}" class="btn-hero-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
         style="background:color-mix(in srgb, var(--color-secondary) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-secondary) 30%, transparent); color:var(--danger); border-radius:10px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
    </div>
@endif

    {{-- ═══════════════════════════════
         CREATE FORM
    ════════════════════════════════ --}}
    <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
        <div class="card-header"
             style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
            <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">New Assignment Details
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('bhw-president.assignments.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bhw_id" class="form-label fw-bold" style="color:var(--text);">BHW <span class="text-danger">*</span></label>
                            <select name="bhw_id" id="bhw_id" class="form-select" style="background:var(--bg-card2); border:1px solid var(--border); color:var(--text);" required>
                                <option value="">Select BHW...</option>
                                @foreach($bhws as $bhw)
                                <option value="{{ $bhw->id }}" {{ old('bhw_id') == $bhw->id ? 'selected' : '' }}>{{ $bhw->name }} ({{ $bhw->barangay }})</option>
                                @endforeach
                            </select>
                            @error('bhw_id')
                                <div class="text-danger mt-1" style="font-size:0.85rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="purok_id" class="form-label fw-bold" style="color:var(--text);">Purok <span class="text-danger">*</span></label>
                            <select name="purok_id" id="purok_id" class="form-select" style="background:var(--bg-card2); border:1px solid var(--border); color:var(--text);" required>
                                <option value="">Select Purok...</option>
                                @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>{{ $purok->name }} ({{ $purok->barangay }})</option>
                                @endforeach
                            </select>
                            @error('purok_id')
                                <div class="text-danger mt-1" style="font-size:0.85rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold" style="color:var(--text);">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" style="background:var(--bg-card2); border:1px solid var(--border); color:var(--text);" placeholder="Add any additional notes about this assignment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger mt-1" style="font-size:0.85rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn"
                       style="background:linear-gradient(135deg,var(--primary),var(--accent-violet)); color:var(--color-on-solid); border:none; border-radius:10px; padding:0.6rem 1.5rem; font-weight:500;">
                        <i class="bi bi-check-circle-fill me-1"></i> Create Assignment
                    </button>
                    <a href="{{ route('bhw-president.assignments.index') }}" class="btn"
                       style="background:var(--bg-card2); border:1px solid var(--border); color:var(--text); border-radius:10px; padding:0.6rem 1.5rem; font-weight:500;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
