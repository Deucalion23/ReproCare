@extends('bhw.layout')

@section('title', 'View Report - BHW Portal')

@section('bhw-content')
<div class="py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $report->title }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-info text-dark">{{ $report->reportPeriod }}</span>
                <span class="badge bg-{{ $report->status === 'completed' ? 'success' : 'warning' }}">
                    {{ ucfirst($report->status) }}
                </span>
                @if($report->printed_at)
                    <span class="badge bg-secondary">
                        <i class="bi bi-printer"></i> Printed {{ $report->printed_at->format('M d, Y') }}
                    </span>
                @endif
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('bhw.reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('bhw.reports.print', $report->id) }}" class="btn btn-success" target="_blank">
                <i class="bi bi-printer"></i> Print Report
            </a>
        </div>
    </div>

    @if($report->description)
        <div class="alert alert-light border mb-4">
            <strong>Description:</strong> {{ $report->description }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Records</h6>
                            <h3 class="mb-0">{{ $report->total_records }}</h3>
                        </div>
                        <i class="bi bi-file-medical display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Unique Patients</h6>
                            <h3 class="mb-0">{{ $uniquePatients }}</h3>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-{{ $riskDistribution['high'] > 0 ? 'danger' : 'success' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">High Risk Cases</h6>
                            <h3 class="mb-0">{{ $riskDistribution['high'] }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Medium Risk</h6>
                            <h3 class="mb-0">{{ $riskDistribution['medium'] }}</h3>
                        </div>
                        <i class="bi bi-flag display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Distribution Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Risk Level Distribution</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width:12px; height:12px;"></div>
                        <span class="me-auto">Low Risk</span>
                        <span class="badge bg-success">{{ $riskDistribution['low'] }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        @php
                            $total = array_sum($riskDistribution);
                            $lowPct = $total > 0 ? ($riskDistribution['low'] / $total) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width:{{ $lowPct }}%"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning rounded-circle me-2" style="width:12px; height:12px;"></div>
                        <span class="me-auto">Medium Risk</span>
                        <span class="badge bg-warning text-dark">{{ $riskDistribution['medium'] }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        @php
                            $medPct = $total > 0 ? ($riskDistribution['medium'] / $total) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-warning" style="width:{{ $medPct }}%"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-danger rounded-circle me-2" style="width:12px; height:12px;"></div>
                        <span class="me-auto">High Risk</span>
                        <span class="badge bg-danger">{{ $riskDistribution['high'] }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        @php
                            $highPct = $total > 0 ? ($riskDistribution['high'] / $total) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-danger" style="width:{{ $highPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Records Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Health Records</h5>
            <span class="text-muted">{{ $healthRecords->total() }} records found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Blood Pressure</th>
                            <th>Weight</th>
                            <th>Heart Rate</th>
                            <th>Temp</th>
                            <th>Risk Level</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($healthRecords as $record)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $record->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $record->created_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                        <div class="fw-medium">{{ optional($record->woman)->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ optional($record->woman)->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $record->bp }}
                                    </span>
                                </td>
                                <td>{{ $record->weight }} kg</td>
                                <td>{{ $record->heart_rate }} bpm</td>
                                <td>{{ $record->temperature }}°C</td>
                                <td>
                                    @if($record->risk_level === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($record->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px; font-size:12px;">
                                            {{ strtoupper(substr(optional($record->recordedBy)->name ?? 'BHW', 0, 2)) }}
                                        </div>
                                        <small>{{ optional($record->recordedBy)->name ?? 'BHW' }}</small>
                                    </div>
                                </td>
                            </tr>
                            @if($record->notes)
                                <tr class="table-light">
                                    <td colspan="8" class="py-2">
                                        <small class="text-muted">
                                            <strong>Notes:</strong> {{ $record->notes }}
                                        </small>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No health records found for this report period</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($healthRecords->hasPages())
            <div class="card-footer">
                {{ $healthRecords->links() }}
            </div>
        @endif
    </div>

    <!-- Footer Actions -->
    <div class="d-flex justify-content-between mt-4">
        <x-archive-form :action="route('bhw.reports.destroy', $report->id)" label="Archive Report" title="Archive report (retained for audit)" btnClass="btn btn-outline-warning" icon="bi bi-archive" confirmText="Archive this report? It will be retained for audit and can be restored." />
        <a href="{{ route('bhw.reports.print', $report->id) }}" class="btn btn-success" target="_blank">
            <i class="bi bi-printer"></i> Print Report
        </a>
    </div>
</div>
@endsection
