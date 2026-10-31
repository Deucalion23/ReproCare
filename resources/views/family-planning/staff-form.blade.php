@extends(auth()->user()->role === 'midwife' ? 'midwife.layout' : 'bhw.layout')
@section(auth()->user()->role === 'midwife' ? 'midwife-content' : 'bhw-content')
@php($base = auth()->user()->role === 'midwife' ? 'midwife.family-planning' : 'bhw.family-planning')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $record->exists ? 'Review Family Planning' : 'Family-Planning Service Record' }}</h1>
        <p class="text-muted mb-0">Private counseling only. The woman's request is separate from the service you provide.</p>
    </div>
    <a href="{{ route($base.'.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card"><div class="card-body p-4">
    @if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ $record->exists ? route($base.'.update', $record) : route($base.'.store') }}">@csrf
        @if($record->exists) @method('PUT') @endif

        <!-- ===== THE WOMAN'S REQUEST (read-only — entered by the client) ===== -->
        <div class="mb-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid var(--color-primary);">
                <div class="card-header fw-bold py-3 rounded-top" style="background:var(--color-primary-soft); color:var(--color-primary);">
                    <i class="bi bi-chat-heart-fill me-2"></i>The Woman's Request
                    <span class="float-end small opacity-75">Read-only · entered by client</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Client (locked when reviewing)</label>
                            @if($record->exists)
                                <div class="form-control" style="background:var(--color-surface-soft); cursor:not-allowed;" readonly>
                                    <strong>{{ $record->client->name }}</strong> <span class="text-muted">— {{ $record->client->barangay }}</span>
                                </div>
                                <input type="hidden" name="user_id" value="{{ $record->user_id }}">
                                <div class="form-text small">Reviewing request for this client only. You cannot switch clients.</div>
                            @else
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select client below</option>
                                    @if(count($clients) === 0)
                                        <option value="" disabled>No approved women found. Register a patient first.</option>
                                    @else
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" @selected(old('user_id') == $client->id)>
                                                {{ $client->name }} — {{ $client->barangay }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if(count($clients) === 0)
                                    <div class="form-text text-warning">
                                        To add test data women, run: <code class="text-dark">php artisan db:seed --class=Rhu1PregnantWomenSeeder</code>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Request status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($record->status) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Method she wants to discuss</label>
                            <input type="text" class="form-control" value="{{ $record->preferred_method ? ucfirst(str_replace('_', ' ', $record->preferred_method)) : 'Not stated' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Pregnancy intention</label>
                            <input type="text" class="form-control" value="{{ $record->pregnancy_intention ? ucfirst($record->pregnancy_intention) : 'Not stated' }}" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Her question or concern</label>
                            <textarea class="form-control" rows="2" readonly>{{ $record->notes ?? 'No additional notes provided.' }}</textarea>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                Request created: {{ $record->created_at?->format('M j, Y H:i') ?? '—' }} ·
                                Status flow: <span class="badge text-bg-warning">requested</span> (waiting for approval) → <span class="badge text-bg-success">active</span> (approved/scheduled) → <span class="badge text-bg-info">referred</span> / <span class="badge text-bg-secondary">stopped</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MIDWIFE SERVICE RECORD (editable — your input) ===== -->
        <div class="mb-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid var(--color-success);">
                <div class="card-header fw-bold py-3 rounded-top" style="background:var(--color-success-soft); color:var(--color-success);">
                    <i class="bi bi-person-check-fill me-2"></i>Midwife's Service Record
                    <span class="float-end small opacity-75">Editable — your action</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Follow-up / resupply</strong> = the date the client must return for her next pill pack, injectable shot, implant check, IUD check, or condom resupply. Set it before the current method runs out. After you approve this request, set status to <strong>active</strong> and set the counseling date and next follow-up.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Method given <span class="text-danger">*</span></label>
                            <select name="method_given" class="form-select" required>
                                <option value="">Select method given</option>
                                @foreach(['condom','pill','injectable','implant','iud','natural_method','other'] as $method)
                                    <option value="{{ $method }}" @selected(old('method_given', $record->method_given) === $method)>
                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['requested','active','stopped','referred'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $record->status) === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Counseling date <span class="text-danger">*</span></label>
                            <input type="date" name="counseling_date" class="form-control" value="{{ old('counseling_date', $record->counseling_date?->format('Y-m-d')) ?? now()->format('Y-m-d') }}" required>
                            <small class="text-muted">When you discussed/approved the request.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $record->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next follow-up / resupply date <span class="text-danger">*</span></label>
                            <input type="date" name="next_follow_up" class="form-control" value="{{ old('next_follow_up', $record->next_follow_up?->format('Y-m-d')) }}" required>
                            <small class="text-muted">When she should return (e.g. next injectable, IUD check, resupply).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Side effects or concern</label>
                            <textarea name="side_effects" class="form-control" rows="2">{{ old('side_effects', $record->side_effects) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Midwife notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $record->notes) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check ms-1">
                                <input class="form-check-input me-2" type="checkbox" name="informed_choice_confirmed" value="1" id="choice" @checked(old('informed_choice_confirmed', $record->informed_choice_confirmed))>
                                <label class="form-check-label fw-bold" for="choice">Client received informed-choice counseling and may accept, decline, or change methods.</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== POSTPARTUM FAMILY-PLANNING PLAN (optional) ===== -->
        <div class="mb-3">
            <div class="card border-0 shadow-sm" style="border-left:4px solid var(--color-danger);">
                <div class="card-header fw-bold py-3 rounded-top" style="background:var(--color-danger-soft); color:var(--color-danger);">
                    <i class="bi bi-heart-pulse-fill me-2"></i>Postpartum Family-Planning Plan (Optional)
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input me-2" type="checkbox" name="is_postpartum_plan" value="1" id="postpartumPlan"
                            @checked(old('is_postpartum_plan', $record->is_postpartum_plan))
                            onchange="togglePostpartumFields()">
                        <label class="form-check-label fw-bold" for="postpartumPlan">Create a postpartum family-planning plan</label>
                    </div>
                    <p class="small text-muted mb-3">
                        When checked, this record links to the client's current pregnancy and appears in postpartum tracking after delivery. The <strong>planned start timing</strong> tells when she wants to begin the method after birth.
                    </p>
                    <div id="postpartumFields" style="display: {{ $record->is_postpartum_plan ? 'block' : 'none' }};">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Planned start timing</label>
                                <select name="postpartum_start_timing" class="form-select">
                                    <option value="undecided" @selected(old('postpartum_start_timing', $record->postpartum_start_timing) === 'undecided')>Undecided</option>
                                    <option value="immediately_after_delivery" @selected(old('postpartum_start_timing', $record->postpartum_start_timing) === 'immediately_after_delivery')>Immediately after delivery, if appropriate</option>
                                    <option value="before_discharge" @selected(old('postpartum_start_timing', $record->postpartum_start_timing) === 'before_discharge')>Before leaving the facility</option>
                                    <option value="six_week_visit" @selected(old('postpartum_start_timing', $record->postpartum_start_timing) === 'six_week_visit')>At 6-week postpartum visit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-2" id="saveBtn">Save service record</button>
    </form>
</div></div>

<script>
    function togglePostpartumFields() {
        const checkbox = document.getElementById('postpartumPlan');
        const fields = document.getElementById('postpartumFields');
        fields.style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection
