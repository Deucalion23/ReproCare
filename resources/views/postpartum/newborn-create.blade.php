@extends('bhw.layout')

@section('title', 'Log Birth - ReproCare')

@section('bhw-content')
<div class="page-hero">
    <div class="page-hero-title">Log Birth</div>
    <p class="page-hero-subtitle mb-0">Record the newborn for {{ $mother->name }}{{ $pregnancy?->delivery_date ? ' · delivered ' . $pregnancy->delivery_date->format('M j, Y') : '' }}. An immunization schedule is generated automatically.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('bhw.postpartum.newborn-store') }}">
            @csrf
            <input type="hidden" name="mother_id" value="{{ $mother->id }}">
            <input type="hidden" name="pregnancy_id" value="{{ $pregnancy?->id }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Baby Name (optional)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Leave blank if unnamed" style="border-radius:12px;">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Sex</label>
                    <select name="sex" class="form-select" style="border-radius:12px;">
                        <option value="">—</option>
                        <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Birth Date <span class="text-danger">*</span></label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $pregnancy?->delivery_date?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Birth Weight (kg)</label>
                    <input type="number" name="birth_weight_kg" class="form-control" value="{{ old('birth_weight_kg') }}" min="0.3" max="8" step="0.01" placeholder="e.g., 3.20" style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Feeding <span class="text-danger">*</span></label>
                    <select name="feeding_type" class="form-select" required style="border-radius:12px;">
                        <option value="exclusive_breast" {{ old('feeding_type') === 'exclusive_breast' ? 'selected' : '' }}>Exclusive breastfeeding</option>
                        <option value="mixed" {{ old('feeding_type') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                        <option value="formula" {{ old('feeding_type') === 'formula' ? 'selected' : '' }}>Formula</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Newborn Danger Signs</label>
                    <input type="text" name="danger_signs" class="form-control" value="{{ old('danger_signs') }}" placeholder="e.g., poor suck, fever, fast breathing" style="border-radius:12px;">
                    <small class="text-muted">Leave blank when none — any entry flags follow-up.</small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" style="border-radius:12px;">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;"><i class="bi bi-check-circle me-1"></i> Save Birth Record</button>
                    <a href="{{ route('bhw.postpartum.show-mother', $mother->id) }}" class="btn btn-outline-secondary" style="border-radius:12px;">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
