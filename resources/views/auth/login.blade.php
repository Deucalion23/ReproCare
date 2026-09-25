@extends('layouts.public')

@section('title', 'Welcome back — ReproCare')
@section('page-class', 'login-page')

@push('styles')
<style>
    /* ── Facebook-style split auth, ReproCare rose palette ── */
    .fb-auth { display:grid; grid-template-columns:1.05fr 1fr; min-height:calc(100vh - 166px); }
    .fb-hero { position:relative; overflow:hidden; padding:64px 56px; display:flex; flex-direction:column; justify-content:center; background:var(--color-secondary-soft); }
    .fb-hero-logo { display:flex; align-items:center; gap:10px; font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.5rem; color:var(--color-text); margin-bottom:28px; }
    .fb-hero-logo span span { color:var(--color-secondary-text); }
    .fb-hero h1 { font-size:clamp(2.4rem,4vw,3.6rem); line-height:1.08; color:var(--color-text); margin:0 0 32px; max-width:440px; }
    .fb-hero h1 span { color:var(--color-secondary-text); }
    .fb-collage { position:relative; width:100%; height:430px; max-width:660px; margin-left:auto; transform:translateX(18px); }
    .fb-shot { position:absolute; border-radius:22px; overflow:hidden; box-shadow:0 18px 48px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); border:4px solid var(--color-border); }
    .fb-shot img { display:block; width:100%; height:100%; object-fit:cover; }
    .fb-shot-a { width:86%; height:94%; left:9%; top:0; }
    .fb-float { position:absolute; display:flex; align-items:center; gap:8px; background:var(--color-surface); border-radius:999px; padding:9px 16px 9px 10px; font-size:.78rem; font-weight:700; color:var(--color-text); box-shadow:0 10px 28px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); z-index:2; }
    .fb-float .fb-ico { width:30px; height:30px; border-radius:50%; display:grid; place-items:center; color:#111; background:transparent; border:1.5px solid #111; font-size:.95rem; flex-shrink:0; }
    .fb-f1 { left:0; top:0; } .fb-f1 .fb-ico { background:transparent; }
    .fb-f2 { right:0; bottom:1%; } .fb-f2 .fb-ico { background:transparent; }
    .fb-f3 { left:8%; bottom:0; } .fb-f3 .fb-ico { background:transparent; }
    .fb-f4 { right:0; top:14%; } .fb-f4 .fb-ico { background:transparent; }
    .fb-side { display:flex; align-items:center; justify-content:center; padding:56px 40px; background:var(--color-surface); }
    .fb-card { width:100%; max-width:420px; }
    .fb-card h2 { font-size:1.45rem; margin:0 0 22px; text-align:center; }
    .fb-field { margin-bottom:14px; }
    .fb-input-wrap { position:relative; }
    .fb-input-wrap > i { position:absolute; top:50%; left:20px; transform:translateY(-50%); color:var(--color-text-muted); font-size:1rem; }
    .fb-card input[type="email"], .fb-card input[type="password"], .fb-card input[type="text"] { width:100%; height:56px; border-radius:999px; border:1.5px solid var(--color-border); background:var(--color-surface); padding:12px 52px 12px 48px; font-size:.92rem; color:var(--color-text); outline:none; transition:border-color .2s, box-shadow .2s; }
    .fb-card input:focus { border-color:var(--color-secondary-text); box-shadow:0 0 0 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent); }
    .fb-card input:focus + i, .fb-input-wrap:focus-within > i { color:var(--color-secondary-text); }
    .fb-btn-primary { width:100%; height:54px; border-radius:999px; border:none; background:linear-gradient(135deg,var(--color-secondary-text),var(--color-secondary-text)); color:var(--color-on-solid); font-weight:700; font-size:1rem; cursor:pointer; box-shadow:0 8px 22px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); transition:all .2s; display:flex; align-items:center; justify-content:center; gap:10px; }
    .fb-btn-primary:hover { transform:translateY(-2px); box-shadow:0 12px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent); }
    .fb-btn-outline { width:100%; height:52px; border-radius:999px; background:var(--color-surface); border:1.5px solid var(--color-secondary-text); color:var(--color-secondary-text); font-weight:700; font-size:.95rem; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:all .2s; }
    .fb-btn-outline:hover { background:var(--color-secondary-soft); transform:translateY(-2px); }
    .fb-center { text-align:center; }
    .fb-forgot { display:inline-block; margin:16px 0 20px; font-size:.86rem; font-weight:600; color:var(--color-text); }
    .fb-forgot:hover { color:var(--color-secondary-text); }
    .fb-divider { display:flex; align-items:center; gap:12px; color:var(--color-text-muted); font-size:.75rem; margin:22px 0; }
    .fb-divider::before, .fb-divider::after { content:''; flex:1; height:1px; background:var(--color-border); }
    .fb-remember { display:flex; align-items:center; gap:9px; font-size:.82rem; color:var(--color-text-muted); margin:2px 0 18px; cursor:pointer; }
    .fb-remember input { accent-color:var(--color-secondary-text); width:16px; height:16px; }
    .fb-input-wrap .password-toggle { top:50%; transform:translateY(-50%); right:8px; }
    .fb-demo { margin-top:22px; border:1px dashed var(--color-border); border-radius:18px; padding:14px 16px; background:var(--color-bg); }
    .fb-demo summary { cursor:pointer; font-size:.8rem; font-weight:700; color:var(--color-text-muted); }
    .fb-demo .demo-options { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .fb-demo .demo-options button { background:var(--color-surface); border:1px solid var(--color-border); border-radius:999px; padding:7px 14px; font-size:.76rem; font-weight:600; color:var(--color-text-muted); cursor:pointer; transition:all .18s; }
    .fb-demo .demo-options button:hover { border-color:var(--color-secondary-text); color:var(--color-secondary-text); }
    @media (max-width:900px) {
        .fb-auth { grid-template-columns:1fr; }
        .fb-hero { padding:44px 28px 56px; }
        .fb-collage { height:340px; max-width:580px; margin-inline:auto; transform:none; }
        .fb-side { padding:36px 22px 56px; }
    }
</style>
@endpush

@section('content')
<div class="fb-auth">
    <aside class="fb-hero" aria-label="Care for every chapter">
        <h1>Care for the ones <span>you love.</span></h1>
        <div class="fb-collage" aria-hidden="true">
            <div class="fb-float fb-f1"><span class="fb-ico"><i class="bi bi-clipboard2-pulse"></i></span> Checkups on track</div>
            <div class="fb-shot fb-shot-a"><img src="{{ asset('images/maternal-care-bright.jpg') }}" alt=""></div>
            <div class="fb-float fb-f2"><span class="fb-ico"><i class="bi bi-alarm"></i></span> Reminders that care</div>
            <div class="fb-float fb-f3"><span class="fb-ico"><i class="bi bi-calendar-heart"></i></span> Period tracking</div>
            <div class="fb-float fb-f4"><span class="fb-ico"><i class="bi bi-journal-medical"></i></span> Health learning</div>
        </div>
    </aside>
    <section class="fb-side">
    <div class="fb-card" aria-labelledby="login-title">
        <a class="auth-back-plain" href="{{ route('home') }}" aria-label="Back to Home"><span aria-hidden="true">&larr;</span></a>
        <h2 id="login-title">Log into ReproCare</h2>
        @if ($errors->any())
            <div class="public-alert" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @if(session('success'))
            <div class="public-alert public-alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if(session('pending_registration'))
            <div class="public-alert public-alert-pending" role="status"><strong>Verification in progress</strong><br>RHU is verifying your account. You will receive an SMS once approved.</div>
        @endif
        @if(session('status'))
            <div class="public-alert public-alert-success" role="status">{{ session('status') }}</div>
        @endif
        <form id="loginForm" method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="fb-field">
                <div class="fb-input-wrap">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Email address" aria-label="Email address" autocomplete="email" required @if($errors->has('email')) aria-invalid="true" @endif>
                </div>
            </div>
            <div class="fb-field">
                <div class="fb-input-wrap">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    <input type="password" id="password" name="password" placeholder="Password" aria-label="Password" autocomplete="current-password" required>
                    <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" id="passwordToggleIcon" aria-hidden="true"></i></button>
                </div>
            </div>
            <label class="fb-remember"><input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}><span>Remember this device</span></label>
            <button type="submit" id="btnSubmitLogin" class="fb-btn-primary">Log in</button>
        </form>
        <div class="fb-center"><a class="fb-forgot" href="{{ route('password.request') }}">Forgot password?</a></div>
        <div class="fb-divider">new to ReproCare?</div>
        <a class="fb-btn-outline" href="{{ route('register') }}">Create new account</a>
        <details class="fb-demo">
            <summary>Demo access</summary>
            <div class="demo-options">
                <button type="button" data-demo-email="mariasanta@gmail.com">Patient</button>
                <button type="button" data-demo-email="midwife@reprocare.com">Midwife</button>
                <button type="button" data-demo-email="ana@gmail.com">BHW</button>
                <button type="button" data-demo-email="rhu@reprocare.com">RHU 1</button>
                <button type="button" data-demo-email="cho@reprocare.com">CHO</button>
                <button type="button" data-demo-email="pres@gmail.com">BHW President</button>
            </div>
            <p id="demoFeedback" role="status"></p>
        </details>
    </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('passwordToggle').addEventListener('click', function () {
        const input = document.getElementById('password');
        const showing = input.type === 'password';
        input.type = showing ? 'text' : 'password';
        this.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
        this.setAttribute('aria-pressed', String(showing));
        document.getElementById('passwordToggleIcon').className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
    document.querySelectorAll('[data-demo-email]').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('email').value = button.dataset.demoEmail;
            document.getElementById('password').value = 'password123';
            document.getElementById('demoFeedback').textContent = button.textContent + ' demo selected. Log in to continue.';
            document.getElementById('email').focus();
        });
    });
    // Modern loading feedback: disable + spinner while signing in (stops double-submit)
    document.getElementById('loginForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnSubmitLogin');
        btn.classList.add('is-loading');
        btn.disabled = true;
        btn.innerHTML = 'Signing in…';
    });
</script>
@endpush
