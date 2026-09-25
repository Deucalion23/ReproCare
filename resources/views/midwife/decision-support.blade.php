@extends('midwife.layout')
@section('title', 'Decision Support | ReproCare')
@section('midwife-content')
<div class="page-hero mb-4">
    <h1 class="page-hero-title">Decision Support</h1>
    <p class="page-hero-subtitle">Choose a patient to review recorded risks and suggested follow-up.</p>
</div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">How to use it</h2>
    <ol class="mb-0">
        <li>Choose registered or walk-in patients, then search by first or last name.</li>
        <li>Select <strong>View suggestions</strong> to open the patient's decision support panel.</li>
        <li>Review the reasons, appointment gaps and existing care plan before taking action.</li>
    </ol>
    <p class="small text-muted mt-3 mb-0">Suggestions use saved records and local rules. They do not automatically change records or send reminders. Patients without an eligible open pregnancy show an explanation.</p>
</div></div>
<div class="card"><div class="card-body">
    <form method="get" action="{{ route('midwife.decision-support') }}" class="row g-3 mb-4">
        <div class="col-md-3"><label class="form-label" for="support-type">Patient type</label><select class="form-select" name="type" id="support-type"><option value="registered" @selected($type === 'registered')>Registered</option><option value="walk-in" @selected($type === 'walk-in')>Walk-in</option></select></div>
        <div class="col-md-6"><label class="form-label" for="support-search">First or last name</label><input class="form-control" name="search" id="support-search" value="{{ $search }}" maxlength="100"></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary" type="submit">Find patient</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Patient</th><th>Barangay</th><th>Decision support</th></tr></thead>
        <tbody>@forelse($patients as $patient)
            <tr><td>{{ $patient->first_name }} {{ $patient->last_name }}</td><td>{{ $patient->barangay ?: 'Not recorded' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route($type === 'walk-in' ? 'midwife.walk-in-patients.show' : 'midwife.patient-details', $patient->id) }}#decision-support">View suggestions<span class="visually-hidden"> for {{ $patient->first_name }} {{ $patient->last_name }}</span></a></td></tr>
        @empty<tr><td colspan="3">No patients match your search.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $patients->links() }}
</div></div>
@endsection
