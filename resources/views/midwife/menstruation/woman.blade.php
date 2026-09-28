@extends('midwife.layout')

@section('title', 'Patient Cycle History - ReproCare')

@section('midwife-content')
<div class="py-4">
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Patient Cycle History</div>
            <p class="page-hero-subtitle">Cycle history for this patient.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('midwife.menstruation.create', $woman->id) }}" class="btn btn-primary"><i class="bi bi-calendar-plus"></i> Add Record</a>
            <a href="{{ route('midwife.menstruation.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> All Records</a>
        </div>
    </div>
</div>

    <div class="card shadow">
        <div class="card-body">
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr><th>Start Date</th><th>End Date</th><th>Duration</th><th>Notes</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>{{ optional($record->period_start_date)->format('M j, Y') ?? '—' }}</td>
                                    <td>{{ $record->period_end_date ? $record->period_end_date->format('M j, Y') : 'Ongoing' }}</td>
                                    <td>{{ $record->period_end_date && $record->period_start_date ? $record->period_start_date->diffInDays($record->period_end_date) . ' days' : '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($record->notes ?? '', 40) }}</td>
                                    <td>
                                        <div class="d-flex gap-1 tbl-actions">
                                            <a href="{{ route('midwife.menstruation.show', $record->id) }}" class="btn btn-sm btn-view" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('midwife.menstruation.edit', $record->id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <x-archive-form :action="route('midwife.menstruation.destroy', $record->id)" label="" title="Archive record (retained for audit)" btnClass="btn btn-sm btn-warning text-white" icon="bi bi-archive" confirmText="Archive this cycle record? It will be retained for audit." />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4 mb-0">No cycle records for this patient yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
