@extends('midwife.layout')

@section('title', 'Health Record Details - ReproCare')

@section('midwife-content')
@php
    $isWalkIn = (bool) $healthRecord->walk_in_patient_id;
    $patient = $isWalkIn ? $healthRecord->walkInPatient : $healthRecord->woman;
    $patientName = $healthRecord->patient_name;
    $patientSubtitle = $isWalkIn ? ($healthRecord->patient_contact ?? 'Unlinked woman') : (optional($healthRecord->woman)->email ?? 'Enrolled woman');
    $patientImage = $isWalkIn ? '/images/avatars/avatar-female.svg' : (optional($healthRecord->woman)->profile_image_url ?? '/images/avatars/avatar-female.svg');
    $bmi = ($healthRecord->weight && $healthRecord->height) ? $healthRecord->weight / pow($healthRecord->height / 100, 2) : null;
    $bmiLabel = $bmi === null ? 'N/A' : ($bmi < 18.5 ? 'Malnourished' : ($bmi >= 30 ? 'Obese' : 'Normal'));
    $allRecords = $healthRecord->user_id
        ? $healthRecord->woman->healthRecords()->with('recordedBy')->orderBy('created_at', 'desc')->get()
        : \App\Models\HealthRecord::with('recordedBy')->where('walk_in_patient_id', $healthRecord->walk_in_patient_id)->orderBy('created_at', 'desc')->get();
@endphp

