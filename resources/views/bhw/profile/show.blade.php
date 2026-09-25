@extends('bhw.layout')

@section('title', 'My Profile')

@section('bhw-content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            @include('bhw.profile.partials.profile-card', ['user' => $user])
        </div>
    </div>
</div>
@endsection
