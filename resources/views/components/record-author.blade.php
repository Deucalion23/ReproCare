@props(['record', 'barangay' => null])
@if($record->recordedBy)
    {{ $record->recordedBy->name }}
@else
    <span class="text-muted">Recorder not recorded</span>
    @php($workers = app(\App\Services\PatientPresentation::class)->barangayWorkers($barangay))
    <small class="d-block">{{ $workers->isNotEmpty() ? 'Barangay BHW: '.$workers->pluck('name')->implode(', ') : 'No BHW assigned to this barangay' }}</small>
@endif
