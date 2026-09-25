@extends('bhw.layout')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">Walk-in Patients</h2>
        <a href="{{ route('bhw.walk-in-patients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Record Walk-in
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($patients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Contact</th>
                                <th>Barangay</th>
                                <th>Reason for Visit</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                <tr>
                                    <td>{{ $patient->full_name }}</td>
                                    <td>{{ $patient->age ?? '—' }}</td>
                                    <td>{{ $patient->contact_number ?? '—' }}</td>
                                    <td>{{ $patient->barangay ?? '—' }}</td>
                                    <td>{{ $patient->reason_for_visit ?? '—' }}</td>
                                    <td>
                                        @if($patient->isPortalActive())
                                            <span class="badge bg-success" title="Authenticated Patient · Direct Access">Enrolled · Portal-Active</span>
                                        @else
                                            <span class="badge bg-secondary" title="Managed Beneficiary · Field Record Only">Unlinked · BHW-Managed</span>
                                        @endif
                                    </td>
                                    <td>{{ $patient->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('bhw.walk-in-patients.show', $patient->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $patients->links() }}
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No walk-in patients recorded.</p>
                    <a href="{{ route('bhw.walk-in-patients.create') }}" class="btn btn-primary">
                        Record First Walk-in Patient
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
