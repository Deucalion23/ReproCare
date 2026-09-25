@extends('bhw.layout')

@section('title', 'Patient Menstrual Cycle - ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Patient Menstrual Cycle</h1>
        <a href="{{ route('bhw.patient-details', $woman->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Patient Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <p class="mb-0">{{ $woman->name }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $woman->email }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Address:</strong>
                        <p class="mb-0">{{ $woman->address ?? 'Not specified' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Barangay:</strong>
                        <p class="mb-0">{{ $woman->barangay ?? 'Not assigned' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    @if($averageCycle || $averagePeriod)
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 style="color:var(--primary-light);">{{ $averageCycle ?? 'N/A' }}</h3>
                    <p class="mb-0">Average Cycle Length (days)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 style="color:var(--color-danger-text);">{{ $averagePeriod ?? 'N/A' }}</h3>
                    <p class="mb-0">Average Period Duration (days)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 style="color:var(--color-success-text);">{{ $records->count() }}</h3>
                    <p class="mb-0">Total Cycles Logged</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('bhw.patient-menstruation-report', $woman->id) }}" class="btn btn-primary w-100">
                <i class="bi bi-file-earmark-text me-2"></i> Generate Report
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('bhw.patient-menstruation-export', $woman->id) }}" class="btn btn-outline-success w-100">
                <i class="bi bi-file-earmark-excel me-2"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Menstrual Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Menstrual Records ({{ $records->count() }})</h5>
        </div>
        <div class="card-body">
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>{{ $record->period_start_date->format('M d, Y') }}</td>
                                    <td>{{ $record->period_end_date ? $record->period_end_date->format('M d, Y') : 'Ongoing' }}</td></td>
                                    <td>{{ $record->period_length ?? '-' }} {{ $record->period_length ? ($record->period_length != 1 ? 'days' : 'day') : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size:3rem;"></i>
                    <h5 class="text-muted mt-3">No Menstrual Records Found</h5>
                    <p class="text-muted">This patient has not logged any menstrual cycle data yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
