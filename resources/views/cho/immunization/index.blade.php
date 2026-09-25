@extends('cho.layout')

@section('title', 'Immunization Records - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div>
        <div class="page-hero-title">Immunization Records</div>
        <p class="page-hero-subtitle">Health records with documented immunization status, city-wide.</p>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.immunization.index') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search patient name...">
            </div>
            <div class="col-md-5">
                <input type="text" name="barangay" class="form-control" value="{{ $barangay }}" placeholder="Filter by barangay...">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="px-4">Patient</th><th>Barangay</th><th>Immunization Status</th><th>Date</th><th>Recorded By</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $record->patient_name }}</td>
                            <td>{{ $record->patient_barangay ?? '—' }}</td>
                            <td>{{ $record->immunization_status ?? '—' }}</td>
                            <td>{{ optional($record->created_at)->format('M j, Y') }}</td>
                            <td>{{ $record->recordedBy?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No immunization records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-3 border-top d-flex justify-content-center">{{ $records->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
