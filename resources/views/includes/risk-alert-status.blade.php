@if(isset($riskAlertStatus) && $riskAlertStatus['state'] !== 'none')
    <div class="alert alert-{{ $riskAlertStatus['state'] === 'unseen' ? 'warning' : 'success' }} mb-4" role="status">
        <strong>Patient risk alert: {{ ucfirst($riskAlertStatus['state']) }}</strong>
        @if($riskAlertStatus['state'] === 'resolved')
            — concern cleared or superseded {{ optional($riskAlertStatus['resolved_at'])->diffForHumans() }}. Reminders stopped for this alert.
        @elseif($riskAlertStatus['seen'])
            — acknowledged in ReproCare {{ optional($riskAlertStatus['read_at'])->diffForHumans() }}. Reminders stopped; this does not mean the clinical concern is resolved.
        @else
            — awaiting patient acknowledgement. Reminded at most once daily while active.
        @endif
        <div class="small mt-1">
            SMS: {{ $riskAlertStatus['sms_mock'] ? 'Mock only (no phone delivery)' : ($riskAlertStatus['sms_status'] === 'sent' ? 'Accepted by gateway (phone read status unavailable)' : ucfirst($riskAlertStatus['sms_status'] ?? 'Not sent')) }}
            @if($riskAlertStatus['sms_sent_at']) · {{ $riskAlertStatus['sms_sent_at']->format('M j, Y g:i A') }} @endif
        </div>
    </div>
@endif
