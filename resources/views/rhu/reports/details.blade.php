@extends('rhu.layout')

@section('title', 'Patient MNCHN Profile - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Maternal &amp; Child Health Profile</div>
            <p class="page-hero-subtitle">
                <i class="bi bi-folder-fill me-1"></i> Continuous clinical audit file for patient <strong>{{ $woman->name }}</strong>.
            </p>
        </div>
        <a href="{{ route('rhu.reports.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Patient General Demographics Card -->
    <div class="col-lg-4">
        <div class="card fade-in-card mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Demographics
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <x-patient-avatar :patient="$woman" :size="90" />
                    <h5 class="fw-700 mt-3 text-dark mb-1">{{ $woman->name }}</h5>
                    <span class="badge bg-primary-soft text-primary fw-600 text-xs px-2.5 py-1">
                        {{ str_replace('_', ' ', ucfirst($woman->pregnancy_status ?? 'not pregnant')) }}
                    </span>
                </div>

                <div class="d-flex flex-column gap-3 text-xs">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Email Address</span>
                        <span class="fw-700 text-dark">{{ $woman->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Contact Number</span>
                        <span class="fw-700 text-dark">{{ $woman->contact_number ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Age</span>
                        <span class="fw-700 text-dark">{{ $woman->age ?? 'N/A' }} years old</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Date of Birth</span>
                        <span class="fw-700 text-dark">{{ $woman->date_of_birth ? $woman->date_of_birth->format('F j, Y') : 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Barangay</span>
                        <span class="fw-700 text-dark">{{ $woman->barangay ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted fw-600">Purok</span>
                        <span class="fw-700 text-dark">{{ $woman->purok->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted fw-600">Account Approved At</span>
                        <span class="fw-700 text-dark">{{ $woman->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical History & Registrations -->
    <div class="col-lg-8">
        <!-- Pregnancies History -->
        <div class="card fade-in-card mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Pregnancy History ({{ $woman->pregnancies->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($woman->pregnancies->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead>
                                <tr>
                                    <th class="px-4">Pregnancy Period</th>
                                    <th>EDD / LMP</th>
                                    <th>G-T-P-A-L</th>
                                    <th>Delivery Location</th>
                                    <th>Risk Status</th>
                                    <th>Workflow</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($woman->pregnancies as $preg)
                                    <tr>
                                        <td class="px-4 fw-600 text-dark">
                                            LMP: {{ $preg->lmp ? $preg->lmp->format('M j, Y') : 'N/A' }}
                                            @if($preg->ended_at)
                                                <div style="font-size:0.7rem; color:var(--text-muted);">Ended: {{ $preg->ended_at->format('M j, Y') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-600 text-dark">EDD: {{ $preg->edd ? $preg->edd->format('M j, Y') : 'N/A' }}</span>
                                        </td>
                                        <td>
                                            G{{ $preg->gravida ?? 0 }} P{{ $preg->parity ?? 0 }} (T{{ $preg->term ?? 0 }} P{{ $preg->preterm ?? 0 }} A{{ $preg->abortion ?? 0 }} L{{ $preg->living ?? 0 }})
                                        </td>
                                        <td>
                                            <span class="fw-600 text-dark">{{ $preg->facility_delivery_place ? str_replace('_', ' ', ucfirst($preg->facility_delivery_place)) : 'Unrecorded' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $preg->is_high_risk ? 'danger' : 'success' }} text-white">
                                                {{ $preg->is_high_risk ? 'High Risk' : 'Normal' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-white">
                                                {{ ucfirst($preg->workflow_status ?? 'unknown') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-heartbreak text-muted" style="font-size:2rem;"></i>
                        <p class="text-muted text-xs mt-2 mb-0">No documented pregnancies found for this patient.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Checkups Records -->
        <div class="card fade-in-card mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Checkups History ({{ $woman->checkups->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($woman->checkups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead>
                                <tr>
                                    <th class="px-4">Date &amp; Time</th>
                                    <th>Midwife / BHW</th>
                                    <th>Blood Pressure</th>
                                    <th>Weight &amp; Height</th>
                                    <th>Fetal Heart Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($woman->checkups as $checkup)
                                    <tr>
                                        <td class="px-4 fw-600 text-dark">
                                            {{ $checkup->scheduled_date ? $checkup->scheduled_date->format('M j, Y') : 'N/A' }}
                                            <div style="font-size:0.7rem; color:var(--text-muted);">{{ $checkup->scheduled_time ?? 'N/A' }}</div>
                                        </td>
                                        <td>{{ $checkup->scheduledBy->name ?? 'Unknown' }}</td>
                                        <td class="fw-700 text-dark">{{ $checkup->blood_pressure ?? 'N/A' }}</td>
                                        <td>{{ $checkup->weight_kg ?? 'N/A' }} kg / {{ $checkup->height_cm ?? 'N/A' }} cm</td>
                                        <td>{{ $checkup->fetal_heart_rate ?? 'N/A' }} bpm</td>
                                        <td>
                                            <span class="badge bg-{{ $checkup->status === 'completed' ? 'success' : ($checkup->status === 'cancelled' ? 'danger' : 'warning') }} text-white">
                                                {{ ucfirst($checkup->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x text-muted" style="font-size:2rem;"></i>
                        <p class="text-muted text-xs mt-2 mb-0">No documented checkup appointments logged.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Clinical Health Records -->
        <div class="card fade-in-card">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Detailed Clinical Health Records
                </h5>
            </div>
            <div class="card-body p-0">
                @if($woman->healthRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead>
                                <tr>
                                    <th class="px-4">Record Date</th>
                                    <th>Fundal Height</th>
                                    <th>Blood Pressure</th>
                                    <th>Nutritional Status</th>
                                    <th>Risk Assessment</th>
                                    <th>Clinical Findings Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($woman->healthRecords as $record)
                                    <tr>
                                        <td class="px-4 fw-600 text-dark">
                                            {{ $record->record_date ? $record->record_date->format('M j, Y') : $record->created_at->format('M j, Y') }}
                                        </td>
                                        <td>{{ $record->fundal_height_cm ?? 'N/A' }} cm</td>
                                        <td class="fw-700 text-dark">{{ $record->blood_pressure ?? 'N/A' }}</td>
                                        <td>{{ $record->nutritional_status ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->is_high_risk ? 'danger' : 'success' }} text-white">
                                                {{ $record->is_high_risk ? 'High Risk' : 'Normal' }}
                                            </span>
                                        </td>
                                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $record->notes }}">
                                            {{ $record->notes ?? 'No comments' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-lock-fill text-muted" style="font-size:2rem;"></i>
                        <p class="text-muted text-xs mt-2 mb-0">No active physical health record sheets found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
