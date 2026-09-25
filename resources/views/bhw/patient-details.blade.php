@extends('bhw.layout')

@section('title', 'Woman Details - ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
            <div class="d-flex align-items-center gap-3">
                <x-patient-avatar :patient="$woman" :size="64" />
                <div>
                    <div class="page-hero-title">{{ $woman->name }}</div>
                    <p class="page-hero-subtitle">{{ $woman->email }} | {{ $woman->barangay ?? 'Barangay not set' }}</p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('bhw.checkups.create', $woman->id) }}" class="btn btn-light">
                    <i class="bi bi-calendar-plus me-1"></i> Schedule Checkup
                </a>
                <a href="{{ route('bhw.health-records.create', [$woman->id]) }}" class="btn btn-light">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Add Health Record
                </a>
                <a href="{{ route('bhw.patients') }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Women
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('includes.risk-alert-status')

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-cyan fade-in-card">
                <i class="bi bi-clipboard-data-fill stat-icon"></i>
                <div class="stat-label">Health Records</div>
                <div class="stat-number">{{ $healthRecords->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-green fade-in-card">
                <i class="bi bi-calendar-check-fill stat-icon"></i>
                <div class="stat-label">Checkups</div>
                <div class="stat-number">{{ $checkups->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-amber fade-in-card">
                <i class="bi bi-heart-pulse-fill stat-icon"></i>
                <div class="stat-label">Account Status</div>
                <div class="stat-number" style="font-size:1.3rem;">{{ ucfirst($woman->status ?? 'active') }}</div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Profile Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Full Name</strong><div class="text-muted mt-1">{{ $woman->name }}</div></div>
                <div class="col-md-4"><strong>Email</strong><div class="text-muted mt-1">{{ $woman->email }}</div></div>
                <div class="col-md-4"><strong>Barangay</strong><div class="text-muted mt-1">{{ $woman->barangay ?? 'Not set' }}</div></div>
                <div class="col-md-4"><strong>Contact Number</strong><div class="text-muted mt-1">{{ $woman->contact_number ?? $woman->phone ?? 'Not provided' }}</div></div>
                <div class="col-md-4"><strong>Member Since</strong><div class="text-muted mt-1">{{ $woman->created_at?->format('M j, Y') ?? 'Unknown' }}</div></div>
                <div class="col-md-4"><strong>Purok</strong><div class="text-muted mt-1">{{ $woman->purok?->name ?? 'Not assigned' }}</div></div>
            </div>
        </div>
    </div>

    @if($woman->partner_name || $woman->partner_contact)
    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Partner / Spouse Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><strong>Partner Name</strong><div class="text-muted mt-1">{{ $woman->partner_name ?? 'Not provided' }}</div></div>
                <div class="col-md-6"><strong>Partner Contact</strong><div class="text-muted mt-1">{{ $woman->partner_contact ?? 'Not provided' }}</div></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Emergency Contacts --}}
    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Emergency Contact Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Primary Contact --}}
                <div class="col-md-6">
                    <div class="p-3 h-100" style="background:color-mix(in srgb, var(--color-surface) 1%, transparent); border:1px solid var(--border); border-radius:12px;">
                        <h6 class="mb-3 fw-700 text-primary">Primary Contact
                        </h6>
                        @if($woman->primaryEmergencyContact)
                            <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Name</small>
                                    <span class="fw-600">{{ $woman->primaryEmergencyContact->name }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Relationship</small>
                                    <span>{{ $woman->primaryEmergencyContact->relationship }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Contact Number</small>
                                    <a href="tel:{{ $woman->primaryEmergencyContact->contact_number }}" class="fw-500 text-decoration-none">
                                        <i class="bi bi-telephone-fill me-1" style="font-size:0.8rem;"></i>{{ $woman->primaryEmergencyContact->contact_number }}
                                    </a>
                                </div>
                                @if($woman->primaryEmergencyContact->address)
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Address</small>
                                    <span class="text-muted">{{ $woman->primaryEmergencyContact->address }}</span>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-muted py-2" style="font-size:0.875rem;">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> No primary emergency contact recorded.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Secondary Contact --}}
                <div class="col-md-6">
                    <div class="p-3 h-100" style="background:color-mix(in srgb, var(--color-surface) 1%, transparent); border:1px solid var(--border); border-radius:12px;">
                        <h6 class="mb-3 fw-700 text-muted">Secondary Contact (Optional)
                        </h6>
                        @if($woman->secondaryEmergencyContact)
                            <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Name</small>
                                    <span class="fw-600">{{ $woman->secondaryEmergencyContact->name }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Relationship</small>
                                    <span>{{ $woman->secondaryEmergencyContact->relationship }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Contact Number</small>
                                    <a href="tel:{{ $woman->secondaryEmergencyContact->contact_number }}" class="fw-500 text-decoration-none text-muted">
                                        <i class="bi bi-telephone-fill me-1" style="font-size:0.8rem;"></i>{{ $woman->secondaryEmergencyContact->contact_number }}
                                    </a>
                                </div>
                                @if($woman->secondaryEmergencyContact->address)
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Address</small>
                                    <span class="text-muted">{{ $woman->secondaryEmergencyContact->address }}</span>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-muted py-2" style="font-size:0.875rem;">
                                <i class="bi bi-info-circle me-1"></i> No secondary emergency contact recorded.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3 mb-4 border-0" style="background:color-mix(in srgb, var(--color-surface) 1%, transparent); border:1px solid var(--border); border-radius:12px;">
                    <h5 class="mb-0">Tertiary Emergency Contact (Optional)</h5>
                    <div class="card-body">
                        @if($woman->tertiaryEmergencyContact)
                            <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Name</small>
                                    <span class="fw-600">{{ $woman->tertiaryEmergencyContact->name }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Relationship</small>
                                    <span>{{ $woman->tertiaryEmergencyContact->relationship }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Contact Number</small>
                                    <a href="tel:{{ $woman->tertiaryEmergencyContact->contact_number }}" class="fw-500 text-decoration-none text-muted">
                                        <i class="bi bi-telephone-fill me-1" style="font-size:0.8rem;"></i>{{ $woman->tertiaryEmergencyContact->contact_number }}
                                    </a>
                                </div>
                                @if($woman->tertiaryEmergencyContact->address)
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Address</small>
                                    <span class="text-muted">{{ $woman->tertiaryEmergencyContact->address }}</span>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-muted py-2" style="font-size:0.875rem;">
                                <i class="bi bi-info-circle me-1"></i> No tertiary emergency contact recorded.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Health Records</h5>
        </div>
        <div class="card-body p-0">
            @if($healthRecords->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>BP</th>
                                <th>Weight</th>
                                <th>Heart Rate</th>
                                <th>Temperature</th>
                                <th>Risk</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($healthRecords as $record)
                                <tr>
                                    <td>{{ $record->created_at->format('M j, Y g:i A') }}</td>
                                    <td>{{ $record->bp }}</td>
                                    <td>{{ $record->weight }} kg</td>
                                    <td>{{ $record->heart_rate }} bpm</td>
                                    <td>{{ $record->temperature }} C</td>
                                    <td>
                                        <span class="badge {{ $record->risk_level === 'High' ? 'bg-danger' : ($record->risk_level === 'Medium' ? 'bg-warning text-dark' : 'bg-success') }}">
                                            {{ $record->risk_level }}
                                        </span>
                                    </td>
                                    <td>{{ $record->recordedBy?->name ?? $record->recordedByUser?->name ?? 'System' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-clipboard2-x empty-state-icon"></i>
                    <h6>No health records yet</h6>
                    <p>This woman does not have any recorded vital signs yet.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-header">
            <h5 class="mb-0">Checkups</h5>
        </div>
        <div class="card-body p-0">
            @if($checkups->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Purpose</th>
                                <th>Notes</th>
                                <th>Scheduled By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkups as $checkup)
                                <tr>
                                    <td>{{ $checkup->scheduled_date->format('M j, Y') }}</td>
                                    <td>
                                        <span class="badge {{ strtolower($checkup->status) === 'completed' ? 'bg-success' : (strtolower($checkup->status) === 'missed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ $checkup->status }}
                                        </span>
                                    </td>
                                    <td>{{ $checkup->purpose ?: 'General prenatal checkup' }}</td>
                                    <td>{{ $checkup->notes ?: 'No notes' }}</td>
                                    <td>{{ $checkup->midwife?->name ?? $checkup->scheduledByBhw?->name ?? 'BHW' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-calendar2-x empty-state-icon"></i>
                    <h6>No checkups yet</h6>
                    <p>No scheduled or completed checkups have been saved for this woman.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
