@extends('user.layout')

@section('title', 'Add Pregnancy - ReproCare')

@push('styles')
<style>
    .self-report-card { border:0; border-radius:24px; overflow:hidden; box-shadow:0 12px 34px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent); }
    .self-report-card .card-header { padding:1.35rem 1.5rem; background:var(--color-surface) !important; color:var(--color-text) !important; border-bottom:1px solid var(--color-border); }
    .self-report-heading { display:flex; align-items:center; gap:.8rem; }
    .self-report-heading-icon { width:42px; height:42px; display:grid; place-items:center; border-radius:13px; color:var(--color-primary-on); background:var(--color-primary); }
    .verification-notice { display:flex; align-items:flex-start; gap:.85rem; padding:1rem 1.05rem; margin-bottom:1.35rem; border:1px solid color-mix(in srgb, var(--color-primary) 25%, transparent); border-radius:16px; background:var(--color-primary-soft); color:var(--color-text); }
    .verification-notice i { color:var(--color-primary-text); font-size:1.2rem; line-height:1.35; }
    .verification-notice strong { display:block; font-size:.9rem; margin-bottom:.18rem; }
    .verification-notice p { margin:0; font-size:.82rem; line-height:1.55; color:var(--color-text-muted); }
    .preg-form-section { margin:1.45rem 0 .85rem; padding-top:1.2rem; border-top:1px solid var(--color-border); }
    .preg-form-section h6 { margin:0; font-size:.75rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--color-primary-text); }
    .preg-form-section p { margin:.25rem 0 0; font-size:.78rem; color:var(--color-text-muted); }
    .self-report-disclaimer { display:flex; gap:.7rem; margin-top:1.35rem; padding:.9rem 1rem; border-radius:14px; background:var(--color-surface-soft); color:var(--color-text-muted); font-size:.78rem; line-height:1.55; }
    .self-report-disclaimer i { color:var(--color-primary-text); font-size:1rem; }
</style>
@endpush

@section('user-content')
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Add Pregnancy</div>
            <p class="page-hero-subtitle">Register a new pregnancy to start tracking.</p>
        </div>
    </div>
