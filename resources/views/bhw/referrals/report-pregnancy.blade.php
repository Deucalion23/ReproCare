@extends('bhw.layout')

@section('title', 'Report Pregnancy to Midwife - ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Report Pregnancy to Midwife</div>
                <p class="page-hero-subtitle">Send {{ $pregnancy->patient_name }}'s pregnancy details plus the recorded health information straight to the midwife you choose.</p>
            </div>
            <a href="{{ route('bhw.pregnancies.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Pregnancies
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-warning fade-in-card mb-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger fade-in-card mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($activeReferral)
        <div class="alert alert-info fade-in-card mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill"></i>
            <div>This pregnancy already has an active report with {{ $activeReferral->assignedMidwife?->name ?? 'the midwife' }} ({{ ucfirst($activeReferral->status) }}). The midwife acts on the existing report.</div>
        </div>
    @endif

    <div class="card fade-in-card mb-4">
        <div class="card-header"><h5 class="mb-0">Pregnancy Details (sent as-is)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted text-uppercase fw-bold d-block">Patient</small><span class="fw-semibold">{{ $pregnancy->patient_name }}</span></div>
                <div class="col-md-2"><small class="text-muted text-uppercase fw-bold d-block">LMP</small>{{ $pregnancy->lmp?->format('M d, Y') ?? 'N/A' }}</div>
                <div class="col-md-2"><small class="text-muted text-uppercase fw-bold d-block">EDD</small>{{ $pregnancy->edd?->format('M d, Y') ?? 'N/A' }}</div>
                <div class="col-md-2"><small class="text-muted text-uppercase fw-bold d-block">AOG</small>{{ $pregnancy->formatted_aog ?? 'N/A' }}</div>
                <div class="col-md-2"><small class="text-muted text-uppercase fw-bold d-block">Risk</small>
                    <span class="badge {{ $pregnancy->is_high_risk ? 'bg-danger' : 'bg-success' }}">{{ $pregnancy->is_high_risk ? 'High Risk' : 'Normal' }}</span>
                </div>
                @if($pregnancy->notes)
                    <div class="col-12"><small class="text-muted text-uppercase fw-bold d-block">Notes</small>{{ $pregnancy->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('bhw.referrals.store-pregnancy-report') }}">
        @csrf
        <input type="hidden" name="pregnancy_id" value="{{ $pregnancy->id }}">

        <div class="card fade-in-card mb-4">
            <div class="card-header"><h5 class="mb-0">Attach Recorded Health Information</h5></div>
            <div class="card-body p-0">
                @if($records->count())
                    <div class="list-group list-group-flush">
                        @foreach($records as $record)
                            <label class="list-group-item d-flex gap-3 align-items-start">
                                <input type="checkbox" class="form-check-input mt-1" name="health_record_ids[]" value="{{ $record->id }}"
                                    {{ in_array($record->id, old('health_record_ids', $records->pluck('id')->take(3)->all())) ? 'checked' : '' }}>
                                <span>
                                    <span class="fw-semibold">{{ $record->created_at?->format('M d, Y h:i A') ?? 'Record' }}</span>
                                    <span class="badge bg-light text-dark border ms-1">{{ $record->risk_level ?? 'Low' }}</span>
                                    <br><small class="text-muted">
                                        BP {{ $record->bp ?? '—' }} · HR {{ $record->heart_rate ?? '—' }} · Temp {{ $record->temperature ?? '—' }}
                                        · by {{ $record->recordedBy?->name ?? 'BHW' }}
                                    </small>
                                    @if($record->notes)<br><small>{{ \Illuminate\Support\Str::limit($record->notes, 120) }}</small>@endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted p-4 mb-0">No health records on file for this pregnancy yet. The report will carry the pregnancy details above.</p>
                @endif
            </div>
        </div>

        <div class="card fade-in-card mb-4">
            <div class="card-header"><h5 class="mb-0">Midwife &amp; Message</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Receiving Midwife *</label>
                        <select name="assigned_midwife_id" class="form-select" required>
                            <option value="">Select midwife</option>
                            @foreach($midwives as $midwife)
                                <option value="{{ $midwife->id }}" {{ (string) old('assigned_midwife_id') === (string) $midwife->id ? 'selected' : '' }}>{{ $midwife->name }}</option>
                            @endforeach
                        </select>
                        @if($midwives->isEmpty())
                            <small class="text-danger">No approved midwife on file — ask the RHU to register one first.</small>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Urgency *</label>
                        <select name="urgency" class="form-select" required>
                            <option value="routine" {{ old('urgency', 'routine') === 'routine' ? 'selected' : '' }}>Routine</option>
                            <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Reason *</label>
                        <input type="text" name="reason" class="form-control" required maxlength="255"
                            placeholder="Example: newly identified pregnancy, needs first prenatal assessment"
                            value="{{ old('reason', 'Newly identified pregnancy — requesting initial prenatal assessment') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Details for the Midwife</label>
                        <textarea name="bhw_notes" class="form-control" rows="3" maxlength="1000" placeholder="Where and when she was found, symptoms, contact follow-up...">{{ old('bhw_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between flex-wrap gap-2">
            <a href="{{ route('bhw.pregnancies.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" @if($activeReferral) disabled @endif>
                <i class="bi bi-send-fill me-1"></i> Report to Midwife
            </button>
        </div>
    </form>
</div>
@endsection
