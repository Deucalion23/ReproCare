@if($notification->parent_notification_id && $notification->patientAlert)
    @php $patientAlert = $notification->patientAlert; @endphp
    <div class="small mt-1">
        <strong>Patient:</strong>
        @if($patientAlert->resolved_at)
            Alert resolved/superseded — reminders stopped.
        @elseif($patientAlert->is_read || $patientAlert->read_at)
            Seen in ReproCare {{ optional($patientAlert->read_at)->diffForHumans() }} — reminders stopped.
        @else
            Unseen — awaiting acknowledgement.
        @endif
    </div>
@endif
