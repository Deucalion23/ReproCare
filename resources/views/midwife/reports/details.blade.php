@extends('midwife.layout')

@section('title', 'Patient Details - Reports')

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Patient Report Details</div>
                <p class="page-hero-subtitle">A complete snapshot of the patient's maternal history, checkups, and recorded health metrics.</p>
            </div>
            <div class="workspace-toolbar-actions">
                <a href="{{ route('midwife.reports.index') }}" class="btn-hero-secondary"><i class="bi bi-arrow-left"></i>Back to Reports</a>
                <a href="{{ route('midwife.patient-details', $woman->id) }}" class="btn-hero-primary"><i class="bi bi-person"></i>Full Profile</a>
            </div>
        </div>
    </div>

    <div class="info-strip">
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-person-circle"></i>Patient</div>
            <div class="info-pill-value">{{ $woman->name }}</div>
        </div>
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-heart-pulse"></i>Status</div>
            <div class="info-pill-value">{{ $woman->pregnancy_status ?? 'Not specified' }}</div>
        </div>
        <div class="info-pill-card fade-in-card">
            <div class="info-pill-label"><i class="bi bi-person-badge"></i>Assigned BHW</div>
            <div class="info-pill-value">{{ $woman->assigned_bhw?->name ?? 'None' }}</div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Patient Information</h2>
        </div>
        <div class="workspace-panel-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="table-meta-stack">
                        <div><strong>Name:</strong> {{ $woman->name }}</div>
                        <div><strong>Email:</strong> {{ $woman->email }}</div>
                        <div><strong>Age:</strong> {{ $woman->age }}</div>
                        <div><strong>Status:</strong> {{ $woman->pregnancy_status ?? 'Not specified' }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="table-meta-stack">
                        <div><strong>Address:</strong> {{ $woman->address ?? 'N/A' }}</div>
                        <div><strong>Barangay:</strong> {{ $woman->barangay ?? 'N/A' }}</div>
                        <div><strong>Assigned BHW:</strong> {{ $woman->assigned_bhw?->name ?? 'None' }}</div>
                        <div><strong>Last Checkup:</strong> {{ $woman->last_checkup ? $woman->last_checkup->scheduled_date->format('M d, Y') : 'None' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($woman->pregnancies->count() > 0)
        <div class="workspace-panel fade-in-card">
            <div class="workspace-panel-header">
                <h2 class="workspace-panel-title">Pregnancy History</h2>
            </div>
            <div class="workspace-panel-body pt-3">
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>LMP</th>
                                <th>EDD</th>
                                <th>AOG</th>
                                <th>Risk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($woman->pregnancies as $pregnancy)
                                <tr>
                                    <td>{{ $pregnancy->lmp ? $pregnancy->lmp->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $pregnancy->formatted_aog }}</td>
                                    <td>
                                        <span class="summary-chip {{ $pregnancy->is_high_risk ? 'chip-danger' : 'chip-success' }}">
                                            {{ $pregnancy->is_high_risk ? 'High Risk' : 'Normal' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="summary-chip {{ $pregnancy->is_active ? 'chip-info' : 'chip-warning' }}">
                                            {{ $pregnancy->is_active ? 'Active' : 'Completed' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($woman->checkups->count() > 0)
        <div class="workspace-panel fade-in-card">
            <div class="workspace-panel-header">
                <h2 class="workspace-panel-title">Checkups</h2>
            </div>
            <div class="workspace-panel-body pt-3">
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Purpose</th>
                                <th>Midwife</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($woman->checkups as $checkup)
                                <tr>
                                    <td>{{ $checkup->scheduled_date->format('M d, Y') }}</td>
                                    <td>{{ $checkup->purpose }}</td>
                                    <td>{{ $checkup->midwife?->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $checkupChip = match($checkup->status) {
                                                'Completed' => 'chip-success',
                                                'Missed' => 'chip-danger',
                                                default => 'chip-info',
                                            };
                                        @endphp
                                        <span class="summary-chip {{ $checkupChip }}">{{ $checkup->status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($woman->healthRecords->count() > 0)
        <div class="workspace-panel fade-in-card">
            <div class="workspace-panel-header">
                <h2 class="workspace-panel-title">Health Records</h2>
            </div>
            <div class="workspace-panel-body pt-3">
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>BP</th>
                                <th>Weight</th>
                                <th>Heart Rate</th>
                                <th>Temp</th>
                                <th>Risk</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($woman->healthRecords as $record)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $record->created_at->format('M d, Y') }}</div>
                                        @if($record->notes)
                                            <div class="table-subtitle mt-2">{{ $record->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $record->bp }}</td>
                                    <td>{{ $record->weight }} kg</td>
                                    <td>{{ $record->heart_rate }} bpm</td>
                                    <td>{{ $record->temperature }} C</td>
                                    <td>
                                        @php
                                            $riskChip = match($record->risk_level) {
                                                'High' => 'chip-danger',
                                                'Medium' => 'chip-warning',
                                                default => 'chip-success',
                                            };
                                        @endphp
                                        <span class="summary-chip {{ $riskChip }}">{{ $record->risk_level }}</span>
                                    </td>
                                    <td>{{ $record->recordedBy?->name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
