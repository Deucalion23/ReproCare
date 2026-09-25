@extends('bhw.layout')

@section('title', 'Needs Revision - BHW Portal')

@section('bhw-content')
<div class="container-fluid">
    <h4 class="mb-1">Draft / Needs Revision</h4>
    <p class="text-muted">Items sent back by your BHW President or Midwife. Reviewer notes are highlighted — fix and resubmit.</p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <h5 class="mt-4">Health Records ({{ $records->count() }})</h5>
    @forelse ($records as $record)
        <div class="card mb-2 border-warning">
            <div class="card-body">
                <strong>{{ $record->patient_name }}</strong>
                <span class="badge bg-warning text-dark ms-2">{{ $record->workflow_status }}</span>
                @if ($record->reviewer_note)
                    <div class="alert alert-warning mt-2 mb-2">📝 Reviewer note: {{ $record->reviewer_note }}</div>
                @endif
                <form method="POST" action="{{ route('workflow.health-records.resubmit', $record->id) }}" class="row g-2 mt-1">
                    @csrf
                    <div class="col-md-2"><input name="bp_systolic" type="number" class="form-control" placeholder="Sys" min="50" max="300"></div>
                    <div class="col-md-2"><input name="bp_diastolic" type="number" class="form-control" placeholder="Dia" min="30" max="200"></div>
                    <div class="col-md-2"><input name="weight" type="number" step="0.1" class="form-control" placeholder="Wt kg"></div>
                    <div class="col-md-2"><input name="heart_rate" type="number" class="form-control" placeholder="HR"></div>
                    <div class="col-md-2"><input name="temperature" type="number" step="0.1" class="form-control" placeholder="Temp"></div>
                    <div class="col-md-12"><input name="notes" class="form-control" placeholder="Correction notes (optional)"></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm">Fix & Resubmit</button></div>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No health records need revision. 🎉</p>
    @endforelse

    <h5 class="mt-4">Monthly Reports ({{ $reports->count() }})</h5>
    @forelse ($reports as $report)
        <div class="card mb-2 border-warning">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>{{ $report->title }}</strong>
                    <span class="badge bg-warning text-dark ms-2">{{ $report->submission_status }}</span>
                    @if ($report->reviewer_note)
                        <div class="alert alert-warning mt-2 mb-0">📝 Reviewer note: {{ $report->reviewer_note }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('workflow.reports.resubmit', $report->id) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm">Resubmit</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No reports need revision. 🎉</p>
    @endforelse
</div>
@endsection
