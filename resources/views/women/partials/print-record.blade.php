{{--
    One woman's complete health record, formatted for A4 print.
    Shared by women.print (single) and women.print-bulk (one per page).

    Expects: $woman, $type, $pregnancies, $checkups, $healthRecords, $referrals,
    $medicalConditions, $portal, $scopeLabel, $printedBy, $printedAt,
    $bulkIndex (int|null), $bulkTotal (int|null)
--}}
@php
    $isWalkIn = $type === 'unregistered';
    $displayName = $isWalkIn ? $woman->full_name : $woman->name;
    $recordRef = ($isWalkIn ? 'Field profile' : 'Patient') . ' ID #' . $woman->id;
    $statusLabel = $isWalkIn ? 'Unregistered' : ucfirst($woman->status ?? 'approved');

    $riskTag = fn (?string $risk) => match (true) {
        in_array($risk, ['High', 'Critical'], true) => 'pr-tag-danger',
        $risk === 'Medium' => 'pr-tag-warning',
        $risk === 'Low' => 'pr-tag-success',
        default => 'pr-tag-muted',
    };
    $checkupTag = fn (?string $status) => match ($status) {
        'Completed' => 'pr-tag-success',
        'Missed', 'Cancelled' => 'pr-tag-danger',
        default => 'pr-tag-warning',
    };

    // Legacy patient-reported history (pre-diagnosis free text / JSON blob).
    $legacyHistoryText = '';
    if (! $isWalkIn) {
        $legacyHistory = $woman->getAttribute('medical_history');
        if (is_string($legacyHistory)) {
            $decodedHistory = json_decode($legacyHistory, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $legacyHistory = $decodedHistory;
            }
        }
        if (is_array($legacyHistory)) {
            $legacyHistoryText = collect($legacyHistory)
                ->filter(fn ($value) => $value !== null && $value !== '' && $value !== false)
                ->map(fn ($value, $key) => is_scalar($value)
                    ? (is_string($key) ? ucfirst(str_replace('_', ' ', $key)) . ': ' : '') . (string) $value
                    : json_encode($value))
                ->implode('; ');
        } else {
            $legacyHistoryText = is_scalar($legacyHistory) ? trim((string) $legacyHistory) : '';
        }
    }

    $prSection = 0;
@endphp