</div>
<div class="py-4">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card self-report-card">
                <div class="card-header">
                    <div class="self-report-heading">
                        <span class="self-report-heading-icon"><i class="bi bi-heart-pulse"></i></span>
                        <div>
                            <h5 class="mb-1 fw-800">Report a Pregnancy</h5>
                            <p class="mb-0 small text-muted">Start personal tracking with details you know today.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="verification-notice" role="note">
                        <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                        <div>
                            <strong>Pending Clinical Verification</strong>
                            <p>This is a self-reported record for your personal tracking. Your BHW or midwife will review and confirm the details during your next visit.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('user.pregnancies.store') }}" id="userPregnancyForm">
                        @csrf

                        <div class="preg-form-section mt-0 pt-0 border-0">
                            <h6>Your pregnancy dates</h6>
                            <p>Your estimated due date and pregnancy age are calculated from your last menstrual period.</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Birthdate</label>
                                <input type="text" class="form-control" value="{{ optional(auth()->user()->date_of_birth)->format('Y-m-d') ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Current Age</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->age !== 'N/A' ? auth()->user()->age . ' years old' : 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="aog_display" class="form-label">AOG</label>
                                <input type="text" class="form-control" id="aog_display" readonly>
                                <input type="hidden" name="aog" id="aog">
                            </div>

                            <div class="col-md-4">
                                <label for="lmp" class="form-label">Last menstrual period (LMP) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="lmp" name="lmp" value="{{ old('lmp') }}" max="{{ now()->subDay()->format('Y-m-d') }}" aria-describedby="lmp-help" required>
                                <div class="form-text" id="lmp-help">Choose the first day of your last period.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="edd" class="form-label">Estimated due date</label>
                                <input type="date" class="form-control" id="edd" aria-describedby="edd-help" readonly>
                                <div class="form-text" id="edd-help">Calculated automatically (40 weeks from LMP).</div>
                            </div>
                            <div class="col-md-2">
                                <label for="gravida" class="form-label">Gravida</label>
                                <input type="number" class="form-control" id="gravida" name="gravida" value="{{ old('gravida', 1) }}" min="1" required>
                            </div>

                            <div class="col-12"><div class="preg-form-section"><h6>Optional health details</h6><p>Share only details you know. Your care team will verify them with you.</p></div></div>
                            <div class="col-md-2">
                                <label for="para" class="form-label">Para</label>
                                <input type="number" class="form-control" id="para" name="para" value="{{ old('para', 0) }}" min="0" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Blood Pressure</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" id="bp_systolic" name="bp_systolic" value="{{ old('bp_systolic') }}" placeholder="Systolic">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic') }}" placeholder="Diastolic">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="{{ old('weight') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" step="0.1" class="form-control" id="height" name="height" value="{{ old('height') }}">
                                <div class="form-text">Below 122 cm (4 ft) flags short-stature risk.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="bmi_display" class="form-label">BMI</label>
                                <input type="text" class="form-control" id="bmi_display" readonly>
                            </div>
                            <div class="col-12">
                                <label for="obstetric_history" class="form-label">Obstetric History</label>
                                <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="3">{{ old('obstetric_history') }}</textarea>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase mb-2">Lifestyle Factors</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="lifestyle_smoking" name="lifestyle_smoking" value="1"
                                        {{ old('lifestyle_smoking') || in_array(old('smoking_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lifestyle_smoking">Smoking</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="lifestyle_alcohol" name="lifestyle_alcohol" value="1"
                                        {{ old('lifestyle_alcohol') || in_array(old('alcohol_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lifestyle_alcohol">Drinking Alcohol</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="lifestyle_drugs" name="lifestyle_drugs" value="1"
                                        {{ old('lifestyle_drugs') || in_array(old('drug_use_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lifestyle_drugs">Drugs</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="lifestyle_other" class="form-label">Other Lifestyle Factors</label>
                                <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="2">{{ old('lifestyle_other', old('lifestyle_notes')) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="self-report-disclaimer">
                            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                            <span>Self-reported information does not replace medical advice, diagnosis, or a prenatal checkup. Please contact your health worker promptly for urgent concerns.</span>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-check"></i> Submit for Clinical Verification
                            </button>
                            <a href="{{ route('user.pregnancies.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Pregnancies
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const lmpInput = document.getElementById('lmp');
    const eddInput = document.getElementById('edd');
    const aogInput = document.getElementById('aog');
    const aogDisplay = document.getElementById('aog_display');
    const weightInput = document.getElementById('weight');
    const heightInput = document.getElementById('height');
    const bmiDisplay = document.getElementById('bmi_display');

    function updateDates() {
        if (!lmpInput.value) return;
        const lmpDate = new Date(lmpInput.value);
        const now = new Date();
        const eddDate = new Date(lmpDate);
        eddDate.setDate(eddDate.getDate() + 280);
        eddInput.value = eddDate.toISOString().split('T')[0];

        const diffDays = Math.max(0, Math.floor((now - lmpDate) / 86400000));
        const weeks = Math.floor(diffDays / 7);
        const days = diffDays % 7;
        aogInput.value = weeks;
        aogDisplay.value = `${weeks} weeks ${days} days`;
    }

    function updateBmi() {
        const weight = parseFloat(weightInput.value || '');
        const height = parseFloat(heightInput.value || '');
        if (!weight || !height) {
            bmiDisplay.value = '';
            return;
        }
        bmiDisplay.value = (weight / Math.pow(height / 100, 2)).toFixed(2);
    }

    lmpInput?.addEventListener('change', updateDates);
    weightInput?.addEventListener('input', updateBmi);
    heightInput?.addEventListener('input', updateBmi);
    updateDates();
    updateBmi();
</script>
@endpush
@endsection
