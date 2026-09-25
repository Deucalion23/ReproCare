@extends('midwife.layout')

@section('title', 'Pregnancy Details - ReproCare')

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
@endphp

<div class="py-4">
    @include('midwife.partials.decision-support')
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Pregnancy Details</h1>
                <p class="page-hero-subtitle">Review the active pregnancy record and latest health summary.</p>
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
        <div class="alert alert-success fade-in-card">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger fade-in-card">{{ $errors->first() }}</div>
    @endif
    @if($pregnancy->is_locked)
        <div class="alert alert-secondary fade-in-card d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><i class="bi bi-lock-fill me-1"></i> <strong>Archived history (read-only).</strong> This delivered pregnancy is locked so the medical timeline stays intact.</div>
            <form action="{{ route('midwife.pregnancies.reopen', $pregnancy->id) }}" method="POST" class="d-flex gap-2 align-items-center m-0">
                @csrf
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reopen reason (required)" required style="min-width:220px;">
                <button type="submit" class="btn btn-sm btn-outline-dark" onclick="return confirm('Reopen this locked pregnancy for correction? The event is audit-logged.')">Reopen</button>
            </form>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 fade-in-card" style="background:var(--bg-card);">
                <div class="card-body p-4">
                    <div class="text-center">
                        <img src="{{ $patientImage }}" alt="{{ $patientName }}" style="width:92px;height:92px;border-radius:24px;object-fit:cover;border:2px solid var(--border);">
                        <h3 class="mt-3 mb-1" style="color:var(--text);">{{ $patientName }}</h3>
                        <p class="mb-2" style="color:var(--text-muted);">{{ $patientSubtitle }}</p>
                        <span class="badge {{ $isWalkIn ? 'bg-info' : 'bg-primary' }}">{{ $isWalkIn ? 'Unlinked woman' : 'Enrolled woman' }}</span>
                    </div>

                    <hr>

                    <div class="d-grid gap-3">
                        <div>
                            <div class="small text-uppercase fw-semibold" style="color:var(--text-muted);">Status</div>
                            <div style="color:var(--text);">
                                <span class="badge {{ $pregnancy->status === 'completed' ? 'bg-secondary' : 'bg-success' }}">{{ ucfirst($pregnancy->status) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="small text-uppercase fw-semibold" style="color:var(--text-muted);">Risk Level</div>
                            <div style="color:var(--text);">{{ $pregnancy->risk_level ?? 'Low' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase fw-semibold" style="color:var(--text-muted);">AOG</div>
                            <div style="color:var(--text);">{{ $pregnancy->formatted_aog }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase fw-semibold" style="color:var(--text-muted);">BMI</div>
                            <div style="color:var(--text);">{{ $bmi ? number_format($bmi, 2) . ' - ' . $bmiLabel : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm fade-in-card" style="background:var(--bg-card);">
                        <div class="card-body p-4">
                            <h5 class="mb-3" style="color:var(--text);">Pregnancy Summary</h5>
                            <div class="row g-3">
                                <div class="col-md-4"><strong>LMP</strong><div>{{ optional($pregnancy->lmp)->format('F j, Y') }}</div></div>
                                <div class="col-md-4"><strong>EDD</strong><div>{{ optional($pregnancy->edd)->format('F j, Y') }}</div></div>
                                <div class="col-md-4"><strong>Trimester</strong><div>{{ $pregnancy->trimester_name }}</div></div>
                                <div class="col-md-3"><strong>Gravida</strong><div>{{ $pregnancy->gravida ?? 'N/A' }}</div></div>
                                <div class="col-md-3"><strong>Para</strong><div>{{ $pregnancy->para ?? 'N/A' }}</div></div>
                                <div class="col-md-3"><strong>Term</strong><div>{{ $pregnancy->gtpal_term ?? 0 }}</div></div>
                                <div class="col-md-3"><strong>Preterm</strong><div>{{ $pregnancy->gtpal_preterm ?? 0 }}</div></div>
                                <div class="col-md-6"><strong>Abortions / Miscarriages</strong><div>{{ $pregnancy->gtpal_abortions ?? 0 }}</div></div>
                                <div class="col-md-6"><strong>Living Children</strong><div>{{ $pregnancy->gtpal_living_children ?? 0 }}</div></div>
                                <div class="col-md-6"><strong>Risk Assessment</strong><div>{{ ucfirst($pregnancy->risk_assessment_mode ?? 'automatic') }}</div></div>
                                <div class="col-md-6"><strong>Outcome</strong><div>{{ $pregnancy->status === 'completed' ? ($profile?->pregnancy_outcome ? ucfirst($profile->pregnancy_outcome) : 'N/A') : 'Available when completed' }}</div></div>
                                @if($pregnancy->status === 'completed' && $profile?->outcome_details)
                                    <div class="col-12"><strong>Outcome Details</strong><div>{{ $profile->outcome_details }}</div></div>
                                @endif
                                @if($pregnancy->risk_notes)
                                    <div class="col-12"><strong>Risk Notes</strong><div>{{ $pregnancy->risk_notes }}</div></div>
                                @endif
                                @if($pregnancy->notes)
                                    <div class="col-12"><strong>Notes</strong><div>{{ $pregnancy->notes }}</div></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm fade-in-card" style="background:var(--bg-card);">
                        <div class="card-body p-4">
                            <h5 class="mb-3" style="color:var(--text);">Latest Health Snapshot</h5>
                            <div class="row g-3">
                                <div class="col-md-3"><strong>Blood Pressure</strong><div>{{ $latestRecord?->bp ?? 'N/A' }}</div></div>
                                <div class="col-md-3"><strong>Weight</strong><div>{{ $weight ? $weight . ' kg' : 'N/A' }}</div></div>
                                <div class="col-md-3"><strong>Height</strong><div>{{ $height ? $height . ' cm' : 'N/A' }}</div></div>
                                <div class="col-md-3"><strong>BMI Status</strong><div>{{ $bmiLabel }}</div></div>
                                <div class="col-12">
                                    <strong>Health Conditions</strong>
                                    <div>
                                        @php
                                            $conditionLabels = collect($profile?->health_conditions ?? [])
                                                ->map(fn ($value) => $healthConditionOptions[$value] ?? $value)
                                                ->filter()
                                                ->implode(', ');
                                        @endphp
                                        {{ $conditionLabels ?: 'None recorded' }}{{ $conditionLabels && $profile?->health_condition_other ? ', ' : '' }}{{ $profile?->health_condition_other }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm fade-in-card" style="background:var(--bg-card);">
                        <div class="card-body p-0">
                            <div class="p-4 border-bottom">
                                <h5 class="mb-0" style="color:var(--text);">Linked Health Records</h5>
                            </div>
                            @if($pregnancy->healthRecords->isEmpty())
                                <div class="p-4 text-muted">No linked health records yet.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Vitals</th>
                                                <th>Risk</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pregnancy->healthRecords->sortByDesc('created_at') as $record)
                                                <tr>
                                                    <td>
                                                        <div>{{ $record->created_at->format('M j, Y') }}</div>
                                                        <small class="text-muted">{{ $record->created_at->format('h:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        <div>BP: {{ $record->bp ?? 'N/A' }}</div>
                                                        <div>Weight: {{ $record->weight ? $record->weight . ' kg' : 'N/A' }}</div>
                                                    </td>
                                                    <td>{{ $record->risk_level ?? 'Low' }}</td>
                                                    <td class="text-end pe-4">
                                                        <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-sm btn-outline-primary">
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
    </div>
</div>
@endsection
