<div class="card fade-in-card mb-4 rec-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Medical Conditions ({{ $medicalConditions->count() }})</span>
        @unless($canManageConditions)
            <span class="small text-muted"><i class="bi bi-eye me-1"></i>View and review only</span>
        @endunless
    </div>
    <div class="card-body">
        @php
            $legacyHistory = $woman->getAttribute('medical_history');
            if (is_string($legacyHistory)) {
                $decodedHistory = json_decode($legacyHistory, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $legacyHistory = $decodedHistory;
                }
            }
            if (is_array($legacyHistory)) {
                $legacyHistoryText = collect($legacyHistory)->filter(fn ($value) => $value !== null && $value !== '' && $value !== false)
                    ->map(fn ($value, $key) => is_scalar($value)
                        ? (is_string($key) ? ucfirst(str_replace('_', ' ', $key)) . ': ' : '') . (string) $value
                        : json_encode($value))
                    ->implode('; ');
            } else {
                $legacyHistoryText = is_scalar($legacyHistory) ? trim((string) $legacyHistory) : '';
            }
        @endphp
        @if($legacyHistoryText !== '')
            <div class="rounded-3 border p-3 mb-3 small" style="border-color:var(--color-border) !important; background:var(--color-surface-soft);">
                <div class="fw-bold mb-1">Existing patient-reported history</div>
                <div>{{ $legacyHistoryText }}</div>
                <div class="text-muted mt-1">This legacy profile note is not a verified diagnosis.</div>
            </div>
        @endif
        <p class="small text-muted mb-3">
            Only recorded diagnoses are shown here. Blood pressure readings and symptoms do not create diagnoses.
        </p>

        @if($medicalConditions->isEmpty())
            <div class="rounded-3 border p-3 text-muted small mb-3">
                No medical conditions have been recorded for this patient.
            </div>
        @else
            <div class="d-grid gap-3">
                @foreach($medicalConditions as $condition)
                    @php
                        $statusClass = match($condition->status) {
                            'Active' => 'bg-danger-subtle text-danger-emphasis',
                            'Resolved' => 'bg-success-subtle text-success-emphasis',
                            default => 'bg-warning-subtle text-warning-emphasis',
                        };
                        $verificationClass = $condition->verification_status === 'Verified'
                            ? 'bg-success-subtle text-success-emphasis'
                            : ($condition->verification_status === 'Reported'
                                ? 'bg-info-subtle text-info-emphasis'
                                : 'bg-secondary-subtle text-secondary-emphasis');
                    @endphp
                    <article class="rounded-3 border p-3" style="border-color:var(--color-border) !important; background:var(--color-surface);">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                            <h6 class="fw-bold mb-0">{{ $condition->condition_name }}</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill {{ $statusClass }}">{{ $condition->status }}</span>
                                <span class="badge rounded-pill {{ $verificationClass }}">{{ $condition->verification_status }}</span>
                            </div>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-sm-6"><span class="text-muted">Date diagnosed:</span> {{ $condition->diagnosed_on?->format('M d, Y') ?? 'Not recorded' }}</div>
                            <div class="col-sm-6"><span class="text-muted">Recorded by:</span> {{ $condition->recordedBy?->name ?? 'Not recorded' }}</div>
                            <div class="col-sm-6"><span class="text-muted">Date recorded:</span> {{ $condition->created_at?->format('M d, Y') ?? 'Not recorded' }}</div>
                            @if($condition->updated_by_id)
                                <div class="col-sm-6"><span class="text-muted">Last updated by:</span> {{ $condition->updatedBy?->name ?? 'Not available' }}</div>
                            @endif
                            @if($condition->notes)
                                <div class="col-12"><span class="text-muted">Notes:</span> {{ $condition->notes }}</div>
                            @endif
                        </div>

                        @if($canManageConditions)
                            <details class="mt-3">
                                <summary class="small fw-semibold" style="color:var(--color-primary-text); cursor:pointer;">Edit condition record</summary>
                                <form method="POST" action="{{ route('rhu.women.conditions.update', [$woman->id, $condition->id]) }}" class="row g-2 mt-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Condition name</label>
                                        <input class="form-control form-control-sm" name="condition_name" maxlength="150" required value="{{ $condition->condition_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Date diagnosed</label>
                                        <input type="date" class="form-control form-control-sm" name="diagnosed_on" max="{{ now()->toDateString() }}" value="{{ $condition->diagnosed_on?->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Current status</label>
                                        <select class="form-select form-select-sm" name="status" required>
                                            @foreach(['Active', 'Resolved', 'Under Review'] as $status)
                                                <option value="{{ $status }}" @selected($condition->status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Verification</label>
                                        <select class="form-select form-select-sm" name="verification_status" required>
                                            @foreach(['Reported', 'Verified', 'Awaiting Verification'] as $verification)
                                                <option value="{{ $verification }}" @selected($condition->verification_status === $verification)>{{ $verification }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Relevant notes</label>
                                        <textarea class="form-control form-control-sm" name="notes" rows="2" maxlength="2000">{{ $condition->notes }}</textarea>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button class="btn btn-sm btn-primary" type="submit">Save changes</button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if($canManageConditions)
            <details class="mt-3">
                <summary class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>Record a medical condition</summary>
                <form method="POST" action="{{ route('rhu.women.conditions.store', $woman->id) }}" class="row g-3 border rounded-3 p-3 mt-2">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Condition name</label>
                        <input class="form-control" name="condition_name" list="condition-suggestions-{{ $woman->id }}" maxlength="150" required value="{{ old('condition_name') }}" placeholder="Enter a diagnosed condition">
                        <datalist id="condition-suggestions-{{ $woman->id }}">
                            <option value="Diabetes"><option value="Anemia"><option value="Asthma">
                            <option value="Heart condition"><option value="Kidney condition">
                            <option value="Thyroid condition"><option value="Hypertension"><option value="Other diagnosed illness">
                        </datalist>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Date diagnosed <span class="text-muted fw-normal">(if known)</span></label>
                        <input type="date" class="form-control" name="diagnosed_on" max="{{ now()->toDateString() }}" value="{{ old('diagnosed_on') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Current status</label>
                        <select class="form-select" name="status" required>
                            @foreach(['Active', 'Resolved', 'Under Review'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'Active') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Verification</label>
                        <select class="form-select" name="verification_status" required>
                            @foreach(['Reported', 'Verified', 'Awaiting Verification'] as $verification)
                                <option value="{{ $verification }}" @selected(old('verification_status', 'Awaiting Verification') === $verification)>{{ $verification }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Relevant notes</label>
                        <textarea class="form-control" name="notes" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary btn-sm" type="submit">Save condition</button>
                    </div>
                </form>
            </details>
        @endif
    </div>
</div>
