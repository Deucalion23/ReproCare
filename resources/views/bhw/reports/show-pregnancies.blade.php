@extends('bhw.layout')

@section('title', 'View Pregnancy Report - BHW Portal')

@section('bhw-content')
<div class="py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $report->title }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-info text-dark">{{ $report->reportPeriod }}</span>
                <span class="badge bg-secondary">Pregnancies</span>
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
                            <h6 class="mb-0">Total Pregnancies</h6>
                            <h3 class="mb-0">{{ $report->total_records }}</h3>
                        </div>
                        <i class="bi bi-heart display-4 opacity-50"></i>
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
                            <h6 class="mb-0">High Risk</h6>
                            <h3 class="mb-0">{{ $riskDistribution['high'] }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Low Risk</h6>
                            <h3 class="mb-0">{{ $riskDistribution['low'] }}</h3>
                        </div>
                        <i class="bi bi-shield-check display-4 opacity-50"></i>
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
                <div class="col-md-6">
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
                <div class="col-md-6">
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

    <!-- Pregnancies Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Pregnancies</h5>
            <span class="text-muted">{{ $pregnancies->total() }} pregnancies found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date Recorded</th>
                            <th>Patient</th>
                            <th>LMP</th>
                            <th>EDD</th>
                            <th>AOG</th>
                            <th>Trimester</th>
                            <th>Risk Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pregnancies as $pregnancy)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $pregnancy->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $pregnancy->created_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ optional($pregnancy->woman)->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ optional($pregnancy->woman)->email ?? '' }}</small>
                                </td>
                                <td>{{ $pregnancy->lmp ? $pregnancy->lmp->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $pregnancy->formatted_aog ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $pregnancy->trimester_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($pregnancy->is_high_risk)
                                        <span class="badge bg-danger">High Risk</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @if($pregnancy->notes)
                                <tr class="table-light">
                                    <td colspan="7" class="py-2">
                                        <small class="text-muted">
                                            <strong>Notes:</strong> {{ $pregnancy->notes }}
                                        </small>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No pregnancies found for this report period</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pregnancies->hasPages())
            <div class="card-footer">
                {{ $pregnancies->links() }}
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
