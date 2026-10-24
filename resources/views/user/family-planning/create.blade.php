@extends('user.layout')

@section('title', 'Request Family Planning Counseling - ReproCare')

@section('user-content')
<div class="page-hero fade-in-card"><div class="page-hero-title">Request private counseling</div><p class="page-hero-subtitle mb-0">You can change your mind. A health worker will explain choices, benefits, and possible side effects.</p></div>
<div class="card mt-3"><div class="card-body p-4">
@if($errors->any()) <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('user.family-planning.store') }}">@csrf
<div class="row g-3"><div class="col-md-6"><label class="form-label">Method you want to discuss</label><select class="form-select" name="preferred_method"><option value="">I am not sure yet</option>@foreach(['condom','pill','injectable','implant','iud','natural_method','other'] as $method)<option value="{{ $method }}" @selected(old('preferred_method') === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Pregnancy intention</label><select class="form-select" name="pregnancy_intention"><option value="">Prefer not to say</option><option value="trying">Trying to become pregnant</option><option value="avoiding">Avoiding pregnancy</option><option value="spacing">Spacing pregnancies</option><option value="undecided">Undecided</option></select></div>
<div class="col-12"><label class="form-label">Question or concern (optional)</label><textarea class="form-control" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea></div></div>
<div class="d-flex gap-2 mt-4"><button class="btn btn-primary"><i class="bi bi-send me-1"></i> Send request</button><a class="btn btn-outline-secondary" href="{{ route('user.family-planning.index') }}">Cancel</a></div>
</form></div></div>
@endsection
