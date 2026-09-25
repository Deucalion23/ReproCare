@extends('layouts.public')
@section('title', 'Forgot password — ReproCare')
@section('navigation')
<div class="auth-nav-note"><a href="{{ route('home') }}"><span aria-hidden="true">&larr;</span> Back to Home</a></div>
@endsection
@section('content')
<div class="auth-shell public-container" style="max-width:520px;">
    <section class="login-panel">
        <h2>Forgot password</h2>
        <p class="login-intro">Enter your account email. If it exists, we will email a reset link.</p>
        @if(session('status'))<div class="public-alert public-alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="public-alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="POST" action="{{ route('password.email') }}">@csrf
            <div class="login-field"><label for="email">Email address</label><input type="email" id="email" name="email" value="{{ old('email') }}" required></div>
            <button class="button button-primary" type="submit">Send reset link</button>
        </form>
        <p class="auth-switch"><a href="{{ route('login') }}">Back to login</a></p>
    </section>
</div>
@endsection
