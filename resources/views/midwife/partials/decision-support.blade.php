<section id="decision-support" class="card mb-4" style="scroll-margin-top:90px" aria-labelledby="patient-decision-support-title">
    <div class="card-header">
        <h2 id="patient-decision-support-title" class="h5 mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-stars" style="color:var(--color-primary);"></i> Patient decision support
        </h2>
        <p class="text-muted small mb-0">Personalized care suggestions generated from this patient's recorded data. Verify the patient's current condition and existing care plan before acting — no diagnosis or treatment is generated.</p>
    </div>
    <div class="card-body">
        @forelse($decisionSupport ?? [] as $support)
            @php($statusColor = $support['emergency'] || in_array($support['risk'], ['High', 'Critical']) ? 'danger' : ($support['risk'] === 'Medium' ? 'warning' : ($support['risk'] === 'Low' ? 'success' : 'info')))
            <article class="border rounded p-3 mb-3" style="border-left:4px solid var(--color-{{ $statusColor }}) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="fw-bold" style="color:var(--text);">{{ $support['summary'] ?? 'Open pregnancy record' }}</div>
                    <span class="badge" style="background:var(--color-{{ $statusColor }}-soft);color:var(--color-{{ $statusColor }}-text)">{{ $support['emergency'] ? 'Emergency flag / ' : '' }}{{ $support['risk'] }} risk</span>
                </div>

                <h3 class="text-uppercase fw-bold mb-2" style="font-size:0.72rem;letter-spacing:0.07em;color:var(--text-muted);">Key findings</h3>
                <ul class="small mb-3" style="color:var(--text);">
                    @foreach($support['findings'] ?? $support['reasons'] ?? [] as $finding)
                        <li class="mb-1">{{ $finding }}</li>
                    @endforeach
                </ul>

                <h3 class="text-uppercase fw-bold mb-2" style="font-size:0.72rem;letter-spacing:0.07em;color:var(--text-muted);">Suggested next steps</h3>
                <ol class="small mb-3" style="color:var(--text);">
                    @foreach($support['recommendations'] ?? [$support['action'] ?? ''] as $recommendation)
                        @if($recommendation)
                            <li class="mb-1">{{ $recommendation }}</li>
                        @endif
                    @endforeach
                </ol>

                <p class="small mb-3" style="color:var(--text-muted);">
                    @if($support['next_appointment'])
                        Next scheduled contact: {{ \Illuminate\Support\Carbon::parse($support['next_appointment'])->format('F j, Y') }}. Confirm attendance and the current plan.
                    @else
                        No upcoming linked appointment is recorded. Verify whether one is arranged and schedule follow-up according to the care plan.
                    @endif
                </p>

                <a class="btn btn-sm btn-primary" href="{{ route('midwife.pregnancies.show', $support['pregnancy_id']) }}">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Review pregnancy record
                </a>
            </article>
        @empty
            <p class="mb-0">No eligible open pregnancy record is available for these suggestions. Review the patient's history and confirm the recorded pregnancy status; this does not indicate Low risk.</p>
        @endforelse
        <p class="small text-muted mb-0 mt-2">Suggestions are read-only — they do not send reminders, change risk levels, or update records automatically.</p>
    </div>
</section>
