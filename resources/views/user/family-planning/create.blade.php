@extends('user.layout')

@section('title', 'Request Family Planning Counseling - ReproCare')

@section('user-content')
<div class="mb-2">
    @include('includes.back-button', ['backUrl' => route('user.family-planning.index'), 'backLabel' => 'Back to Family Planning'])
</div>
<div class="page-hero fade-in-card"><div class="page-hero-title">Request private counseling</div><p class="page-hero-subtitle mb-0">You can change your mind. A health worker will explain choices, benefits, and possible side effects.</p></div>
<div class="card mt-3"><div class="card-body p-4">
@if($errors->any()) <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('user.family-planning.store') }}">@csrf
<div class="row g-3"><div class="col-md-6"><label class="form-label">Method you want to discuss</label><select class="form-select" name="preferred_method"><option value="">I am not sure yet</option>@foreach(['condom','pill','injectable','implant','iud','natural_method','other'] as $method)<option value="{{ $method }}" @selected(old('preferred_method') === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Pregnancy intention</label><select class="form-select" name="pregnancy_intention"><option value="">Prefer not to say</option><option value="trying">Trying to become pregnant</option><option value="avoiding">Avoiding pregnancy</option><option value="spacing">Spacing pregnancies</option><option value="undecided">Undecided</option></select></div>
<div class="col-12"><label class="form-label">Question or concern (optional)</label><textarea class="form-control" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea></div></div>
@if($activePregnancy)
<div class="mt-4 p-3 rounded-3" style="background:var(--color-secondary-soft);border:1px solid var(--color-border);">
    <div class="form-check"><input class="form-check-input" type="checkbox" value="1" id="is_postpartum_plan" name="is_postpartum_plan" @checked(old('is_postpartum_plan'))><label class="form-check-label fw-bold" for="is_postpartum_plan">Make this my postpartum family-planning plan</label></div>
    <p class="small text-muted mt-2 mb-2">This is linked to your current pregnancy. It helps your care team prepare the method you choose after delivery; it does not start contraception during pregnancy.</p>
    <label class="form-label small">When would you prefer to start?</label><select name="postpartum_start_timing" class="form-select"><option value="undecided">I am not sure yet</option><option value="immediately_after_delivery">Immediately after delivery, if appropriate</option><option value="before_discharge">Before leaving the facility</option><option value="six_week_visit">At my 6-week postpartum visit</option></select>
</div>
@endif
<div class="d-flex gap-2 mt-4"><button class="btn btn-primary"><i class="bi bi-send me-1"></i> Send request</button><a class="btn btn-outline-secondary" href="{{ route('user.family-planning.index') }}">Cancel</a></div>
</form></div></div>
@endsection
