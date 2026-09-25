@extends('midwife.layout')

@section('title', 'Pregnancy History - ReproCare')

@section('midwife-content')
<div class="py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Pregnancy History</h1>
            <p class="text-muted mb-0">{{ $woman->name }}</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('midwife.pregnant-patients') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
            <a href="{{ route('midwife.patient-details', $woman->id) }}" class="btn btn-primary">
                <i class="bi bi-person me-2"></i>View Woman Profile
            </a>
        </div>
    </div>

    <!-- Current Pregnancy Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Current Pregnancy Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <h6 class="text-muted">Last Menstrual Period</h6>
                                <h4 class="text-primary">{{ $pregnancy->lmp ? $pregnancy->lmp->format('F d, Y') : 'Not recorded' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <h6 class="text-muted">Estimated Due Date</h6>
                                <h4 class="text-primary">{{ $pregnancy->edd ? $pregnancy->edd->format('F d, Y') : 'Not calculated' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <h6 class="text-muted">Age of Gestation</h6>
                                <h4 class="text-primary">{{ $pregnancy->formatted_aog ?? 'N/A' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <h6 class="text-muted">Gravida/Para</h6>
                                <h4 class="text-primary">G{{ $pregnancy->gravida }}/P{{ $pregnancy->para }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    @if($pregnancy->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-muted">Notes:</h6>
                                <p class="mb-0">{{ $pregnancy->notes }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Status:</strong> 
                                @if($pregnancy->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Completed</span>
                                @endif
                            </p>
                            <p class="mb-1"><strong>Risk Level:</strong> 
                                @if($pregnancy->is_high_risk)
                                    <span class="badge bg-danger">High Risk</span>
                                @else
                                    <span class="badge bg-success">Normal</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Created:</strong> {{ $pregnancy->created_at->format('M d, Y') }}</p>
                            <p class="mb-0"><strong>Last Updated:</strong> {{ $pregnancy->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkups History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Checkups History</h5>
                </div>
                <div class="card-body">
                    @if($pregnancy->checkups && $pregnancy->checkups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Weight</th>
                                        <th>Blood Pressure</th>
                                        <th>Fetal Heartbeat</th>
                                        <th>Status</th>
                                        <th>Midwife</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pregnancy->checkups as $checkup)
                                        <tr>
                                            <td>{{ $checkup->scheduled_date ? $checkup->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $checkup->type ?? 'Regular' }}</td>
                                            <td>{{ $checkup->weight ? $checkup->weight . ' kg' : 'N/A' }}</td>
                                            <td>{{ $checkup->blood_pressure ?? 'N/A' }}</td>
                                            <td>{{ $checkup->fetal_heartbeat ?? 'N/A' }}</td>
                                            <td>
                                                @if($checkup->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($checkup->status === 'missed')
                                                    <span class="badge bg-danger">Missed</span>
                                                @elseif($checkup->status === 'scheduled')
                                                    <span class="badge bg-info">Scheduled</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($checkup->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $checkup->midwife ? $checkup->midwife->name : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('midwife.checkups.show', $checkup->id) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size:2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No checkups recorded for this pregnancy.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pregnancy History Timeline -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Pregnancies Timeline</h5>
                </div>
                <div class="card-body">
                    @if($pregnancyHistory->count() > 0)
                        <div class="timeline">
                            @foreach($pregnancyHistory as $index => $historyPregnancy)
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle {{ $historyPregnancy->id === $pregnancy->id ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width:50px; height:50px; font-size:1.5rem;">
                                            {{ $pregnancyHistory->count() - $index }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card {{ $historyPregnancy->id === $pregnancy->id ? 'border-primary' : '' }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="card-title">
                                                            Pregnancy #{{ $pregnancyHistory->count() - $index }}
                                                            @if($historyPregnancy->id === $pregnancy->id)
                                                                <span class="badge bg-primary ms-2">Current</span>
                                                            @endif
                                                        </h5>
                                                        <p class="card-text">
                                                            <strong>Period:</strong> {{ $historyPregnancy->lmp ? $historyPregnancy->lmp->format('M d, Y') : 'N/A' }} - {{ $historyPregnancy->ended_at ? $historyPregnancy->ended_at->format('M d, Y') : ($historyPregnancy->is_active ? 'Present' : 'N/A') }}<br>
                                                            <strong>AOG:</strong> {{ $historyPregnancy->formatted_aog ?? 'N/A' }} | 
                                                            <strong>G/P:</strong> G{{ $historyPregnancy->gravida }}/P{{ $historyPregnancy->para }} | 
                                                            <strong>Checkups:</strong> {{ $historyPregnancy->checkups ? $historyPregnancy->checkups->count() : 0 }}
                                                        </p>
                                                        @if($historyPregnancy->notes)
                                                            <p class="card-text"><strong>Notes:</strong> {{ $historyPregnancy->notes }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="btn-group">
                                                        @if($historyPregnancy->id !== $pregnancy->id)
                                                            <a href="{{ route('midwife.pregnancy-history', $historyPregnancy->id) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('midwife.pregnancies.edit', $historyPregnancy->id) }}" class="btn btn-sm btn-outline-warning">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="badge {{ $historyPregnancy->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $historyPregnancy->is_active ? 'Active' : 'Completed' }}
                                                    </span>
                                                    @if($historyPregnancy->is_high_risk)
                                                        <span class="badge bg-danger ms-2">High Risk</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size:2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No pregnancy history available.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
