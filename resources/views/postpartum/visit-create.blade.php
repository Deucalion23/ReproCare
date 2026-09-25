@extends('bhw.layout')

@section('title', 'Log Postpartum Visit - ReproCare')

@section('bhw-content')
<div class="page-hero">
    <div class="page-hero-title">Log Postpartum Visit</div>
    <p class="page-hero-subtitle mb-0">House-to-house maternal check for {{ $mother->name }} — vitals, infection screen, mood screen &amp; breastfeeding support.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('bhw.postpartum.visit-store') }}">
            @csrf
            <input type="hidden" name="mother_id" value="{{ $mother->id }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Visit Date <span class="text-danger">*</span></label>
                    <input type="date" name="visit_date" class="form-control" value="{{ old('visit_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Weeks Postpartum <span class="text-danger">*</span></label>
                    <input type="number" name="visit_week" class="form-control" value="{{ old('visit_week', 1) }}" min="0" max="52" required style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Link Newborn (optional)</label>
                    <select name="newborn_id" class="form-select" style="border-radius:12px;">
                        <option value="">—</option>
                        @foreach($mother->newborns as $nb)
                            <option value="{{ $nb->id }}" {{ (string) old('newborn_id') === (string) $nb->id ? 'selected' : '' }}>{{ $nb->display_name }} ({{ $nb->birth_date->format('M j') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Blood Pressure</label>
                    <input type="text" name="bp" class="form-control" value="{{ old('bp') }}" placeholder="e.g., 120/80" style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Temperature (°C)</label>
                    <input type="number" name="temperature" class="form-control" value="{{ old('temperature') }}" min="30" max="45" step="0.1" placeholder="e.g., 36.8" style="border-radius:12px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Bleeding <span class="text-danger">*</span></label>
                    <select name="bleeding" class="form-select" required style="border-radius:12px;">
                        <option value="none" {{ old('bleeding') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="spotting" {{ old('bleeding') === 'spotting' ? 'selected' : '' }}>Spotting</option>
                        <option value="heavy" {{ old('bleeding') === 'heavy' ? 'selected' : '' }}>Heavy (flag!)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Infection Signs</label>
                    <input type="text" name="infection_signs" class="form-control" value="{{ old('infection_signs') }}" placeholder="e.g., foul discharge, wound redness" style="border-radius:12px;">
                    <small class="text-muted">Any entry flags the visit for follow-up.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Mood Screen (0–9)</label>
                    <input type="number" name="depression_score" class="form-control" value="{{ old('depression_score') }}" min="0" max="9" placeholder="Postpartum blues check" style="border-radius:12px;">
                    <small class="text-muted">6+ flags follow-up.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Breastfeeding <span class="text-danger">*</span></label>
                    <select name="breastfeeding" class="form-select" required style="border-radius:12px;">
                        <option value="exclusive" {{ old('breastfeeding') === 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                        <option value="partial" {{ old('breastfeeding') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="none" {{ old('breastfeeding') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold" style="font-size:.8rem;">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" style="border-radius:12px;">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;"><i class="bi bi-check-circle me-1"></i> Save Visit</button>
                    <a href="{{ route('bhw.postpartum.show-mother', $mother->id) }}" class="btn btn-outline-secondary" style="border-radius:12px;">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
