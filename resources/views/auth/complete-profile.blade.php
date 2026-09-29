@extends('layouts.public')

@section('title', 'Complete your profile — ReproCare')
@section('page-class', 'login-page')

@push('styles')
<style>
    .complete-wrap { display:flex; align-items:flex-start; justify-content:center; padding:56px 22px 64px; background:var(--color-bg); min-height:calc(100vh - 166px); }
    .complete-card { width:100%; max-width:480px; background:var(--color-surface); border:1px solid var(--color-border); border-radius:22px; padding:34px 32px; box-shadow:0 18px 48px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 14%, transparent); }
    .complete-card h2 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; letter-spacing:-.04em; margin:0 0 8px; text-align:center; }
    .complete-card p.lead { text-align:center; color:var(--color-text-muted); font-size:.9rem; margin:0 0 24px; line-height:1.6; }
    .complete-field { margin-bottom:16px; }
    .complete-field label { display:block; font-size:.82rem; font-weight:700; margin-bottom:7px; }
    .complete-field input, .complete-field select { width:100%; height:54px; border-radius:14px; border:1.5px solid var(--color-border); background:var(--color-surface); padding:12px 16px; font-size:.92rem; color:var(--color-text); outline:none; }
    .complete-field input:focus, .complete-field select:focus { border-color:var(--color-primary-text); box-shadow:0 0 0 4px color-mix(in srgb, var(--color-primary) 13%, transparent); }
    .complete-field .hint { font-size:.76rem; color:var(--color-text-muted); margin-top:6px; }
    .complete-error { font-size:.78rem; color:var(--color-danger-text, #b42318); margin-top:6px; }
</style>
@endpush

@section('content')
<div class="complete-wrap">
    <div class="complete-card" aria-labelledby="complete-title">
        <h2 id="complete-title">One last step</h2>
        <p class="lead">Welcome, {{ $user->first_name }}! Please add your <strong>phone number</strong> and <strong>barangay</strong> so your BHW and midwife can reach you.</p>

        @if ($errors->any())
            <div class="public-alert" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.complete.store') }}">
            @csrf

            <div class="complete-field">
                <label for="contact_number">Phone number</label>
                <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g. 09171234567" autocomplete="tel" required>
                <div class="hint">Used for checkup reminders and RHU approval updates via SMS.</div>
                @error('contact_number')<div class="complete-error">{{ $message }}</div>@enderror
            </div>

            <div class="complete-field">
                <label for="barangay">Barangay (San Carlos City)</label>
                <select id="barangay" name="barangay" required>
                    <option value="" disabled {{ old('barangay', $user->barangay) ? '' : 'selected' }}>Select your barangay</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay }}" {{ old('barangay', $user->barangay) === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                    @endforeach
                </select>
                @error('barangay')<div class="complete-error">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="fb-btn-primary" style="width:100%; height:54px; border-radius:16px; border:none; background:var(--color-primary); color:var(--color-primary-on); font-weight:700; font-size:1rem; cursor:pointer;">Complete profile</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:14px; text-align:center;">
            @csrf
            <button type="submit" style="background:none; border:none; color:var(--color-text-muted); font-size:.82rem; cursor:pointer; text-decoration:underline;">Sign out</button>
        </form>
    </div>
</div>
@endsection
