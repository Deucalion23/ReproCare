@extends('midwife.layout')

@section('title', 'Pregnancy Details - ReproCare')

@push('styles')
    <style>
        body .main-content .page-hero { padding:0.85rem 1.1rem !important; margin-bottom:0.75rem !important; border-radius:14px !important; }
        body .main-content .page-hero .page-hero-title { font-size:1.2rem !important; margin-bottom:0.1rem !important; }
        body .main-content .page-hero .page-hero-subtitle { font-size:0.82rem !important; }
        body .main-content .page-hero .btn-hero-primary,
        body .main-content .page-hero .btn-hero-secondary { padding:0.4rem 0.85rem !important; font-size:0.85rem !important; }
        .pd-label { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); line-height:1.1; margin-bottom:2px; }
        .pd-value { font-size:0.82rem; font-weight:600; color:var(--text); line-height:1.3; overflow-wrap:anywhere; }
        .pd-section { font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--text); margin-bottom:0.5rem; }
        /* Chips share one row, so each must survive being squeezed: min-width:0 lets the
           flex/grid track shrink below its text, and the badge wraps instead of spilling
           over its own border and onto the neighbour's label. */
        .pd-stat { min-width:0; display:flex; flex-direction:column; align-items:flex-start; justify-content:center; gap:2px; padding:0.4rem 0.7rem; border:1px solid var(--border); border-radius:10px; height:100%; }
        .pd-stat .badge { max-width:100%; font-size:0.72rem; font-weight:700; line-height:1.25; padding:0.16rem 0.5rem; white-space:normal; overflow-wrap:anywhere; }
        .pd-identity { width:46px; height:46px; border-radius:14px; object-fit:cover; border:1px solid var(--border); flex:0 0 auto; }
        .pd-split { position:relative; }
        @media (min-width:1200px) {
            .pd-split::after { content:''; position:absolute; top:2px; bottom:2px; right:-0.75rem; width:1px; background:var(--border); }
        }
    </style>
@endpush

@section('midwife-content')
@php
    $profile = $pregnancy->maternalCareTargetClient;
    $isWalkIn = (bool) $pregnancy->walk_in_patient_id;
    $patient = $isWalkIn ? $pregnancy->walkInPatient : $pregnancy->woman;
    $patientName = $isWalkIn ? ($patient->full_name ?? 'Unknown woman') : ($patient->name ?? 'Unknown woman');
    $patientSubtitle = $isWalkIn ? ($patient->contact_number ?? 'No contact number') : ($patient->email ?? 'No email');
    $patientImage = $isWalkIn ? '/images/avatars/avatar-female.svg' : ($patient->profile_image_url ?? '/images/avatars/avatar-female.svg');
    $latestRecord = $pregnancy->healthRecords->sortByDesc('created_at')->first();
    $weight = $latestRecord?->weight;
    $height = $latestRecord?->height;
    $bmi = ($weight && $height) ? $weight / pow($height / 100, 2) : null;
    $bmiLabel = $bmi === null ? 'N/A' : ($bmi < 18.5 ? 'Malnourished' : ($bmi >= 30 ? 'Obese' : 'Normal'));
    $conditionLabels = collect($profile?->health_conditions ?? [])
        ->map(fn ($value) => $healthConditionOptions[$value] ?? $value)
        ->filter()
        ->implode(', ');
    $conditionText = ($conditionLabels ?: 'None recorded')
        . ($conditionLabels && $profile?->health_condition_other ? ', ' . $profile->health_condition_other : '');
@endphp

