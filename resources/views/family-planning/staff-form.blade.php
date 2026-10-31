@extends(auth()->user()->role === 'midwife' ? 'midwife.layout' : 'bhw.layout')
@section(auth()->user()->role === 'midwife' ? 'midwife-content' : 'bhw-content')
@php($base = auth()->user()->role === 'midwife' ? 'midwife.family-planning' : 'bhw.family-planning')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $record->exists ? 'Review family-planning record' : 'Family-planning service record' }}</h1>
        <p class="text-muted mb-0">Record services only after a private, informed-choice discussion.</p>
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

        <!-- ===== SECTION 1: THE WOMAN'S REQUEST (read-only, what she asked for) ===== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary-soft text-primary fw-bold py-3 rounded-top">
                        <i class="bi bi-chat-heart-fill me-2"></i>The Woman's Request
                        <span class="float-end small opacity-75">{{ $record->exists ? 'Read-only — entered by client' : '' }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Client: dropdown when creating new, locked text when reviewing existing -->
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Client</label>
                                @if($record->exists)
                                    <input type="text" class="form-control" value="{{ $record->client->name }} — {{ $record->client->barangay }}" readonly>
                                    <input type="hidden" name="user_id" value="{{ $record->user_id }}">
                                @else
                                    <select name="user_id" class="form-select" required>
                                        <option value="">Select client below</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" @selected(old('user_id') == $client->id)>
                                                {{ $client->name }} — {{ $client->barangay }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <!-- Status (from her request) -->
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Status (her request)</label>
                                <input type="text" class="form-control" value="{{ ucfirst($record->status) }}" readonly>
                            </div>
                            <!-- Preferred method (what she wants to discuss) -->
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Method she wants to discuss</label>
                                <input type="text" class="form-control" value="{{ $record->preferred_method ? ucfirst(str_replace('_', ' ', $record->preferred_method)) : 'Not stated' }}" readonly>
                            </div>
                            <!-- Pregnancy intention -->
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Pregnancy intention</label>
                                <input type="text" class="form-control" value="{{ $record->pregnancy_intention ? ucfirst($record->pregnancy_intention) : 'Not stated' }}" readonly>
                            </div>
                            <!-- Her question/notes -->
                            <div class="col-12">
                                <label class="form-label small text-muted">Her question or concern</label>
                                <textarea class="form-control" rows="2" readonly>{{ $record->notes ?? 'No additional notes provided.' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SECTION 2: MIDWIFE'S SERVICE RECORD (editable, what you provide) ===== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success-soft text-success fw-bold py-3 rounded-top">
                        <i class="bi bi-person-check-fill me-2"></i>Midwife's Service Record
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Follow-up / resupply</strong> is the date when this client should return for her next pill pack, injectable shot, implant check, IUD check, or condom resupply. Set it so she is reminded before her current method runs out.
                        </div>

                        <div class="row g-3">
                            <!-- Method given (what you actually provided) -->
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

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    @foreach(['active','stopped','referred'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $record->status) === $status)>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Counseling date -->
                            <div class="col-md-6">
                                <label class="form-label">Counseling date <span class="text-danger">*</span></label>
                                <input type="date" name="counseling_date" class="form-control" value="{{ old('counseling_date', $record->counseling_date?->format('Y-m-d')) ?? now()->format('Y-m-d') }}" required>
                            </div>

                            <!-- Start date -->
                            <div class="col-md-6">
                                <label class="form-label">Start date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $record->start_date?->format('Y-m-d')) }}">
                            </div>

                            <!-- Next follow-up / resupply -->
                            <div class="col-md-6">
                                <label class="form-label">Next follow-up / resupply date <span class="text-danger">*</span></label>
                                <input type="date" name="next_follow_up" class="form-control" value="{{ old('next_follow_up', $record->next_follow_up?->format('Y-m-d')) }}" required>
                            </div>

                            <!-- Side effects or concern -->
                            <div class="col-md-6">
                                <label class="form-label">Side effects or concern</label>
                                <textarea name="side_effects" class="form-control" rows="2">{{ old('side_effects', $record->side_effects) }}</textarea>
                            </div>

                            <!-- Midwife notes -->
                            <div class="col-md-6">
                                <label class="form-label">Midwife notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $record->notes) }}</textarea>
                            </div>

                            <!-- Informed choice -->
                            <div class="col-12 form-check ms-1">
                                <input class="form-check-input me-2" type="checkbox" name="informed_choice_confirmed" value="1" id="choice" @checked(old('informed_choice_confirmed', $record->informed_choice_confirmed))>
                                <label class="form-check-label fw-bold" for="choice">Client received informed-choice counseling and may accept, decline, or change methods.</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SECTION 3: POSTPARTUM FAMILY-PLANNING PLAN (optional) ===== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger-soft text-danger fw-bold py-3 rounded-top">
                        <i class="bi bi-heart-pulse-fill me-2"></i>Postpartum Family-Planning Plan (Optional)
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input me-2" type="checkbox" name="is_postpartum_plan" value="1" id="postpartumPlan"
                                @checked(old('is_postpartum_plan', $record->is_postpartum_plan))
                                onchange="togglePostpartumFields()">
                            <label class="form-check-label fw-bold" for="postpartumPlan">Create a postpartum family-planning plan</label>
                        </div>
                        <p class="small text-muted mb-3">
                            When checked, this record is linked to the selected client's current pregnancy and appears in postpartum tracking after delivery.
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
        </div>

        <button type="submit" class="btn btn-primary mt-4" id="saveBtn">Save service record</button>
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