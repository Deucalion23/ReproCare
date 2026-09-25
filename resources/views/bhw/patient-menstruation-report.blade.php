@extends('bhw.layout')

@section('title', 'Patient Menstrual Cycle Report - ReproCare')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Patient Menstrual Cycle Report</h1>
        <div>
            <a href="{{ route('bhw.patient-menstruation', $woman->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
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
                    <div class="mb-3">
                        <strong>Phone:</strong>
                        <p class="mb-0">{{ $woman->phone ?? 'Not specified' }}</p>
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
                    <div class="mb-3">
                        <strong>Report Generated:</strong>
                        <p class="mb-0">{{ now()->format('F j, Y - g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
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

    <!-- Cycle History with Predictions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Cycle History & Predictions</h5>
        </div>
        <div class="card-body">
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Period Start</th>
                                <th>Period End</th>
                                <th>Duration</th>
                                <th>Predicted Ovulation</th>
                                <th>Fertile Window</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $index => $record)
                                <tr>
                                    <td>{{ $record->period_start_date->format('M d, Y') }}</td>
                                    <td>{{ $record->period_end_date ? $record->period_end_date->format('M d, Y') : 'Ongoing' }}</td></td>
                                    <td>{{ $record->period_length ?? '-' }} {{ $record->period_length ? ($record->period_length != 1 ? 'days' : 'day') : '' }}</td>
                                    <td>
                                        @isset($predictions[$index]['ovulation'])
                                            {{ $predictions[$index]['ovulation']->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @isset($predictions[$index]['fertile_window'])
                                            {{ $predictions[$index]['fertile_window']['start']->format('M d') }} – 
                                            {{ $predictions[$index]['fertile_window']['end']->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
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

    <!-- Summary Notes -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Summary Notes</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Pattern Analysis:</strong>
                <p class="mb-0">
                    @if($averageCycle)
                        Patient has an average cycle length of {{ $averageCycle }} days, which is
                        @if($averageCycle >= 21 && $averageCycle <= 35)
                            within the normal range (21-35 days).
                        @else
                            outside the normal range (21-35 days). May require medical attention.
                        @endif
                    @else
                        Insufficient data to determine cycle pattern.
                    @endif
                </p>
            </div>
            <div class="mb-3">
                <strong>Recommendations:</strong>
                <p class="mb-0">
                    @if($records->count() >= 3)
                        Patient has sufficient data for accurate predictions. Continue regular tracking.
                    @else
                        Patient needs to log more cycles for accurate predictions. Encourage consistent tracking.
                    @endif
                </p>
            </div>
            <div class="mb-3">
                <strong>BHW Notes:</strong>
                <textarea class="form-control" rows="3" placeholder="Add notes for this patient..."></textarea>
            </div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Generated by:</strong> {{ auth()->user()->name }}</p>
                    <p class="mb-1"><strong>Role:</strong> Barangay Health Worker</p>
                    <p class="mb-0"><strong>Date:</strong> {{ now()->format('F j, Y') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mb-3">
                        <p class="mb-0">_________________________</p>
                        <p class="mb-0"><strong>BHW Signature</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display:none !important; }
    .card { border:1px solid var(--color-border); page-break-inside:avoid; }
    .card-header { background-color:var(--color-surface-soft) !important; }
    .btn { display:none !important; }
    .container-fluid { padding:0 !important; }
}
</style>
@endsection