<div class="py-3">
    <div class="page-hero fade-in-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Pregnancy Details</h1>
                <p class="page-hero-subtitle">{{ $patientName }} · {{ $pregnancy->formatted_aog }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('midwife.pregnancies.edit', $pregnancy->id) }}" class="btn-hero-primary">
                    <i class="bi bi-pencil-square"></i> Edit Pregnancy
                </a>
                <a href="{{ route('midwife.pregnancies.index') }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 fade-in-card">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 fade-in-card">{{ $errors->first() }}</div>
    @endif
    @if($pregnancy->is_locked)
        <div class="alert alert-secondary py-2 fade-in-card d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small"><i class="bi bi-lock-fill me-1"></i> <strong>Archived history (read-only).</strong> This delivered pregnancy is locked so the medical timeline stays intact.</div>
            <form action="{{ route('midwife.pregnancies.reopen', $pregnancy->id) }}" method="POST" class="d-flex gap-2 align-items-center m-0">
                @csrf
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reopen reason (required)" required style="min-width:200px;">
                <button type="submit" class="btn btn-sm btn-outline-dark" onclick="return confirm('Reopen this locked pregnancy for correction? The event is audit-logged.')">Reopen</button>
            </form>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-xl-4 pd-split">
            @include('midwife.partials.decision-support', ['supportStacked' => true])
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm fade-in-card mb-3" style="background:var(--bg-card);">
                <div class="card-body p-3">
                    {{-- Identity and chips are stacked, not side-by-side columns: the
                         auto-width identity block used to eat the row and leave each chip
                         ~40px wide, so the Status badge spilled over its own border. --}}
                    <div class="pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <img src="{{ $patientImage }}" alt="{{ $patientName }}" class="pd-identity">
                            <div class="min-w-0">
                                <div class="fw-bold text-truncate" style="color:var(--text);font-size:1rem;line-height:1.2;">{{ $patientName }}</div>
                                <div class="small text-truncate" style="color:var(--text-muted);">{{ $patientSubtitle }}</div>
                            </div>
                            <span class="badge {{ $isWalkIn ? 'bg-info' : 'bg-primary' }}">{{ $isWalkIn ? 'Unlinked woman' : 'Enrolled woman' }}</span>
                        </div>
                        <div class="row row-cols-2 row-cols-lg-4 g-2 mt-2">
                            <div class="col">
                                <div class="pd-stat">
                                    <div class="pd-label">Status</div>
                                    <span class="badge {{ $pregnancy->status === 'completed' ? 'bg-secondary' : 'bg-success' }}">{{ ucfirst($pregnancy->status) }}</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="pd-stat">
                                    <div class="pd-label">Risk Level</div>
                                    <div class="pd-value">{{ $pregnancy->risk_level ?? 'Low' }}</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="pd-stat">
                                    <div class="pd-label">AOG</div>
                                    <div class="pd-value">{{ $pregnancy->formatted_aog }}</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="pd-stat">
                                    <div class="pd-label">BMI</div>
                                    <div class="pd-value">{{ $bmi ? number_format($bmi, 2) . ' - ' . $bmiLabel : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="pd-section">Pregnancy Summary</h5>
                    <div class="row g-2">
                        <div class="col-6 col-md-4"><div class="pd-label">LMP</div><div class="pd-value">{{ optional($pregnancy->lmp)->format('F j, Y') }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">EDD</div><div class="pd-value">{{ optional($pregnancy->edd)->format('F j, Y') }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Trimester</div><div class="pd-value">{{ $pregnancy->trimester_name }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Risk Assessment</div><div class="pd-value">{{ ucfirst($pregnancy->risk_assessment_mode ?? 'automatic') }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Outcome</div><div class="pd-value">{{ $pregnancy->status === 'completed' ? ($profile?->pregnancy_outcome ? ucfirst($profile->pregnancy_outcome) : 'N/A') : 'Available when completed' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Gravida</div><div class="pd-value">{{ $pregnancy->gravida ?? 'N/A' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Para</div><div class="pd-value">{{ $pregnancy->para ?? 'N/A' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Term</div><div class="pd-value">{{ $pregnancy->gtpal_term ?? 0 }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Preterm</div><div class="pd-value">{{ $pregnancy->gtpal_preterm ?? 0 }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Abortions / Miscarriages</div><div class="pd-value">{{ $pregnancy->gtpal_abortions ?? 0 }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Living Children</div><div class="pd-value">{{ $pregnancy->gtpal_living_children ?? 0 }}</div></div>
                        @if($pregnancy->status === 'completed' && $profile?->outcome_details)
                            <div class="col-6 col-md-4"><div class="pd-label">Outcome Details</div><div class="pd-value">{{ $profile->outcome_details }}</div></div>
                        @endif
                        @if($pregnancy->risk_notes)
                            <div class="col-12"><div class="pd-label">Risk Notes</div><div class="pd-value">{{ $pregnancy->risk_notes }}</div></div>
                        @endif
                        @if($pregnancy->notes)
                            <div class="col-12"><div class="pd-label">Notes</div><div class="pd-value">{{ $pregnancy->notes }}</div></div>
                        @endif
                    </div>

                    <h5 class="pd-section mt-3 pt-3 border-top">Latest Health Snapshot</h5>
                    <div class="row g-2">
                        <div class="col-6 col-md-4"><div class="pd-label">Blood Pressure</div><div class="pd-value">{{ $latestRecord?->bp ?? 'N/A' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Weight</div><div class="pd-value">{{ $weight ? $weight . ' kg' : 'N/A' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">Height</div><div class="pd-value">{{ $height ? $height . ' cm' : 'N/A' }}</div></div>
                        <div class="col-6 col-md-4"><div class="pd-label">BMI Status</div><div class="pd-value">{{ $bmiLabel }}</div></div>
                        <div class="col-12"><div class="pd-label">Health Conditions</div><div class="pd-value">{{ $conditionText }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm fade-in-card" style="background:var(--bg-card);">
                <div class="card-body p-0">
                    <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                        <h5 class="pd-section mb-0">Linked Health Records</h5>
                        <span class="badge rounded-pill text-bg-light border">{{ $pregnancy->healthRecords->count() }}</span>
                    </div>
                    @if($pregnancy->healthRecords->isEmpty())
                        <div class="p-3 text-muted small">No linked health records yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>BP</th>
                                        <th>Weight</th>
                                        <th>Height</th>
                                        <th>Risk</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pregnancy->healthRecords->sortByDesc('created_at') as $record)
                                        <tr>
                                            <td>
                                                <div class="pd-value">{{ $record->created_at->format('M j, Y') }}</div>
                                                <small class="text-muted">{{ $record->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td class="small">{{ $record->bp ?? 'N/A' }}</td>
                                            <td class="small">{{ $record->weight ? $record->weight . ' kg' : 'N/A' }}</td>
                                            <td class="small">{{ $record->height ? $record->height . ' cm' : 'N/A' }}</td>
                                            <td class="small">{{ $record->risk_level ?? 'Low' }}</td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-sm btn-outline-primary py-0">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
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
@endsection