<div class="py-2">
    {{-- ═══════════════════════════════
         PAGE HERO & HEADER ACTIONS
    ═══════════════════════════════ --}}
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Health Record Details</h1>
                <p class="page-hero-subtitle">Comprehensive clinical snapshot, vitals, measurements, and assessment notes.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="{{ route('midwife.health-records.index') }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Records
                </a>
                <a href="{{ route('midwife.health-records.edit', $healthRecord->id) }}" class="btn-hero-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit Record
                </a>
                <button type="button" class="btn btn-sm d-flex align-items-center gap-1" style="height:42px; padding:0 1rem; border-radius:12px; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text); font-weight:600;" onclick="confirmArchive()">
                    <i class="bi bi-archive text-muted"></i> Archive
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Patient Info Card --}}
        <div class="col-lg-4">
            <div class="card h-100 fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:24px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent); overflow:hidden;">
                <div class="card-body p-4 text-center">
                    @if($patientImage && !str_contains($patientImage, 'avatar-female.svg'))
                        <img src="{{ $patientImage }}" alt="{{ $patientName }}" style="width:84px;height:84px;border-radius:20px;object-fit:cover;border:2px solid var(--color-border);">
                    @else
                        <div class="mx-auto" style="width:84px;height:84px;border-radius:20px;background:var(--color-primary-soft);border:2px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-primary-text);font-size:2rem;font-weight:800;">
                            {{ strtoupper(substr($patientName, 0, 1)) }}
                        </div>
                    @endif
                    <h4 class="mt-3 mb-1" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif; font-weight:700;">{{ $patientName }}</h4>
                    <p class="mb-2" style="color:var(--color-text-muted); font-size:0.875rem;">{{ $patientSubtitle }}</p>
                    <span class="badge" style="background:{{ $isWalkIn ? 'var(--color-info-soft)' : 'var(--color-primary-soft)' }}; color:{{ $isWalkIn ? 'var(--color-info-text)' : 'var(--color-primary)' }}; border:1px solid {{ $isWalkIn ? 'var(--color-info-soft)' : 'var(--color-border)' }}; font-weight:600; padding:0.4rem 0.8rem; border-radius:9999px;">
                        {{ $isWalkIn ? 'Unlinked Patient' : 'Enrolled Patient' }}
                    </span>

                    <hr style="border-color:var(--color-border); margin:1.5rem 0;">

                    <div class="text-start d-grid gap-3">
                        <div>
                            <span class="text-uppercase" style="font-size:0.7rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Recorded On</span>
                            <span style="font-weight:600; color:var(--color-text); font-size:0.95rem;">{{ $healthRecord->created_at->format('M j, Y · h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-uppercase" style="font-size:0.7rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Recorded By</span>
                            <span style="font-weight:600; color:var(--color-text); font-size:0.95rem;"><x-record-author :record="$healthRecord" :barangay="$healthRecord->woman?->barangay ?? $healthRecord->walkInPatient?->barangay" /></span>
                        </div>
                        <div>
                            <span class="text-uppercase" style="font-size:0.7rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Contact Number</span>
                            <span style="font-weight:600; color:var(--color-text); font-size:0.95rem;">{{ $healthRecord->patient_contact ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-uppercase" style="font-size:0.7rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Barangay</span>
                            <span style="font-weight:600; color:var(--color-text); font-size:0.95rem;">{{ $healthRecord->patient_barangay ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if($healthRecord->user_id)
                        <div class="mt-4 pt-2">
                            <a href="{{ route('midwife.patient-details', $healthRecord->user_id) }}" class="btn w-100 d-flex align-items-center justify-content-center gap-2" style="background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-primary-text); font-weight:600; border-radius:12px; height:42px;">
                                <i class="bi bi-person"></i> View Patient Profile
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Clinical Data Cards --}}
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                {{-- Vital Signs Section (Individual Light Cards) --}}
                <div class="card fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:24px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-700" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Vital Signs
                            </h5>
                            <span class="badge" style="background:var(--color-primary-soft); color:var(--color-primary-text); border:1px solid var(--color-border); font-weight:600; border-radius:9999px; padding:0.35rem 0.75rem;">
                                Clinical Measurements
                            </span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Blood Pressure</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->bp ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">mmHg</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Heart Rate</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->heart_rate ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">bpm</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Temperature</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->temperature ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">°C</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Oxygen Sat.</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->oxygen_saturation ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Weight</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->weight ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">kg</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Height</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $healthRecord->height ?: '—' }} <small style="font-size:0.75rem; font-weight:500; color:var(--color-text-muted);">cm</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">BMI</span>
                                    <div class="mt-1" style="font-size:1.15rem; font-weight:700; color:var(--color-text);">
                                        {{ $bmi ? number_format($bmi, 1) : '—' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">BMI Status</span>
                                    <div class="mt-1" style="font-size:1.05rem; font-weight:700; color:var(--color-text);">
                                        {{ $bmiLabel }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assessment & Lab Data --}}
                <div class="card fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:24px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent);">
                    <div class="card-body p-4">
                        <h5 class="mb-3 fw-700" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Clinical Assessment
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Risk Level</span>
                                    <div class="mt-2">
                                        @switch($healthRecord->risk_level)
                                            @case('Low')
                                                <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                    <i class="bi bi-shield-check"></i> Low Risk
                                                </span>
                                                @break
                                            @case('Medium')
                                                <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                    <i class="bi bi-exclamation-circle"></i> Medium Risk
                                                </span>
                                                @break
                                            @case('High')
                                                <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-danger-soft); border:1px solid var(--color-danger-soft); color:var(--color-danger-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                    <i class="bi bi-exclamation-triangle"></i> High Risk
                                                </span>
                                                @break
                                            @default
                                                <span style="font-weight:600; color:var(--color-text);">{{ $healthRecord->risk_level ?? 'Not specified' }}</span>
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Hemoglobin</span>
                                    <div class="mt-1" style="font-weight:700; color:var(--color-text); font-size:1rem;">
                                        {{ $healthRecord->hemoglobin ? $healthRecord->hemoglobin . ' g/dL' : 'None recorded' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Immunization Status</span>
                                    <div class="mt-1" style="font-weight:700; color:var(--color-text); font-size:1rem;">
                                        {{ $healthRecord->immunization_status ?? 'Up to date' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Contraceptive Use</span>
                                    <div class="mt-1" style="font-weight:600; color:var(--color-text);">
                                        {{ $healthRecord->contraceptive_use ?? 'None recorded' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3" style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px;">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block;">Lab Results</span>
                                    <div class="mt-1" style="font-weight:600; color:var(--color-text);">
                                        {{ $healthRecord->lab_results ?? 'Normal / No findings' }}
                                    </div>
                                </div>
                            </div>
                            @if($healthRecord->gestational_age || $healthRecord->fetal_heart_rate)
                                <div class="col-md-6">
                                    <div class="p-3" style="background:var(--color-primary-soft); border:1px solid var(--color-border); border-radius:16px;">
                                        <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-primary-text); letter-spacing:0.5px; display:block;">Gestational Age</span>
                                        <div class="mt-1" style="font-weight:700; color:var(--color-text); font-size:1.1rem;">
                                            {{ $healthRecord->gestational_age }} weeks
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3" style="background:var(--color-primary-soft); border:1px solid var(--color-border); border-radius:16px;">
                                        <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-primary-text); letter-spacing:0.5px; display:block;">Fetal Heart Rate</span>
                                        <div class="mt-1" style="font-weight:700; color:var(--color-text); font-size:1.1rem;">
                                            {{ $healthRecord->fetal_heart_rate }} bpm
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Notes And Recommendations --}}
                @if($healthRecord->notes || $healthRecord->recommendations)
                    <div class="card fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:24px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent);">
                        <div class="card-body p-4">
                            <h5 class="mb-3 fw-700" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Clinical Notes & Recommendations
                            </h5>
                            @if($healthRecord->notes)
                                <div class="mb-3 p-3" style="background:var(--color-bg); border-radius:16px; border:1px solid var(--color-border);">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-text-muted); letter-spacing:0.5px; display:block; margin-bottom:0.5rem;">Clinical Notes</span>
                                    <div style="color:var(--color-text); font-size:0.9rem; line-height:1.6;">{!! nl2br(e($healthRecord->notes)) !!}</div>
                                </div>
                            @endif
                            @if($healthRecord->recommendations)
                                <div class="p-3" style="background:var(--color-success-soft); border-radius:16px; border:1px solid var(--color-success-soft);">
                                    <span class="text-uppercase" style="font-size:0.68rem; font-weight:700; color:var(--color-success-text); letter-spacing:0.5px; display:block; margin-bottom:0.5rem;">Care Recommendations</span>
                                    <div style="color:var(--color-success-text); font-size:0.9rem; line-height:1.6;">{!! nl2br(e($healthRecord->recommendations)) !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Patient's Previous Health Records --}}
                <div class="card fade-in-card" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:24px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent); overflow:hidden;">
                    <div class="card-header p-4 border-bottom" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <h5 class="mb-0 fw-700" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Patient Health Records History
                        </h5>
                    </div>
                    @if($allRecords->isEmpty())
                        <div class="p-4 text-center" style="color:var(--color-text-muted);">No other health records found for this patient.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="font-size:0.875rem;">
                                <thead style="background:var(--color-bg); border-bottom:1px solid var(--color-border);">
                                    <tr style="color:var(--color-text-muted); font-weight:700; font-size:0.75rem; text-transform:uppercase;">
                                        <th style="padding:1rem 1.5rem;">Date</th>
                                        <th style="padding:1rem;">Vitals Summary</th>
                                        <th style="padding:1rem;">Risk</th>
                                        <th style="padding:1rem 1.5rem; text-align:right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allRecords as $record)
                                        <tr style="border-bottom:1px solid var(--color-primary-soft);" class="{{ $record->id === $healthRecord->id ? 'bg-[var(--color-primary-soft)]' : '' }}">
                                            <td style="padding:1rem 1.5rem;">
                                                <div style="font-weight:700; color:var(--color-text);">{{ $record->created_at->format('M j, Y') }}</div>
                                                <small style="color:var(--color-text-muted);">{{ $record->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td style="padding:1rem;">
                                                <div style="color:var(--color-text); font-weight:500;">
                                                    BP: <strong>{{ $record->bp ?? '—' }}</strong> &nbsp;·&nbsp;
                                                    Weight: <strong>{{ $record->weight ? $record->weight . ' kg' : '—' }}</strong>
                                                </div>
                                            </td>
                                            <td style="padding:1rem;">
                                                @switch($record->risk_level)
                                                    @case('Low')
                                                        <span class="badge" style="background:var(--color-success-soft); color:var(--color-success-text); border:1px solid var(--color-success-soft); border-radius:9999px; font-weight:600;">Low Risk</span>
                                                        @break
                                                    @case('Medium')
                                                        <span class="badge" style="background:var(--color-warning-soft); color:var(--color-warning-text); border:1px solid var(--color-warning); border-radius:9999px; font-weight:600;">Medium Risk</span>
                                                        @break
                                                    @case('High')
                                                        <span class="badge" style="background:var(--color-danger-soft); color:var(--color-danger-text); border:1px solid var(--color-danger-soft); border-radius:9999px; font-weight:600;">High Risk</span>
                                                        @break
                                                    @default
                                                        <span class="badge" style="background:var(--color-bg); color:var(--color-text-muted); border:1px solid var(--color-border); border-radius:9999px;">{{ $record->risk_level ?? '—' }}</span>
                                                @endswitch
                                            </td>
                                            <td style="padding:1rem 1.5rem; text-align:right;">
                                                @if($record->id !== $healthRecord->id)
                                                    <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-sm" style="background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-primary-text); font-weight:600; border-radius:8px; padding:0.25rem 0.75rem;">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                @else
                                                    <span class="badge" style="background:var(--color-primary-soft); color:var(--color-primary-text); border:1px solid var(--color-border); border-radius:9999px; font-weight:600; padding:0.35rem 0.75rem;">Viewing Current</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Health Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('midwife.health-records.archive', $healthRecord->id) }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <p class="text-muted small mb-2">The record will be hidden from active lists but <strong>retained for audit</strong> and can be restored from Archived Records.</p>
                    <label class="form-label fw-bold small" for="archiveReason">Reason for archiving <span class="text-danger">*</span></label>
                    <textarea id="archiveReason" name="reason" class="form-control" rows="2" maxlength="1000" placeholder="e.g. duplicate entry, entered in error" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white fw-bold">Archive Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmArchive() {
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}
</script>
@endsection