<article class="pr-record">
    <header class="pr-doc-head">
        <div class="pr-brand">
            <img src="{{ asset('images/brand/reprocare-logo.svg') }}" alt="ReproCare logo">
            <div>
                <div class="pr-brand-name">ReproCare</div>
                <div class="pr-brand-sub">Maternal &amp; Reproductive Health Record System<br>San Carlos City, Pangasinan</div>
            </div>
        </div>
        <div class="pr-doc-title">
            <h1>Women's Health Record</h1>
            <div class="pr-doc-meta">
                <strong>{{ $portal }} Portal</strong> · {{ $scopeLabel }}<br>
                Printed {{ $printedAt->format('M d, Y · g:i A') }}<br>
                By {{ $printedBy }}
                @if ($bulkIndex !== null)
                    · Record {{ $bulkIndex }} of {{ $bulkTotal }}
                @endif
            </div>
        </div>
    </header>

    <div class="pr-identity">
        <div class="pr-identity-top">
            <h2 class="pr-name">{{ $displayName ?: 'Unknown' }}</h2>
            <div class="pr-pills">
                <span class="pr-pill {{ $isWalkIn ? 'pr-pill-warning' : 'pr-pill-accent' }}">{{ $isWalkIn ? 'Field-only profile' : 'Registered patient' }}</span>
                <span class="pr-pill {{ ! $isWalkIn && ($woman->status ?? 'approved') === 'approved' ? 'pr-pill-success' : 'pr-pill-muted' }}">{{ $statusLabel }}</span>
                <span class="pr-pill pr-pill-muted">{{ $recordRef }}</span>
            </div>
        </div>
        <div class="pr-identity-facts">
            <span><span class="pr-fact-label">Age / DOB</span><strong>{{ $woman->date_of_birth ? $woman->age . ' y/o · ' . $woman->date_of_birth->format('M d, Y') : 'Not recorded' }}</strong></span>
            <span><span class="pr-fact-label">Barangay</span><strong>{{ $woman->barangay ?: 'Not recorded' }}{{ $woman->purok?->name ? ' · ' . $woman->purok->name : '' }}</strong></span>
            <span><span class="pr-fact-label">Contact</span><strong>{{ $woman->contact_number ?: 'Not provided' }}</strong></span>
        </div>
    </div>

    {{-- 1 · Personal information --}}
    <section class="pr-section">
        <div class="pr-section-head">
            <span class="pr-section-num">{{ ++$prSection }}</span>
            <h3 class="pr-section-title">Personal Information</h3>
        </div>
        <div class="pr-grid">
            <div class="pr-field">
                <div class="pr-field-label">Full name</div>
                <div class="pr-field-value">{{ $displayName ?: 'Not recorded' }}</div>
            </div>
            <div class="pr-field">
                <div class="pr-field-label">Date of birth</div>
                <div class="pr-field-value">{{ $woman->date_of_birth ? $woman->date_of_birth->format('M d, Y') . ' (' . $woman->age . ' y/o)' : 'Not recorded' }}</div>
            </div>
            <div class="pr-field">
                <div class="pr-field-label">{{ $isWalkIn ? 'Contact number' : 'Gender' }}</div>
                <div class="pr-field-value">{{ $isWalkIn ? ($woman->contact_number ?: 'Not provided') : ($woman->gender ? ucfirst($woman->gender) : 'Not recorded') }}</div>
            </div>
            @if (! $isWalkIn)
                <div class="pr-field">
                    <div class="pr-field-label">Contact number</div>
                    <div class="pr-field-value">{{ $woman->contact_number ?: 'Not provided' }}</div>
                </div>
                <div class="pr-field">
                    <div class="pr-field-label">Email</div>
                    <div class="pr-field-value">{{ $woman->email ?: 'Not provided' }}</div>
                </div>
                <div class="pr-field">
                    <div class="pr-field-label">Account status</div>
                    <div class="pr-field-value">{{ $statusLabel }}</div>
                </div>
            @endif
            <div class="pr-field">
                <div class="pr-field-label">Barangay</div>
                <div class="pr-field-value">{{ $woman->barangay ?: 'Not recorded' }}</div>
            </div>
            <div class="pr-field">
                <div class="pr-field-label">Purok</div>
                <div class="pr-field-value">{{ $woman->purok?->name ?: 'Not assigned' }}</div>
            </div>
            <div class="pr-field">
                <div class="pr-field-label">{{ $isWalkIn ? 'Recorded by' : 'Registered' }}</div>
                <div class="pr-field-value">{{ $isWalkIn ? ($woman->recordedBy?->name ?? 'Unknown') : ($woman->created_at?->format('M d, Y') ?? 'Not recorded') }}</div>
            </div>
            @if (! $isWalkIn)
                <div class="pr-field pr-span-2">
                    <div class="pr-field-label">Partner</div>
                    <div class="pr-field-value">{{ $woman->partner_name ?: 'Not recorded' }}{{ $woman->partner_contact ? ' · ' . $woman->partner_contact : '' }}</div>
                </div>
            @else
                <div class="pr-field">
                    <div class="pr-field-label">Added</div>
                    <div class="pr-field-value">{{ $woman->created_at?->format('M d, Y') ?? 'Not recorded' }}</div>
                </div>
            @endif
            <div class="pr-field pr-span-3">
                <div class="pr-field-label">Address</div>
                <div class="pr-field-value">{{ $woman->address ?: 'Not provided' }}</div>
            </div>
            @if ($isWalkIn)
                <div class="pr-field pr-span-3">
                    <div class="pr-field-label">Reason for visit</div>
                    <div class="pr-field-value">{{ $woman->reason_for_visit ?: 'No reason recorded' }}</div>
                </div>
                <div class="pr-field pr-span-3">
                    <div class="pr-field-label">Notes</div>
                    <div class="pr-field-value">{{ $woman->notes ?: 'No notes recorded' }}</div>
                </div>
            @endif
        </div>
    </section>

    {{-- 2 · Emergency contacts (registered women) --}}
    @if (! $isWalkIn)
        <section class="pr-section">
            <div class="pr-section-head">
                <span class="pr-section-num">{{ ++$prSection }}</span>
                <h3 class="pr-section-title">Emergency Contacts</h3>
                <span class="pr-section-count">{{ $woman->emergencyContacts->count() }} on file</span>
            </div>
            @if ($woman->emergencyContacts->isNotEmpty())
                <table class="pr-table">
                    <colgroup><col style="width:24%"><col style="width:18%"><col style="width:18%"><col style="width:26%"><col style="width:14%"></colgroup>
                    <thead><tr><th>Name</th><th>Relationship</th><th>Contact number</th><th>Address</th><th>Priority</th></tr></thead>
                    <tbody>
                        @foreach ($woman->emergencyContacts as $contact)
                            <tr>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->relationship ?: '—' }}</td>
                                <td>{{ $contact->contact_number ?: '—' }}</td>
                                <td>{{ $contact->address ?: '—' }}</td>
                                <td><span class="pr-tag {{ $contact->is_primary || $contact->contact_order === 1 ? 'pr-tag-success' : 'pr-tag-muted' }}">{{ $contact->is_primary || $contact->contact_order === 1 ? 'Primary' : 'Secondary' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="pr-empty">No emergency contacts recorded.</div>
            @endif
        </section>

        {{-- 3 · Medical conditions --}}
        <section class="pr-section">
            <div class="pr-section-head">
                <span class="pr-section-num">{{ ++$prSection }}</span>
                <h3 class="pr-section-title">Medical Conditions</h3>
                <span class="pr-section-count">{{ $medicalConditions->count() }} recorded</span>
            </div>
            @if ($medicalConditions->isNotEmpty())
                <table class="pr-table">
                    <colgroup><col style="width:20%"><col style="width:12%"><col style="width:11%"><col style="width:12%"><col style="width:27%"><col style="width:18%"></colgroup>
                    <thead><tr><th>Condition</th><th>Diagnosed</th><th>Status</th><th>Verification</th><th>Notes</th><th>Recorded by</th></tr></thead>
                    <tbody>
                        @foreach ($medicalConditions as $condition)
                            <tr>
                                <td>{{ $condition->condition_name }}</td>
                                <td>{{ $condition->diagnosed_on?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="pr-tag {{ $condition->status === 'Active' ? 'pr-tag-danger' : ($condition->status === 'Resolved' ? 'pr-tag-success' : 'pr-tag-warning') }}">{{ $condition->status ?: '—' }}</span></td>
                                <td><span class="pr-tag {{ $condition->verification_status === 'Verified' ? 'pr-tag-success' : 'pr-tag-muted' }}">{{ $condition->verification_status ?: '—' }}</span></td>
                                <td>{{ $condition->notes ?: '—' }}</td>
                                <td>{{ $condition->recordedBy?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="pr-empty">No medical conditions recorded.</div>
            @endif
            @if ($legacyHistoryText !== '')
                <div class="pr-subnote">
                    <strong>Existing patient-reported history (not a verified diagnosis)</strong>
                    {{ $legacyHistoryText }}
                </div>
            @endif
        </section>
    @endif

    {{-- Pregnancy history --}}
    <section class="pr-section">
        <div class="pr-section-head">
            <span class="pr-section-num">{{ ++$prSection }}</span>
            <h3 class="pr-section-title">Pregnancy History</h3>
            <span class="pr-section-count">{{ $pregnancies->count() }} {{ Str::plural('pregnancy', $pregnancies->count()) }}</span>
        </div>
        @if ($pregnancies->isNotEmpty())
            <table class="pr-table">
                <colgroup><col style="width:5%"><col style="width:14%"><col style="width:14%"><col style="width:9%"><col style="width:16%"><col style="width:20%"><col style="width:22%"></colgroup>
                <thead><tr><th class="pr-num">#</th><th>LMP</th><th>EDD</th><th>G / P</th><th>Risk</th><th>Outcome / Status</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach ($pregnancies as $index => $pregnancy)
                        @php $risk = $pregnancy->risk_level ?? 'Unassessed'; @endphp
                        <tr>
                            <td class="pr-num">{{ $index + 1 }}</td>
                            <td>{{ $pregnancy->lmp?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $pregnancy->edd?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $pregnancy->gravida ?? '—' }} / {{ $pregnancy->para ?? '—' }}</td>
                            <td>
                                <span class="pr-tag {{ $riskTag($risk) }}">{{ $risk }}</span>
                                @if ($pregnancy->is_high_risk)<span class="pr-tag pr-tag-danger">High-risk</span>@endif
                            </td>
                            <td>{{ $pregnancy->outcome ?? ($pregnancy->ended_at ? 'Ended ' . $pregnancy->ended_at->format('M d, Y') : 'Active') }}</td>
                            <td>{{ $pregnancy->notes ?: ($pregnancy->risk_notes ?: '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="pr-empty">No pregnancies recorded.</div>
        @endif
    </section>

    {{-- Prenatal checkups --}}
    <section class="pr-section">
        <div class="pr-section-head">
            <span class="pr-section-num">{{ ++$prSection }}</span>
            <h3 class="pr-section-title">Prenatal Checkups</h3>
            <span class="pr-section-count">{{ $checkups->count() }} {{ Str::plural('checkup', $checkups->count()) }}</span>
        </div>
        @if ($checkups->isNotEmpty())
            <table class="pr-table">
                <colgroup><col style="width:13%"><col style="width:24%"><col style="width:20%"><col style="width:12%"><col style="width:31%"></colgroup>
                <thead><tr><th>Date</th><th>Purpose</th><th>Midwife</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach ($checkups as $checkup)
                        <tr>
                            <td>{{ ($checkup->actual_date ?? $checkup->scheduled_date)?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $checkup->purpose ?: '—' }}</td>
                            <td>{{ $checkup->midwife?->name ?? 'Not assigned' }}</td>
                            <td><span class="pr-tag {{ $checkupTag($checkup->status) }}">{{ $checkup->status ?: '—' }}</span></td>
                            <td>{{ $checkup->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="pr-empty">No checkups recorded.</div>
        @endif
    </section>

    {{-- Health records / visit vitals --}}
    <section class="pr-section">
        <div class="pr-section-head">
            <span class="pr-section-num">{{ ++$prSection }}</span>
            <h3 class="pr-section-title">Health Records &amp; Visit Vitals</h3>
            <span class="pr-section-count">{{ $healthRecords->count() }} {{ Str::plural('record', $healthRecords->count()) }}</span>
        </div>
        @if ($healthRecords->isNotEmpty())
            <table class="pr-table">
                <colgroup><col style="width:11%"><col style="width:8%"><col style="width:7%"><col style="width:7%"><col style="width:6%"><col style="width:7%"><col style="width:7%"><col style="width:9%"><col style="width:16%"><col style="width:22%"></colgroup>
                <thead><tr><th>Date</th><th>BP</th><th>Wt (kg)</th><th>Ht (cm)</th><th>HR</th><th>Temp (°C)</th><th>AOG</th><th>Risk</th><th>Recorded by</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach ($healthRecords as $record)
                        @php $risk = $record->risk_level ?? 'Low'; @endphp
                        <tr>
                            <td>
                                {{ $record->created_at?->format('M d, Y') ?? '—' }}
                                @if ($record->is_emergency)<br><span class="pr-tag pr-tag-danger">Emergency</span>@endif
                            </td>
                            <td>{{ $record->bp ?: '—' }}</td>
                            <td>{{ $record->weight ?: '—' }}</td>
                            <td>{{ $record->height ?: '—' }}</td>
                            <td>{{ $record->heart_rate ?: '—' }}</td>
                            <td>{{ $record->temperature ?: '—' }}</td>
                            <td>{{ $record->gestational_age ?: '—' }}</td>
                            <td><span class="pr-tag {{ $riskTag($risk) }}">{{ $risk }}</span></td>
                            <td>{{ $record->recordedBy?->name ?? '—' }}</td>
                            <td>{{ $record->notes ?: ($record->risk_notes ?: '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="pr-empty">No health records recorded.</div>
        @endif
    </section>

    {{-- Referrals --}}
    @if ($referrals->isNotEmpty() || $isWalkIn)
        <section class="pr-section">
            <div class="pr-section-head">
                <span class="pr-section-num">{{ ++$prSection }}</span>
                <h3 class="pr-section-title">Referral History</h3>
                <span class="pr-section-count">{{ $referrals->count() }} {{ Str::plural('referral', $referrals->count()) }}</span>
            </div>
            @if ($referrals->isNotEmpty())
                <table class="pr-table">
                    <colgroup><col style="width:13%"><col style="width:37%"><col style="width:12%"><col style="width:12%"><col style="width:26%"></colgroup>
                    <thead><tr><th>Date</th><th>Reason</th><th>Urgency</th><th>Status</th><th>Notes</th></tr></thead>
                    <tbody>
                        @foreach ($referrals as $referral)
                            <tr>
                                <td>{{ $referral->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $referral->reason ?: '—' }}</td>
                                <td><span class="pr-tag {{ in_array(strtolower((string) $referral->urgency), ['urgent', 'emergency'], true) ? 'pr-tag-danger' : 'pr-tag-muted' }}">{{ ucfirst($referral->urgency ?: '—') }}</span></td>
                                <td><span class="pr-tag {{ $referral->status === 'completed' ? 'pr-tag-success' : (in_array($referral->status, ['rejected', 'declined'], true) ? 'pr-tag-danger' : 'pr-tag-warning') }}">{{ ucfirst($referral->status ?: '—') }}</span></td>
                                <td>{{ $referral->bhw_notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="pr-empty">No referrals recorded.</div>
            @endif
        </section>
    @endif

    {{-- Valid ID documents --}}
    @if (! $isWalkIn && $woman->hasIdImages())
        <section class="pr-section">
            <div class="pr-section-head">
                <span class="pr-section-num">{{ ++$prSection }}</span>
                <h3 class="pr-section-title">Valid ID Documents</h3>
            </div>
            <div class="pr-id-grid">
                <figure class="pr-id-figure">
                    <figcaption class="pr-id-caption">Front of ID</figcaption>
                    @if ($woman->id_image_front_url)
                        <img src="{{ $woman->id_image_front_url }}" alt="Front of valid ID for {{ $displayName }}">
                    @else
                        <div class="pr-empty">Front not on file.</div>
                    @endif
                </figure>
                <figure class="pr-id-figure">
                    <figcaption class="pr-id-caption">Back of ID</figcaption>
                    @if ($woman->id_image_back_url)
                        <img src="{{ $woman->id_image_back_url }}" alt="Back of valid ID for {{ $displayName }}">
                    @else
                        <div class="pr-empty">Back not on file.</div>
                    @endif
                </figure>
            </div>
        </section>
    @endif

    <footer class="pr-record-foot">
        <span>Confidential patient information — for authorized health personnel only.</span>
        <span>
            @if ($bulkIndex !== null)
                Record {{ $bulkIndex }} of {{ $bulkTotal }} · {{ $displayName }}
            @else
                End of record · {{ $displayName }}
            @endif
            · ReproCare {{ $portal }}
        </span>
    </footer>
</article>
