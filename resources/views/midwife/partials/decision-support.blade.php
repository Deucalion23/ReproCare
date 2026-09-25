<section id="decision-support" class="card mb-4" style="scroll-margin-top:90px" aria-labelledby="patient-decision-support-title">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 id="patient-decision-support-title" class="h5 mb-0">Patient decision support</h2>
        <span class="badge bg-secondary">Recorded-data rules</span>
    </div>
    <div class="card-body">
        <p class="text-muted small">Suggested follow-up based on saved records. Verify the patient's current condition and existing care plan before acting. No new diagnosis or treatment is generated.</p>
        @forelse($decisionSupport ?? [] as $support)
            @php($statusColor = $support['emergency'] || in_array($support['risk'], ['High', 'Critical']) ? 'danger' : ($support['risk'] === 'Medium' ? 'warning' : ($support['risk'] === 'Low' ? 'success' : 'info')))
            <article class="border rounded p-3 mb-3" style="border-left:4px solid var(--color-{{ $statusColor }}) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="h6 mb-0">Pregnancy #{{ $support['pregnancy_id'] }}</h3>
                    <span class="badge" style="background:var(--color-{{ $statusColor }}-soft);color:var(--color-{{ $statusColor }}-text)">{{ $support['emergency'] ? 'Emergency flag / ' : '' }}{{ $support['risk'] }}</span>
                </div>
                <p class="fw-semibold mb-2">{{ $support['action'] }}</p>
                <ul class="small mb-2">@foreach($support['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul>
                <p class="small mb-2">Expected delivery: {{ $support['edd'] ?? 'Not recorded' }}. Latest linked assessment: {{ $support['assessment_date'] ?? 'Not recorded' }}.</p>
                <p class="small">{{ $support['next_appointment'] ? 'Next scheduled contact: '.$support['next_appointment'].'. Confirm attendance and the current plan.' : 'No upcoming linked appointment is recorded. Verify whether one is arranged and schedule follow-up according to the care plan.' }}</p>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('midwife.pregnancies.show', $support['pregnancy_id']) }}">Review pregnancy record</a>
            </article>
        @empty
            <p class="mb-0">No eligible open pregnancy record is available for these suggestions. Review the patient's history and confirm the recorded pregnancy status; this does not indicate Low risk.</p>
        @endforelse
        <p class="small text-muted mb-0">Suggestions do not send reminders, change risk levels or update records automatically. Patient details are not sent to an online AI for this panel.</p>
    </div>
</section>
