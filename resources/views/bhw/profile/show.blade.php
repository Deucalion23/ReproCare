@extends('bhw.layout')

@section('title', 'My Profile')

@section('bhw-content')
<div class="py-4">
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">My Profile</div>
            <p class="page-hero-subtitle">View your BHW profile and assignment.</p>
        </div>
    </div>
</div>
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            @include('bhw.profile.partials.profile-card', ['user' => $user])
        </div>
    </div>
</div>
@endsection
