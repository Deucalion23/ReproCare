@php
    $supportRows = collect($decisionSupport ?? []);
    $isOwnRecord = isset($pregnancy) && $pregnancy instanceof \App\Models\Pregnancy;
@endphp
<section id="decision-support" class="card mb-3" style="scroll-margin-top:90px" aria-labelledby="patient-decision-support-title">
    <div class="card-header d-flex align-items-center gap-2 py-2 px-3">
        <i class="bi bi-stars" style="color:var(--color-primary);"></i>
        <h2 id="patient-decision-support-title" class="h6 mb-0">Patient decision support</h2>
        @if($supportRows->count() > 1)
            <span class="badge rounded-pill text-bg-light border">{{ $supportRows->count() }} open records</span>
        @endif
    </div>
    <div class="card-body p-3">
        @forelse($supportRows as $support)
            @php($statusColor = $support['emergency'] || in_array($support['risk'], ['High', 'Critical']) ? 'danger' : ($support['risk'] === 'Medium' ? 'warning' : ($support['risk'] === 'Low' ? 'success' : 'info')))
            <article class="rounded p-2 ps-3 mb-2" style="border:1px solid var(--border);border-left:3px solid var(--color-{{ $statusColor }}) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div class="fw-semibold small" style="color:var(--text);">{{ $support['summary'] ?? 'Open pregnancy record' }}</div>
                    <div class="d-flex align-items-center gap-2">
                        @if($support['next_appointment'])
                            <span class="small text-muted">Next contact: {{ \Illuminate\Support\Carbon::parse($support['next_appointment'])->format('M j, Y') }}</span>
                        @endif
                        <span class="badge rounded-pill" style="background:var(--color-{{ $statusColor }}-soft);color:var(--color-{{ $statusColor }}-text)">{{ $support['emergency'] ? 'Emergency flag / ' : '' }}{{ $support['risk'] }} risk</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-uppercase fw-bold mb-1" style="font-size:0.68rem;letter-spacing:0.07em;color:var(--text-muted);">Key findings</div>
                        <ul class="small mb-0 ps-4" style="color:var(--text);line-height:1.45;">
                            @foreach($support['findings'] ?? $support['reasons'] ?? [] as $finding)
                                <li>{{ $finding }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="text-uppercase fw-bold mb-1" style="font-size:0.68rem;letter-spacing:0.07em;color:var(--text-muted);">Suggested next steps</div>
                        <ol class="small mb-0 ps-4" style="color:var(--text);line-height:1.45;">
                            @foreach($support['recommendations'] ?? [$support['action'] ?? ''] as $recommendation)
                                @if($recommendation)
                                    <li>{{ $recommendation }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </div>
                </div>
                @if(! $isOwnRecord)
                    <a class="small fw-semibold" href="{{ route('midwife.pregnancies.show', $support['pregnancy_id']) }}">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Review pregnancy record
                    </a>
                @endif
            </article>
        @empty
            <p class="small mb-0">No eligible open pregnancy record is available for these suggestions. Review the patient's history and confirm the recorded pregnancy status; this does not indicate Low risk.</p>
        @endforelse
        <p class="small text-muted mb-0 mt-2">Read-only suggestions built from recorded data — no diagnosis, risk change, or reminder is generated.</p>
    </div>
</section>
