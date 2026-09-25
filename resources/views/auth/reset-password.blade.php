@extends('layouts.public')
@section('title', 'Reset password — ReproCare')
@section('navigation')
<div class="auth-nav-note"><a href="{{ route('home') }}"><span aria-hidden="true">&larr;</span> Back to Home</a></div>
@endsection
@section('content')
<div class="auth-shell public-container" style="max-width:520px;">
    <section class="login-panel">
        <h2>Reset password</h2>
        @if($errors->any())<div class="public-alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="POST" action="{{ route('password.update') }}">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="login-field"><label for="email">Email address</label><input type="email" id="email" name="email" value="{{ old('email', $email) }}" required></div>
            <div class="login-field"><label for="password">New password</label><input type="password" id="password" name="password" required minlength="8"></div>
            <div class="login-field"><label for="password_confirmation">Confirm password</label><input type="password" id="password_confirmation" name="password_confirmation" required></div>
            <button class="button button-primary" type="submit">Reset password</button>
        </form>
    </section>
</div>
@endsection
