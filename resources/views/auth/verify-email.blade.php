@extends('layouts.public')

@section('title', 'Verify your email — ReproCare')
@section('page-class', 'login-page')

@push('styles')
<style>
    .verify-wrap { display:flex; align-items:flex-start; justify-content:center; padding:56px 22px 64px; background:var(--color-bg); min-height:calc(100vh - 166px); }
    .verify-card { width:100%; max-width:480px; background:var(--color-surface); border:1px solid var(--color-border); border-radius:22px; padding:34px 32px; text-align:center; box-shadow:0 18px 48px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 14%, transparent); }
    .verify-card h2 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; letter-spacing:-.04em; margin:0 0 10px; }
    .verify-card p { color:var(--color-text-muted); font-size:.9rem; line-height:1.65; margin:0 0 14px; }
    .verify-ico { width:64px; height:64px; border-radius:50%; margin:0 auto 18px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; background:color-mix(in srgb, var(--color-primary) 12%, var(--color-surface)); color:var(--color-primary-text); }
</style>
@endpush

@section('content')
<div class="verify-wrap">
    <div class="verify-card" aria-labelledby="verify-title">
        <div class="verify-ico"><i class="bi bi-envelope-check" aria-hidden="true"></i></div>
        <h2 id="verify-title">Check your email</h2>
        <p>We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Click the link to verify your account, then continue to your dashboard.</p>

        @if (session('status'))
            <div class="public-alert public-alert-success" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" style="margin-top:18px;">
            @csrf
            <button type="submit" class="fb-btn-primary" style="width:100%; height:52px; border-radius:16px; border:none; background:var(--color-primary); color:var(--color-primary-on); font-weight:700; font-size:.95rem; cursor:pointer;">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:14px;">
            @csrf
            <button type="submit" style="background:none; border:none; color:var(--color-text-muted); font-size:.82rem; cursor:pointer; text-decoration:underline;">Sign out</button>
        </form>
    </div>
</div>
@endsection
