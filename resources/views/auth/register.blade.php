@extends('layouts.public')

@section('title', 'Create Account - ReproCare')
@section('page-class', 'signup-page')

@push('styles')

<style>
    /* ================================================================
       REPROCARE MULTI-STEP REGISTRATION
       Split-screen: Left = Beautiful visual panel | Right = Step form
       ================================================================ */

    /* Hide global nav/footer */
    body { padding-top:0 !important; }
    nav.navbar, footer.footer { display:none !important; }

    *, *::before, *::after { box-sizing:border-box; }

    :root {
   /* Soft Pink — portal palette */


        --primary-sub:color-mix(in srgb, var(--color-secondary) 10%, transparent);
   /* Portal Heading */

        --teal:var(--color-info-text);   /* Warm Teal */
        --teal-dark:var(--color-info-text);
        --teal-light:var(--color-info-soft);
        --green:var(--color-success-text);   /* Success green (was undefined) */
        --green-deep:var(--color-success-text);
        --green-dim:var(--color-success-soft);
        --green-light:var(--color-success-soft);
        --green-dark:var(--color-success-text);
        --pink:var(--color-secondary);   /* (was undefined) */
        --pink-light:var(--color-secondary-soft);
        --pink-dark:var(--color-secondary-text);
        --surface:var(--color-secondary-soft);
        --cream:var(--color-surface);
        --white:var(--color-surface);

        --text-mid:var(--color-text);
        --text-soft:var(--color-text-muted);
        --text-faint:var(--color-text-muted);


        --success-bg:var(--color-success-soft);

        --danger-bg:var(--color-danger-soft);
        --r-sm:8px;
        --r-md:14px;
        --r-lg:20px;
        --r-xl:28px;
        --r-full:9999px;
        --shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 9%, transparent);

    }

    html, body { height:100%; margin:0; font-family:'Inter', sans-serif; }

    /* ── OUTER WRAPPER ── */
    .reg-page {
        display:flex;
        min-height:100vh;
        background:var(--cream);
    }

    /* ══════════════════════════════════════
       LEFT PANEL — Beautiful visual side
    ══════════════════════════════════════ */
    .reg-left {
        width:42%;
        flex-shrink:0;
        position:sticky;
        top:0;
        height:100vh;
        background:url('/images/maternal-care-bright.jpg') center/cover no-repeat;
        display:flex;
        flex-direction:column;
        overflow:hidden;
    }

    /* Dark slate + soft pink overlay (matches login + portal accents) */
    .reg-left-overlay {
        position:absolute;
        inset:0;
        background:linear-gradient(
            150deg,
            color-mix(in srgb, var(--color-surface-strong) 92%, transparent) 0%,
            color-mix(in srgb, var(--color-surface-strong) 85%, transparent) 45%,
            color-mix(in srgb, var(--color-secondary-text) 78%, transparent) 100%
        );
    }

    .reg-left-content {
        position:relative;
        z-index:1;
        display:flex;
        flex-direction:column;
        height:100%;
        padding:2.5rem 2.75rem;
    }

    /* Brand top */
    .reg-brand {
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:auto;
    }
    .reg-brand img {
        width:36px; height:36px;
        object-fit:contain; border-radius:8px;
    }
    .reg-brand-name {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.35rem; font-weight:800;
        color:var(--color-on-solid); letter-spacing:-0.02em;
    }
    .reg-brand-name span {
        color:var(--color-secondary-text);
    }

    /* Step progress on left panel */
    .reg-step-nav {
        display:flex;
        flex-direction:column;
        gap:6px;
        margin-bottom:2.5rem;
    }

    .step-nav-item {
        display:flex;
        align-items:center;
        gap:14px;
        padding:10px 14px;
        border-radius:var(--r-md);
        transition:all 0.3s ease;
        cursor:default;
    }
    .step-nav-item.done   { background:color-mix(in srgb, var(--color-surface) 8%, transparent); }
    .step-nav-item.active { background:color-mix(in srgb, var(--color-secondary) 22%, transparent); border:1px solid color-mix(in srgb, var(--color-secondary) 40%, transparent); }

    .step-nav-circle {
        width:36px; height:36px;
        border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800; font-size:0.85rem;
        flex-shrink:0;
        transition:all 0.3s;
    }
    .step-nav-item.pending .step-nav-circle {
        background:color-mix(in srgb, var(--color-surface) 10%, transparent);
        color:color-mix(in srgb, var(--color-on-solid) 50%, transparent);
        border:1.5px solid color-mix(in srgb, var(--color-border) 20%, transparent);
    }
    .step-nav-item.active .step-nav-circle {
        background:var(--primary);
        color:var(--color-on-solid);
        box-shadow:0 4px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent);
    }
    .step-nav-item.done .step-nav-circle {
        background:var(--teal);
        color:var(--color-on-solid);
    }

    .step-nav-text { flex:1; }
    .step-nav-label {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.88rem; font-weight:700;
        color:color-mix(in srgb, var(--color-on-solid) 92%, transparent);
        display:block;
        line-height:1.2;
    }
    .step-nav-item.pending .step-nav-label { color:color-mix(in srgb, var(--color-on-solid) 45%, transparent); }
    .step-nav-sub {
        font-size:0.74rem;
        color:color-mix(in srgb, var(--color-on-solid) 65%, transparent);
        display:block;
        margin-top:1px;
    }

    /* Bottom tagline */
    .reg-left-tagline {
        border-top:1px solid color-mix(in srgb, var(--color-border) 14%, transparent);
        padding-top:1.5rem;
    }
    .reg-left-tagline p {
        font-size:0.85rem;
        color:color-mix(in srgb, var(--color-on-solid) 75%, transparent);
        line-height:1.65;
        font-style:italic;
    }
    .reg-left-tagline strong {
        color:var(--color-secondary-text);
        font-style:normal;
    }

    /* ══════════════════════════════════════
       RIGHT PANEL — Form area
    ══════════════════════════════════════ */
    .reg-right {
        flex:1;
        display:flex;
        flex-direction:column;
        min-height:100vh;
        overflow-y:auto;
    }

    .reg-right-inner {
        flex:1;
        display:flex;
        flex-direction:column;
        justify-content:center;
        padding:3rem 3.5rem;
        max-width:520px;
        width:100%;
        margin:0 auto;
    }

    /* Top breadcrumb / step counter */
    .step-counter {
        font-size:0.78rem;
        font-weight:700;
        color:var(--text-faint);
        text-transform:uppercase;
        letter-spacing:0.06em;
        margin-bottom:0.5rem;
    }
    .step-counter span { color:var(--green); }

    /* Step header */
    .step-head h2 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.7rem;
        font-weight:800;
        color:var(--green-deep);
        margin-bottom:0.4rem;
        line-height:1.2;
    }
    .step-head p {
        font-size:0.9rem;
        color:var(--text-soft);
        margin-bottom:2rem;
        line-height:1.6;
    }

    /* ── Progress bar (top of right panel) ── */
    .reg-progress-bar {
        height:4px;
        background:var(--border);
        border-radius:var(--r-full);
        margin-bottom:2.5rem;
        overflow:hidden;
    }
    .reg-progress-fill {
        height:100%;
        background:linear-gradient(90deg, var(--green), var(--pink));
        border-radius:var(--r-full);
        transition:width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── FORM FIELDS ── */
    .field-group {
        display:flex;
        flex-direction:column;
        gap:1.1rem;
        margin-bottom:1.75rem;
    }

    .field-row {
        display:flex;
        gap:14px;
    }
    .field-row > * { flex:1; }

    .field-item { display:flex; flex-direction:column; gap:5px; }

    .field-label {
        font-size:0.82rem;
        font-weight:700;
        color:var(--text-mid);
        display:flex; align-items:center; gap:6px;
    }
    .field-label .req { color:var(--danger); }

    .field-wrap {
        position:relative;
    }
    .field-wrap .f-icon {
        position:absolute;
        left:14px; top:50%;
        transform:translateY(-50%);
        color:var(--text-faint);
        font-size:0.95rem;
        pointer-events:none;
        transition:color 0.2s;
        z-index:2;
    }
    .field-wrap:focus-within .f-icon { color:var(--primary); }

    .field-wrap input,
    .field-wrap select,
    .field-wrap textarea {
        width:100%;
        height:48px;
        padding:0 14px 0 40px;
        background:var(--color-surface, #ffffff);
        border:1.5px solid var(--border);
        border-radius:var(--r-md);
        font-size:0.9rem;
        color:var(--text);
        font-family:'Inter', sans-serif;
        outline:none;
        transition:border-color 0.22s, box-shadow 0.22s;
        -webkit-appearance:none;
    }
    .field-wrap textarea {
        height:auto;
        padding-top:12px;
        padding-bottom:12px;
        resize:none;
    }
    .field-wrap input::placeholder,
    .field-wrap textarea::placeholder { color:var(--text-faint); }

    .field-wrap input:focus,
    .field-wrap select:focus,
    .field-wrap textarea:focus {
        border-color:var(--primary);
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent);
        background:var(--color-surface);
    }
    .field-wrap input.is-error,
    .field-wrap select.is-error { border-color:var(--danger); }

    /* No icon inputs */
    .field-wrap.no-icon input,
    .field-wrap.no-icon select { padding-left:14px; }

    /* ── SECTION SUB-LABELS ── */
    .sub-section {
        background:var(--cream);
        border:1px solid var(--border);
        border-radius:var(--r-md);
        padding:1.25rem;
        display:flex;
        flex-direction:column;
        gap:1rem;
    }
    .sub-section-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.82rem; font-weight:700;
        color:var(--green); display:flex; align-items:center; gap:6px;
        margin-bottom:-2px;
    }

    /* ── ID UPLOAD AREA ── */
    .id-upload-area {
        border:2px dashed var(--border);
        border-radius:var(--r-lg);
        padding:2rem;
        text-align:center;
        cursor:pointer;
        transition:all 0.25s;
        background:var(--cream);
        position:relative;
    }
    .id-upload-area:hover { border-color:var(--green-dim); background:var(--green-light); }
    .id-upload-area.has-file { border-color:var(--success); background:var(--success-bg); }
    .id-upload-area input[type="file"] {
        position:absolute; inset:0; opacity:0; cursor:pointer; z-index:1;
    }
    .id-upload-icon {
        width:52px; height:52px;
        border-radius:var(--r-md);
        background:var(--green-light);
        color:var(--green);
        display:flex; align-items:center; justify-content:center;
        font-size:1.4rem;
        margin:0 auto 0.75rem;
    }
    .id-upload-area h4 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.95rem; font-weight:700;
        color:var(--green-dark); margin-bottom:4px;
    }
    .id-upload-area p {
        font-size:0.8rem; color:var(--text-faint); margin:0;
    }
    #id-preview-front, #id-preview-back {
        width:100%; max-height:160px; object-fit:cover;
        border-radius:var(--r-sm); margin-top:12px;
        display:none;
        border:1px solid var(--border);
    }

    /* ── ID CAPTURE METHOD TOGGLE + CAMERA PANE ── */
    .id-mode-toggle {
        display:inline-flex;
        background:var(--cream);
        border:1px solid var(--border);
        border-radius:var(--r-full);
        padding:3px;
        gap:2px;
        margin-bottom:10px;
    }
    .id-mode-btn {
        border:none; background:transparent;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.8rem; font-weight:700;
        color:var(--text-mid);
        padding:7px 16px; border-radius:var(--r-full);
        cursor:pointer; transition:all 0.2s;
        display:inline-flex; align-items:center; gap:6px;
    }
    .id-mode-btn.active {
        background:var(--color-surface, #ffffff);
        color:var(--primary-dk);
        box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent);
    }
    .id-camera-pane {
        border:2px dashed var(--border);
        border-radius:var(--r-lg);
        padding:1.25rem;
        text-align:center;
        background:var(--color-surface-strong);
    }
    .id-camera-pane video {
        width:100%; max-height:260px;
        border-radius:var(--r-sm);
        background:var(--color-surface-strong);
        object-fit:cover;
    }
    .id-camera-pane video.captured { display:none; }
    .id-camera-actions {
        display:flex; gap:10px; justify-content:center;
        margin-top:12px; flex-wrap:wrap;
    }
    .btn-capture, .btn-retake {
        display:inline-flex; align-items:center; gap:8px;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700; font-size:0.88rem;
        padding:11px 24px; border-radius:var(--r-full);
        border:none; cursor:pointer; transition:all 0.2s;
    }
    .btn-capture {
        background:var(--color-surface-strong);
        border:1px solid var(--color-text);
        color:var(--color-on-solid);
        box-shadow:0 6px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
    }
    .btn-capture:hover { transform:translateY(-2px); }
    .btn-retake {
        background:color-mix(in srgb, var(--color-surface) 14%, transparent); color:var(--color-on-solid);
        border:1px solid color-mix(in srgb, var(--color-border) 30%, transparent);
    }
    .id-camera-hint {
        font-size:0.78rem; color:color-mix(in srgb, var(--color-on-solid) 75%, transparent);
        margin:10px 0 0;
    }
    .id-camera-fallback {
        margin-top:12px; padding:12px;
        background:color-mix(in srgb, var(--color-surface) 8%, transparent);
        border-radius:var(--r-sm);
        font-size:0.82rem; color:var(--color-on-solid);
    }
    .id-camera-fallback input { margin-top:8px; }
    .id-req-error {
        display:block;
        font-size:0.76rem; color:var(--danger);
        margin-top:6px; font-weight:600;
    }
    .id-upload-area.missing, .id-camera-pane.missing {
        border-color:var(--danger);
        animation:idMissingShake 0.4s ease;
    }
    @keyframes idMissingShake {
        0%, 100% { transform:translateX(0); }
        25% { transform:translateX(-6px); }
        50% { transform:translateX(6px); }
        75% { transform:translateX(-4px); }
    }

    /* ── PASSWORD STRENGTH ── */
    .strength-bar {
        height:4px; border-radius:var(--r-full);
        background:var(--border); overflow:hidden; margin-top:6px;
    }
    .strength-fill {
        height:100%; border-radius:var(--r-full);
        transition:width 0.3s, background 0.3s;
        width:0%;
        background:var(--danger);
    }
    .strength-label {
        font-size:0.74rem; color:var(--text-faint);
        margin-top:4px; display:flex; justify-content:space-between;
    }

    /* ── STEP BUTTONS ── */
    .step-btns {
        display:flex;
        gap:12px;
        margin-top:0.5rem;
    }

    .btn-next, .btn-prev, .btn-submit {
        display:inline-flex; align-items:center; gap:8px;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700; font-size:0.95rem;
        padding:13px 28px; border-radius:var(--r-full);
        border:none; cursor:pointer; transition:all 0.22s ease;
    }
    .btn-next, .btn-submit {
        background:linear-gradient(135deg, var(--color-secondary-text) 0%, var(--color-secondary-text) 100%);
        border:1px solid var(--color-secondary-text);
        color:var(--color-on-solid);
        flex:1;
        justify-content:center;
        box-shadow:0 6px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
    }
    .btn-next:hover, .btn-submit:hover {
        background:linear-gradient(135deg, var(--color-secondary-text) 0%, var(--color-secondary-text) 100%);
        border-color:var(--color-secondary-text);
        transform:translateY(-2px);
        box-shadow:0 10px 28px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent);
    }
    .btn-prev {
        background:var(--color-surface, #ffffff);
        color:var(--text-mid);
        border:1.5px solid var(--border);
        padding:13px 20px;
    }
    .btn-prev:hover { background:var(--color-secondary-soft); border-color:var(--color-secondary-text); color:var(--color-secondary-text); transform:translateY(-1px); }

    /* Step-level error summary: tells the user WHY Next didn't advance */
    .step-err-summary {
        display:none;
        background:var(--color-danger-soft);
        border:1px solid color-mix(in srgb, var(--color-danger) 35%, transparent);
        color:var(--color-secondary-text);
        border-radius:12px;
        padding:0.7rem 1rem;
        font-size:0.82rem;
        font-weight:600;
        margin-bottom:1rem;
    }
    .step-err-summary.show { display:flex; align-items:center; gap:8px; }
    .field-err-msg { font-size:0.74rem; color:var(--color-danger-text); margin-top:2px; display:block; font-weight:600; }
    .is-error { border-color:var(--color-danger-text) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent) !important; }

    /* ── BOTTOM LINK ── */
    .reg-signin-link {
        text-align:center;
        margin-top:1.75rem;
        font-size:0.86rem;
        color:var(--text-faint);
    }
    .reg-signin-link a { color:var(--primary); font-weight:700; }
    .reg-signin-link a:hover { color:var(--primary-dk); }

    /* ── ERROR ALERT ── */
    .error-box {
        background:var(--danger-bg);
        border:1px solid color-mix(in srgb, var(--color-danger) 25%, transparent);
        border-radius:var(--r-md);
        padding:0.9rem 1.1rem;
        display:flex; align-items:flex-start; gap:10px;
        margin-bottom:1.25rem;
        font-size:0.85rem; color:var(--danger);
    }
    .error-box i { flex-shrink:0; margin-top:1px; }

    /* Step panels */
    .step-panel { display:none; }
    .step-panel.active { display:block; animation:slideIn 0.35s ease both; }

    @keyframes slideIn {
        from { opacity:0; transform:translateX(18px); }
        to   { opacity:1; transform:translateX(0); }
    }
    @keyframes slideOut {
        from { opacity:1; transform:translateX(0); }
        to   { opacity:0; transform:translateX(-18px); }
    }

    /* Field focus color uses primary */
    .field-wrap:focus-within .f-icon { color:var(--primary) !important; }
    .field-wrap input:focus,
    .field-wrap select:focus,
    .field-wrap textarea:focus {
        border-color:var(--primary) !important;
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent) !important;
    }
    .field-wrap input.is-error,
    .field-wrap select.is-error,
    .field-wrap textarea.is-error {
        border-color:var(--danger) !important;
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent) !important;
    }
    .field-err-msg {
        display:flex;
        align-items:center;
        gap:4px;
        font-size:0.75rem;
        color:var(--danger);
        margin-top:4px;
        animation:fadeInUp 0.2s ease;
    }
    .field-err-msg::before {
        content:'⚠';
        font-size:0.7rem;
    }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(4px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* Barangay Searchable & Scrollable Dropdown */
    .brgy-combobox-wrap {
        position:relative;
    }
    .brgy-dropdown-toggle {
        position:absolute;
        right:0.85rem;
        top:50%;
        transform:translateY(-50%);
        background:transparent;
        border:none;
        color:var(--text-soft);
        cursor:pointer;
        padding:0.35rem 0.5rem;
        border-radius:6px;
        display:flex;
        align-items:center;
        justify-content:center;
        transition:transform 0.2s, color 0.2s;
        z-index:3;
    }
    .brgy-dropdown-toggle:hover {
        color:var(--primary);
    }
    .brgy-dropdown-toggle.open #brgyArrowIcon {
        transform:rotate(180deg);
    }
    .brgy-dropdown-list {
        position:absolute;
        top:calc(100% + 6px);
        left:0;
        right:0;
        /* NOTE: was background:var(--var(--color-surface)) — nested var()
           is invalid CSS, so the declaration was dropped and the open panel
           rendered transparent over the fields below. */
        background:var(--color-surface, #ffffff);
        border:1.5px solid var(--color-border, #e5e7eb);
        border-radius:var(--r-md);
        box-shadow:0 14px 36px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent);
        z-index:1000;
        overflow:hidden;
    }
    .brgy-list-header {
        padding:0.6rem 0.95rem;
        background:var(--surface);
        border-bottom:1px solid var(--border);
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-size:0.75rem;
        font-weight:700;
        color:var(--text-soft);
        text-transform:uppercase;
        letter-spacing:0.5px;
    }
    .brgy-list-header .badge-count {
        background:var(--primary-sub);
        color:var(--primary-dk);
        padding:2px 8px;
        border-radius:999px;
        font-size:0.72rem;
        font-weight:700;
    }
    .brgy-scroll-container {
        max-height:240px;
        overflow-y:auto;
        overscroll-behavior:contain;
    }
    .brgy-scroll-container::-webkit-scrollbar {
        width:6px;
    }
    .brgy-scroll-container::-webkit-scrollbar-thumb {
        background:var(--color-primary-soft);
        border-radius:3px;
    }
    .brgy-option-item {
        padding:0.65rem 0.95rem;
        font-size:0.86rem;
        font-weight:500;
        color:var(--text);
        cursor:pointer;
        display:flex;
        align-items:center;
        gap:0.65rem;
        border-bottom:1px solid color-mix(in srgb, var(--color-primary-soft) 50%, transparent);
        transition:background 0.15s, color 0.15s;
    }
    .brgy-option-item i {
        color:var(--primary);
        font-size:0.95rem;
        flex-shrink:0;
    }
    .brgy-option-item:hover, .brgy-option-item.highlighted {
        background:var(--surface);
        color:var(--primary-dk);
    }
    .brgy-option-item.selected {
        background:var(--primary-sub);
        color:var(--primary-dk);
        font-weight:700;
    }
    .brgy-option-item.hidden {
        display:none !important;
    }
    .brgy-empty-state {
        padding:1.25rem 1rem;
        text-align:center;
        color:var(--text-faint);
        font-size:0.84rem;
    }


    /* ── Facebook-style centered single column (system rose palette) ── */
    .reg-page { display:block; background:var(--color-bg); }
    .reg-left { display:none; }
    .reg-right { min-height:auto; overflow-y:visible; }
    .reg-right-inner { max-width:680px; padding:2.5rem 2rem 4rem; }
    .fb-reg-top { margin-bottom:1.75rem; }
    .fb-back { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1.5px solid var(--color-border, #e5e7eb); background:var(--color-surface, #ffffff); color:var(--text-mid); font-size:1.1rem; cursor:pointer; text-decoration:none; transition:all .2s; margin-bottom:1.25rem; }
    .fb-back:hover { border-color:var(--pink-dark); color:var(--pink-dark); transform:translateX(-2px); }
    .fb-reg-top h1 { margin-top:0.25rem; }
    .fb-reg-top h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:clamp(1.6rem,3.4vw,2.1rem); font-weight:800; color:var(--text); margin:0 0 .4rem; letter-spacing:-0.02em; }
    .fb-reg-top h1 span { color:var(--pink-dark); }
    .fb-reg-top p { font-size:.92rem; color:var(--text-soft); margin:0; }
    /* Pill inputs like Facebook signup */
    .field-wrap input,
    .field-wrap select,
    .field-wrap textarea { border-radius:999px; height:54px; border-color:var(--color-border); }
    .field-wrap textarea { height:auto; min-height:96px; border-radius:22px; padding-top:14px; }
    .field-wrap input:focus,
    .field-wrap select:focus,
    .field-wrap textarea:focus { border-color:var(--pink-dark); box-shadow:0 0 0 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent); }
    .field-wrap:focus-within .f-icon { color:var(--pink-dark); }
    .reg-progress-fill { background:linear-gradient(90deg, var(--pink), var(--pink-dark)); }

    /* Responsive */
    @media (max-width: 820px) {
        .reg-left { display:none; }
        .reg-right-inner { padding:2.5rem 1.75rem; max-width:100%; }
    }
    @media (max-width: 480px) {
        .reg-right-inner { padding:2rem 1.25rem; }
        .field-row { flex-direction:column; gap:1.1rem; }
    }
</style>
@endpush

@section('content')
<div class="reg-page">

    <!-- ════════════════════════════════
         LEFT VISUAL PANEL
    ════════════════════════════════ -->
    <div class="reg-left" id="regLeft">
        <div class="reg-left-overlay"></div>
        <div class="reg-left-content">

            <!-- Brand -->
            <div class="signup-intro">
                <div class="eyebrow">Your care starts here</div>
                <h1>A little about you.<br>A better way to care.</h1>
                <p>Let's connect you with your community health team. We'll guide you through each step.</p>
            </div>

            <!-- Step nav list -->
            <div class="reg-step-nav" id="leftStepNav">
                <div class="step-nav-item active" data-nav="1" aria-current="step">
                    <div class="step-nav-circle"><i class="bi bi-envelope-fill" aria-hidden="true"></i></div>
                    <div class="step-nav-text">
                        <span class="step-nav-label">Account Setup</span>
                        <span class="step-nav-sub">Email &amp; password</span>
                    </div>
                </div>
                <div class="step-nav-item pending" data-nav="2">
                    <div class="step-nav-circle"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
                    <div class="step-nav-text">
                        <span class="step-nav-label">Personal Info</span>
                        <span class="step-nav-sub">Name, birthdate, contact</span>
                    </div>
                </div>
                <div class="step-nav-item pending" data-nav="3">
                    <div class="step-nav-circle"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
                    <div class="step-nav-text">
                        <span class="step-nav-label">Location</span>
                        <span class="step-nav-sub">Barangay &amp; purok</span>
                    </div>
                </div>
                <div class="step-nav-item pending" data-nav="4">
                    <div class="step-nav-circle"><i class="bi bi-people-fill" aria-hidden="true"></i></div>
                    <div class="step-nav-text">
                        <span class="step-nav-label">Emergency Contacts</span>
                        <span class="step-nav-sub">Up to 3 contacts</span>
                    </div>
                </div>
                <div class="step-nav-item pending" data-nav="5">
                    <div class="step-nav-circle"><i class="bi bi-card-image" aria-hidden="true"></i></div>
                    <div class="step-nav-text">
                        <span class="step-nav-label">Valid ID</span>
                        <span class="step-nav-sub">Front &amp; back photo</span>
                    </div>
                </div>
            </div>

            <!-- Tagline -->
            <div class="reg-left-tagline">
                <p><strong><i class="bi bi-card-checklist" aria-hidden="true"></i> A few things to have ready</strong>Your contact details, an emergency contact, and photos of the front and back of a valid ID.</p>
            </div>

        </div>
    </div>


    <!-- ════════════════════════════════
         RIGHT FORM PANEL
    ════════════════════════════════ -->
    <div class="reg-right">
        <div class="reg-right-inner">

            <!-- Facebook-style header -->
            <div class="fb-reg-top">
                <a class="fb-back" href="{{ route('home') }}" aria-label="Back to home"><i class="bi bi-arrow-left" aria-hidden="true"></i></a>
                <h1>Get started with <span>ReproCare</span></h1>
                <p>Create one account for checkups, cycle tracking, and your community care team.</p>
            </div>

            <!-- Progress bar -->
            <div class="reg-progress-bar" role="progressbar" aria-label="Registration progress" aria-valuemin="0" aria-valuemax="5" aria-valuenow="1" id="registrationProgress">
                <div class="reg-progress-fill" id="progressFill" style="width:20%"></div>
            </div>

            <!-- Global error display (server-side) -->
            @if ($errors->any())
            <div class="error-box" role="alert">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" id="regForm" enctype="multipart/form-data">
                @csrf

                <!-- Hidden fields that get populated -->
                <input type="hidden" id="id_image_data_front" name="id_image_data_front">
                <input type="hidden" id="id_image_data_back" name="id_image_data_back">


                <!-- ══════════════════════════
                     STEP 1 — Account Setup
                ══════════════════════════ -->
                <div class="step-panel active" id="step-1">
                    <div class="step-counter">Step <span>1</span> of 5</div>
                    <div class="step-head">
                        <h2>Let's set up your account</h2>
                        <p>Start with your email and create a secure password. This is what you'll use to log in.</p>
                    </div>

                    <div class="field-group">
                        <div class="field-item">
                            <label class="field-label" for="email">Email Address <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-envelope f-icon" aria-hidden="true"></i>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email') }}"
                                    placeholder="your@email.com"
                                    autocomplete="email" required>
                            </div>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="password">Password <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-lock f-icon" aria-hidden="true"></i>
                                <input type="password" name="password" id="password"
                                    placeholder="At least 8 characters"
                                    autocomplete="new-password" required
                                    oninput="checkStrength(this.value)">
                            </div>
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-label">
                                <span id="strengthText">Password strength</span>
                                <span id="strengthTip">Use numbers &amp; symbols</span>
                            </div>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-lock-fill f-icon" aria-hidden="true"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="Repeat your password"
                                    autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>

                    <div class="step-btns">
                        <button type="button" class="btn-next" onclick="goStep(2)">
                            Continue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>


                <!-- ══════════════════════════
                     STEP 2 — Personal Info
                ══════════════════════════ -->
                <div class="step-panel" id="step-2">
                    <div class="step-counter">Step <span>2</span> of 5</div>
                    <div class="step-head">
                        <h2>Tell us about yourself</h2>
                        <p>Your personal details help us connect you with the right health workers in your area.</p>
                    </div>

                    <div class="field-group">
                        <div class="field-row">
                            <div class="field-item">
                                <label class="field-label" for="first_name">First Name <span class="req">*</span></label>
                                <div class="field-wrap">
                                    <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                    <input type="text" name="first_name" id="first_name"
                                        value="{{ old('first_name') }}"
                                        placeholder="First name" required
                                        autocomplete="given-name">
                                </div>
                            </div>
                            <div class="field-item" style="max-width:110px;">
                                <label class="field-label" for="middle_initial">M.I.</label>
                                <div class="field-wrap">
                                    <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                    <input type="text" name="middle_initial" id="middle_initial"
                                        value="{{ old('middle_initial') }}"
                                        placeholder="M.I." maxlength="2"
                                        autocomplete="additional-name">
                                </div>
                            </div>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="last_name">Last Name <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                <input type="text" name="last_name" id="last_name"
                                    value="{{ old('last_name') }}"
                                    placeholder="Last name" required
                                    autocomplete="family-name">
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-item">
                                <label class="field-label" for="date_of_birth">Date of Birth <span class="req">*</span></label>
                                <div class="field-wrap">
                                    <i class="bi bi-calendar-heart f-icon" aria-hidden="true"></i>
                                    <input type="date" name="date_of_birth" id="date_of_birth"
                                        value="{{ old('date_of_birth') }}"
                                        max="{{ now()->subDay()->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="contact_number">Phone Number</label>
                                <div class="field-wrap">
                                    <i class="bi bi-telephone f-icon" aria-hidden="true"></i>
                                    <input type="text" name="contact_number" id="contact_number"
                                        value="{{ old('contact_number') }}"
                                        placeholder="09XXXXXXXXX"
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="partner_name">Partner / Spouse Name <span style="color:var(--text-faint);font-weight:500;">(Optional)</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-person-hearts f-icon" aria-hidden="true"></i>
                                <input type="text" name="partner_name"
                                    value="{{ old('partner_name') }}"
                                    placeholder="Full name"
                                    autocomplete="off" id="partner_name">
                            </div>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="partner_contact">Partner Phone <span style="color:var(--text-faint);font-weight:500;">(Optional)</span></label>
                            <div class="field-wrap">
                                <i class="bi bi-telephone-plus f-icon" aria-hidden="true"></i>
                                <input type="text" name="partner_contact"
                                    value="{{ old('partner_contact') }}"
                                    placeholder="09XXXXXXXXX"
                                    autocomplete="off" id="partner_contact">
                            </div>
                        </div>
                    </div>

                    <div class="step-btns">
                        <button type="button" class="btn-prev" aria-label="Previous step" onclick="goStep(1)">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn-next" onclick="goStep(3)">
                            Continue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>


                <!-- ══════════════════════════
                     STEP 3 — Location
                ══════════════════════════ -->
                <div class="step-panel" id="step-3">
                    <div class="step-counter">Step <span>3</span> of 5</div>
                    <div class="step-head">
                        <h2>Where do you live?</h2>
                        <p>Your location helps us assign your Barangay Health Worker and RHU midwife.</p>
                    </div>

                    <div class="field-group">
                        <div class="field-item">
                            <label class="field-label" for="barangayInput">
                                <i class="bi bi-geo-alt-fill me-1" aria-hidden="true" style="color:var(--primary);"></i>
                                Barangay (San Carlos City, Pangasinan) <span class="req">*</span>
                            </label>
                            <div class="brgy-combobox-wrap position-relative">
                                <div class="field-wrap">
                                    <i class="bi bi-search f-icon" aria-hidden="true"></i>
                                    <input type="text" id="barangayInput" name="barangay"
                                           value="{{ old('barangay') }}"
                                           placeholder="Type to search or scroll to select your Barangay..."
                                           autocomplete="off" required>
                                    <button type="button" class="brgy-dropdown-toggle" id="brgyDropdownToggle" title="Click to view all {{ count($barangays ?? []) }} San Carlos City barangays">
                                        <i class="bi bi-chevron-down" aria-hidden="true" id="brgyArrowIcon"></i>
                                    </button>
                                </div>
                                <div class="brgy-dropdown-list" id="brgyDropdownList" style="display:none;">
                                    <div class="brgy-list-header">
                                        <span><i class="bi bi-pin-map-fill me-1" aria-hidden="true"></i> San Carlos City Barangays</span>
                                        <span class="badge-count" id="brgyMatchCount">{{ count($barangays ?? []) }}</span>
                                    </div>
                                    <div class="brgy-scroll-container" id="brgyScrollContainer">
                                        @foreach($barangays as $b)
                                            <div class="brgy-option-item" data-value="{{ $b }}">
                                                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                                <span>{{ $b }}</span>
                                            </div>
                                        @endforeach
                                        <div class="brgy-empty-state" id="brgyEmptyState" style="display:none;">
                                            <i class="bi bi-search me-1" aria-hidden="true"></i> No matching barangay found in San Carlos City
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span style="font-size:0.75rem;color:var(--text-faint);margin-top:4px;display:block;">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Type to search or scroll to pick from all {{ count($barangays ?? []) }} San Carlos City barangays.
                            </span>
                        </div>

                        {{-- House Number --}}
                        <div class="field-item mt-3">
                            <label class="field-label" for="house_number">
                                <i class="bi bi-house-door-fill me-1" aria-hidden="true" style="color:var(--primary);"></i>
                                House / Unit No. <span style="color:var(--text-faint);font-weight:500;">(Optional)</span>
                            </label>
                            <div class="field-wrap">
                                <i class="bi bi-hash f-icon" aria-hidden="true"></i>
                                <input type="text" name="house_number"
                                       value="{{ old('house_number') }}"
                                       placeholder="e.g. 123, Unit 4B, Lot 7"
                                       autocomplete="off" id="house_number">
                            </div>
                        </div>

                        {{-- Purok --}}
                        <div class="field-item mt-2">
                            <label class="field-label" for="purok">
                                <i class="bi bi-pin-map-fill me-1" aria-hidden="true" style="color:var(--primary);"></i>
                                Purok <span style="color:var(--text-faint);font-weight:500;">(Optional)</span>
                            </label>
                            <div class="field-wrap">
                                <i class="bi bi-geo-alt f-icon" aria-hidden="true"></i>
                                <input type="text" name="purok"
                                       value="{{ old('purok') }}"
                                       placeholder="e.g. Purok 1, Purok Centro, Purok Ilang-Ilang"
                                       autocomplete="off" id="purok">
                            </div>
                            <span style="font-size:0.75rem;color:var(--text-faint);margin-top:4px;display:block;">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Enter your purok name or number if applicable.
                            </span>
                        </div>

                        {{-- Sitio / Street --}}
                        <div class="field-item mt-2">
                            <label class="field-label" for="sitio">
                                <i class="bi bi-signpost-2-fill me-1" aria-hidden="true" style="color:var(--primary);"></i>
                                Sitio / Street <span style="color:var(--text-faint);font-weight:500;">(Optional)</span>
                            </label>
                            <div class="field-wrap">
                                <i class="bi bi-geo f-icon" aria-hidden="true"></i>
                                <input type="text" name="sitio"
                                       value="{{ old('sitio') }}"
                                       placeholder="e.g. Sitio Malaya, Rizal St."
                                       autocomplete="off" id="sitio">
                            </div>
                            <span style="font-size:0.75rem;color:var(--text-faint);margin-top:4px;display:block;">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Enter your sitio, street name, or landmark near your home.
                            </span>
                        </div>
                    </div>

                    <div class="step-btns">
                        <button type="button" class="btn-prev" aria-label="Previous step" onclick="goStep(2)">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn-next" onclick="goStep(4)">
                            Continue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>


                <!-- ══════════════════════════
                     STEP 4 — Emergency Contacts
                ══════════════════════════ -->
                <div class="step-panel" id="step-4">
                    <div class="step-counter">Step <span>4</span> of 5</div>
                    <div class="step-head">
                        <h2>Emergency contacts</h2>
                        <p>Who should we contact in case of an emergency? Add up to 3 people you trust.</p>
                    </div>

                    <div class="field-group">

                        <!-- Contact 1 -->
                        <div class="sub-section">
                            <div class="sub-section-title">
                                <i class="bi bi-1-circle-fill" aria-hidden="true"></i> Primary Contact <span style="color:var(--danger);font-size:0.75rem;">Required</span>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_name_1">Full Name <span class="req">*</span></label>
                                <div class="field-wrap">
                                    <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_name_1" value="{{ old('emergency_name_1') }}" placeholder="Contact's full name" required id="emergency_name_1">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-item">
                                    <label class="field-label" for="emergency_relationship_1">Relationship <span class="req">*</span></label>
                                    <div class="field-wrap">
                                        <i class="bi bi-heart f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_relationship_1" value="{{ old('emergency_relationship_1') }}" placeholder="Mother, Spouse…" required id="emergency_relationship_1">
                                    </div>
                                </div>
                                <div class="field-item">
                                    <label class="field-label" for="emergency_contact_number_1">Contact No. <span class="req">*</span></label>
                                    <div class="field-wrap">
                                        <i class="bi bi-telephone f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_contact_number_1" value="{{ old('emergency_contact_number_1') }}" placeholder="09XXXXXXXXX" required id="emergency_contact_number_1">
                                    </div>
                                </div>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_address_1">Address</label>
                                <div class="field-wrap">
                                    <i class="bi bi-geo-alt f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_address_1" value="{{ old('emergency_address_1') }}" placeholder="Address (optional)" id="emergency_address_1">
                                </div>
                            </div>
                        </div>

                        <!-- Contact 2 -->
                        <div class="sub-section">
                            <div class="sub-section-title" style="color:var(--text-soft);">
                                <i class="bi bi-2-circle-fill" aria-hidden="true"></i> Secondary Contact <span style="font-weight:500;color:var(--text-faint);">(Optional)</span>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_name_2">Full Name</label>
                                <div class="field-wrap">
                                    <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_name_2" value="{{ old('emergency_name_2') }}" placeholder="Contact's full name" id="emergency_name_2">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-item">
                                    <label class="field-label" for="emergency_relationship_2">Relationship</label>
                                    <div class="field-wrap">
                                        <i class="bi bi-heart f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_relationship_2" value="{{ old('emergency_relationship_2') }}" placeholder="Sibling, Friend…" id="emergency_relationship_2">
                                    </div>
                                </div>
                                <div class="field-item">
                                    <label class="field-label" for="emergency_contact_number_2">Contact No.</label>
                                    <div class="field-wrap">
                                        <i class="bi bi-telephone f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_contact_number_2" value="{{ old('emergency_contact_number_2') }}" placeholder="09XXXXXXXXX" id="emergency_contact_number_2">
                                    </div>
                                </div>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_address_2">Address</label>
                                <div class="field-wrap">
                                    <i class="bi bi-geo-alt f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_address_2" value="{{ old('emergency_address_2') }}" placeholder="Address (optional)" id="emergency_address_2">
                                </div>
                            </div>
                        </div>

                        <!-- Contact 3 -->
                        <div class="sub-section">
                            <div class="sub-section-title" style="color:var(--text-soft);">
                                <i class="bi bi-3-circle-fill" aria-hidden="true"></i> Tertiary Contact <span style="font-weight:500;color:var(--text-faint);">(Optional)</span>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_name_3">Full Name</label>
                                <div class="field-wrap">
                                    <i class="bi bi-person f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_name_3" value="{{ old('emergency_name_3') }}" placeholder="Contact's full name" id="emergency_name_3">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-item">
                                    <label class="field-label" for="emergency_relationship_3">Relationship</label>
                                    <div class="field-wrap">
                                        <i class="bi bi-heart f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_relationship_3" value="{{ old('emergency_relationship_3') }}" placeholder="Neighbor, Relative…" id="emergency_relationship_3">
                                    </div>
                                </div>
                                <div class="field-item">
                                    <label class="field-label" for="emergency_contact_number_3">Contact No.</label>
                                    <div class="field-wrap">
                                        <i class="bi bi-telephone f-icon" aria-hidden="true"></i>
                                        <input type="text" name="emergency_contact_number_3" value="{{ old('emergency_contact_number_3') }}" placeholder="09XXXXXXXXX" id="emergency_contact_number_3">
                                    </div>
                                </div>
                            </div>
                            <div class="field-item">
                                <label class="field-label" for="emergency_address_3">Address</label>
                                <div class="field-wrap">
                                    <i class="bi bi-geo-alt f-icon" aria-hidden="true"></i>
                                    <input type="text" name="emergency_address_3" value="{{ old('emergency_address_3') }}" placeholder="Address (optional)" id="emergency_address_3">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="step-btns">
                        <button type="button" class="btn-prev" aria-label="Previous step" onclick="goStep(3)">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn-next" onclick="goStep(5)">
                            Continue <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>


                <!-- ══════════════════════════
                     STEP 5 — Valid ID
                ══════════════════════════ -->
                <div class="step-panel" id="step-5">
                    <div class="step-counter">Step <span>5</span> of 5</div>
                    <div class="step-head">
                        <h2>Upload your valid ID</h2>
                        <p>We need both sides of a government-issued ID to verify your identity. Accepted: PhilSys, Passport, UMID, Driver's License.</p>
                    </div>

                    <div class="field-group">
                        <!-- Front of ID -->
                        <div class="field-item">
                            <label class="field-label" for="id-front-file"><i class="bi bi-card-front" aria-hidden="true"></i> Front of ID <span class="req">*</span></label>
                            <div class="id-mode-toggle" role="tablist" aria-label="Front ID capture method">
                                <button type="button" class="id-mode-btn active" id="frontModeUpload" onclick="setIdMode('front','upload')">
                                    <i class="bi bi-cloud-upload" aria-hidden="true"></i> Upload file
                                </button>
                                <button type="button" class="id-mode-btn" id="frontModeCamera" onclick="setIdMode('front','camera')">
                                    <i class="bi bi-camera" aria-hidden="true"></i> Use camera
                                </button>
                            </div>
                            <div class="id-upload-area" id="frontUploadArea">
                                <input type="file" accept="image/*" id="id-front-file"
                                    onchange="handleIdUpload(this, 'front')">
                                <div class="id-upload-icon" id="frontIcon">
                                    <i class="bi bi-card-image" aria-hidden="true"></i>
                                </div>
                                <h4 id="frontTitle">Click or tap to upload</h4>
                                <p id="frontDesc">Front side of your valid ID · JPG, PNG · Max 5MB</p>
                                <img id="id-preview-front" src="" alt="ID Front Preview">
                            </div>
                            <div class="id-camera-pane" id="frontCameraPane" style="display:none;">
                                <video id="frontVideo" playsinline muted></video>
                                <canvas id="frontCanvas" style="display:none;"></canvas>
                                <div class="id-camera-actions">
                                    <button type="button" class="btn-capture" id="frontCaptureBtn" onclick="captureIdPhoto('front')">
                                        <i class="bi bi-camera-fill" aria-hidden="true"></i> Capture photo
                                    </button>
                                    <button type="button" class="btn-retake" id="frontRetakeBtn" onclick="retakeIdPhoto('front')" style="display:none;">
                                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Retake
                                    </button>
                                </div>
                                <p class="id-camera-hint" id="frontCameraHint">Point your camera at the front of your ID, then capture.</p>
                                <div class="id-camera-fallback" id="frontCameraFallback" style="display:none;">
                                    <p><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Camera unavailable — take a photo instead:</p>
                                    <input type="file" accept="image/*" capture="environment" class="form-control"
                                        onchange="handleIdUpload(this, 'front')">
                                </div>
                            </div>
                            <span class="id-req-error" id="frontIdError" style="display:none;">
                                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Front of ID is required — upload a file or capture with your camera.
                            </span>
                        </div>

                        <!-- Back of ID -->
                        <div class="field-item">
                            <label class="field-label" for="id-back-file"><i class="bi bi-card-back" aria-hidden="true"></i> Back of ID <span class="req">*</span></label>
                            <div class="id-mode-toggle" role="tablist" aria-label="Back ID capture method">
                                <button type="button" class="id-mode-btn active" id="backModeUpload" onclick="setIdMode('back','upload')">
                                    <i class="bi bi-cloud-upload" aria-hidden="true"></i> Upload file
                                </button>
                                <button type="button" class="id-mode-btn" id="backModeCamera" onclick="setIdMode('back','camera')">
                                    <i class="bi bi-camera" aria-hidden="true"></i> Use camera
                                </button>
                            </div>
                            <div class="id-upload-area" id="backUploadArea">
                                <input type="file" accept="image/*" id="id-back-file"
                                    onchange="handleIdUpload(this, 'back')">
                                <div class="id-upload-icon" id="backIcon" style="background:var(--pink-light);color:var(--pink-dark);">
                                    <i class="bi bi-card-image" aria-hidden="true"></i>
                                </div>
                                <h4 id="backTitle">Click or tap to upload</h4>
                                <p id="backDesc">Back side of your valid ID · JPG, PNG · Max 5MB</p>
                                <img id="id-preview-back" src="" alt="ID Back Preview">
                            </div>
                            <div class="id-camera-pane" id="backCameraPane" style="display:none;">
                                <video id="backVideo" playsinline muted></video>
                                <canvas id="backCanvas" style="display:none;"></canvas>
                                <div class="id-camera-actions">
                                    <button type="button" class="btn-capture" id="backCaptureBtn" onclick="captureIdPhoto('back')">
                                        <i class="bi bi-camera-fill" aria-hidden="true"></i> Capture photo
                                    </button>
                                    <button type="button" class="btn-retake" id="backRetakeBtn" onclick="retakeIdPhoto('back')" style="display:none;">
                                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Retake
                                    </button>
                                </div>
                                <p class="id-camera-hint" id="backCameraHint">Point your camera at the back of your ID, then capture.</p>
                                <div class="id-camera-fallback" id="backCameraFallback" style="display:none;">
                                    <p><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Camera unavailable — take a photo instead:</p>
                                    <input type="file" accept="image/*" capture="environment" class="form-control"
                                        onchange="handleIdUpload(this, 'back')">
                                </div>
                            </div>
                            <span class="id-req-error" id="backIdError" style="display:none;">
                                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Back of ID is required — upload a file or capture with your camera.
                            </span>
                        </div>
                    </div>

                    <div class="step-btns">
                        <button type="button" class="btn-prev" aria-label="Previous step" onclick="stopAllIdCameras();goStep(4)">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-person-check-fill" aria-hidden="true"></i> Create My Account
                        </button>
                    </div>

                    <div style="margin-top:1rem;font-size:0.78rem;color:var(--text-faint);display:flex;align-items:center;gap:6px;">
                        <i class="bi bi-shield-lock-fill" aria-hidden="true" style="color:var(--success);"></i>
                        Your ID will be reviewed by your community health team to verify your registration.
                    </div>
                </div>

            </form>

            <div class="reg-signin-link">
                Already have an account? <a href="{{ route('login') }}">Log in here</a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Step management ──
    let currentStep = {{ $errors->any() ? 1 : 1 }};
    const totalSteps = 5;
    const progressPct = [20, 40, 60, 80, 100];

    function goStep(n) {
        // Validate current step before proceeding forward
        if (n > currentStep && !validateStep(currentStep)) return;

        // Animate out
        const current = document.getElementById('step-' + currentStep);
        current.classList.remove('active');

        currentStep = n;
        const next = document.getElementById('step-' + n);
        next.classList.add('active');

        // Update progress bar
        document.getElementById('progressFill').style.width = progressPct[n - 1] + '%';
        document.getElementById('registrationProgress').setAttribute('aria-valuenow', n);

        // Update left panel nav
        updateLeftNav(n);

        // Scroll right panel to top
        document.querySelector('.reg-right').scrollTo({ top: 0, behavior: 'smooth' });
        const heading = next.querySelector('h2');
        heading.setAttribute('tabindex', '-1');
        heading.focus({ preventScroll: true });
        document.querySelector('.reg-right').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateLeftNav(step) {
        document.querySelectorAll('.step-nav-item').forEach(item => {
            const n = parseInt(item.dataset.nav);
            item.classList.remove('active', 'done', 'pending');
            if (n < step)       item.classList.add('done');
            else if (n === step) item.classList.add('active');
            else                item.classList.add('pending');
            if (n === step) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');

            // Done step: show check icon
            const circle = item.querySelector('.step-nav-circle');
            if (n < step) {
                circle.innerHTML = '<i class="bi bi-check-lg" aria-hidden="true"></i>';
            } else {
                const icons = ['bi-envelope-fill','bi-person-fill','bi-geo-alt-fill','bi-people-fill','bi-card-image'];
                circle.innerHTML = '<i class="bi ' + icons[n-1] + '" aria-hidden="true"></i>';
            }
        });
    }

    // ── Step validation ──
    function validateStep(step) {
        let valid = true;

        if (step === 1) {
            const email = document.getElementById('email');
            const pw    = document.getElementById('password');
            const pwc   = document.getElementById('password_confirmation');

            if (!email.value || !email.value.includes('@')) {
                shake(email); showFieldError(email, 'Please enter a valid email.'); valid = false;
            }
            if (!pw.value || pw.value.length < 8) {
                shake(pw); showFieldError(pw, 'Password must be at least 8 characters.'); valid = false;
            }
            if (pw.value !== pwc.value) {
                shake(pwc); showFieldError(pwc, 'Passwords do not match.'); valid = false;
            }
        }

        if (step === 2) {
            const fn  = document.getElementById('first_name');
            const ln  = document.getElementById('last_name');
            const dob = document.getElementById('date_of_birth');
            if (!fn.value.trim()) { shake(fn); showFieldError(fn, 'First name is required.'); valid = false; }
            if (!ln.value.trim()) { shake(ln); showFieldError(ln, 'Last name is required.'); valid = false; }
            if (!dob.value) { shake(dob); showFieldError(dob, 'Date of birth is required.'); valid = false; }
        }

        if (step === 3) {
            const brgy = document.getElementById('barangayInput');
            if (!brgy.value || !brgy.value.trim()) {
                shake(brgy);
                showFieldError(brgy, 'Please select your barangay in San Carlos City.');
                valid = false;
            }
        }

        if (step === 4) {
            const n1 = document.querySelector('[name="emergency_name_1"]');
            const r1 = document.querySelector('[name="emergency_relationship_1"]');
            const c1 = document.querySelector('[name="emergency_contact_number_1"]');
            if (!n1.value.trim()) { shake(n1); showFieldError(n1, 'Contact name is required.'); valid = false; }
            if (!r1.value.trim()) { shake(r1); showFieldError(r1, 'Relationship is required.'); valid = false; }
            if (!c1.value.trim()) { shake(c1); showFieldError(c1, 'Contact number is required.'); valid = false; }
        }

        // Tell the user WHY Next didn't advance (was previously silent on steps 2 & 4)
        showStepSummary(step, valid);

        return valid;
    }

    function showStepSummary(step, valid) {
        const panel = document.getElementById('step-' + step);
        let bar = panel.querySelector('.step-err-summary');
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'step-err-summary';
            bar.setAttribute('role', 'alert');
            panel.prepend(bar);
        }
        if (valid) {
            bar.classList.remove('show');
        } else {
            bar.innerHTML = '<i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Please complete the highlighted fields to continue.';
            bar.classList.add('show');
        }
    }

    function shake(el) {
        el.classList.add('is-error');
        el.animate([
            {transform:'translateX(0)'},{transform:'translateX(-6px)'},
            {transform:'translateX(6px)'},{transform:'translateX(-4px)'},
            {transform:'translateX(4px)'},{transform:'translateX(0)'}
        ], { duration: 350, easing: 'ease' });
        el.addEventListener('input', () => {
            el.classList.remove('is-error');
            clearFieldError(el);
        }, { once: true });
    }

    function showFieldError(el, msg) {
        let err = el.parentElement.parentElement.querySelector('.field-err-msg');
        if (!err) {
            err = document.createElement('span');
            err.className = 'field-err-msg';
            err.style.cssText = 'font-size:0.74rem;color:var(--danger);margin-top:2px;display:block;';
            el.parentElement.parentElement.appendChild(err);
        }
        err.textContent = msg;
    }

    function clearFieldError(el) {
        const err = el.parentElement.parentElement.querySelector('.field-err-msg');
        if (err) err.remove();
    }

    // ── Password strength ──
    function checkStrength(val) {
        const fill  = document.getElementById('strengthFill');
        const text  = document.getElementById('strengthText');
        const tip   = document.getElementById('strengthTip');
        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^a-zA-Z0-9]/.test(val)) score++;

        const levels = [
            { pct:'0%',   color:'var(--danger)',  label:'Too short',   tipText:'Use 8+ characters' },
            { pct:'25%',  color:'var(--danger)',  label:'Weak',        tipText:'Add uppercase letters' },
            { pct:'50%',  color:'var(--warning)', label:'Fair',        tipText:'Add numbers too' },
            { pct:'75%',  color:'#F59E0B',        label:'Good',        tipText:'Add symbols for best security' },
            { pct:'100%', color:'var(--success)', label:'Strong 💪',   tipText:'Great password!' },
        ];
        const l = levels[score];
        fill.style.width    = l.pct;
        fill.style.background = l.color;
        text.textContent    = l.label;
        tip.textContent     = l.tipText;
    }

    // ── ID photo state (upload OR camera — both sides REQUIRED) ──
    const idCameraStreams = { front: null, back: null };

    function capSide(side) {
        return side === 'front' ? 'Front' : 'Back';
    }

    // Shared success state for both capture methods.
    function setIdPhoto(side, dataUrl, label) {
        const S = capSide(side);
        document.getElementById('id_image_data_' + side).value = dataUrl;
        const preview = document.getElementById('id-preview-' + side);
        preview.src = dataUrl;
        preview.style.display = 'block';
        document.getElementById(side + 'UploadArea').classList.add('has-file');
        document.getElementById(side + 'UploadArea').classList.remove('missing');
        document.getElementById(side + 'CameraPane').classList.remove('missing');
        document.getElementById(side + 'Icon').innerHTML = '<i class="bi bi-check-circle-fill" aria-hidden="true" style="color:var(--success);"></i>';
        document.getElementById(side + 'Title').textContent = S + ' ID ready ✓';
        document.getElementById(side + 'Desc').textContent = label;
        document.getElementById(side + 'IdError').style.display = 'none';
    }

    function clearIdPhoto(side) {
        const S = capSide(side);
        document.getElementById('id_image_data_' + side).value = '';
        const preview = document.getElementById('id-preview-' + side);
        preview.src = '';
        preview.style.display = 'none';
        document.getElementById(side + 'UploadArea').classList.remove('has-file');
    }

    // ── ID upload handler (file picker) ──
    function handleIdUpload(input, side) {
        const file = input.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Please choose an image file (JPG or PNG).');
            input.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Image is larger than 5MB. Please choose a smaller file or use the camera.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            setIdPhoto(side, e.target.result, file.name + ' · via upload');
        };
        reader.readAsDataURL(file);
    }

    // ── Upload / Camera mode toggle ──
    function setIdMode(side, mode) {
        document.getElementById(side + 'ModeUpload').classList.toggle('active', mode === 'upload');
        document.getElementById(side + 'ModeCamera').classList.toggle('active', mode === 'camera');
        document.getElementById(side + 'UploadArea').style.display = mode === 'upload' ? '' : 'none';
        document.getElementById(side + 'CameraPane').style.display = mode === 'camera' ? '' : 'none';
        if (mode === 'camera') {
            startIdCamera(side);
        } else {
            stopIdCamera(side);
        }
    }

    function startIdCamera(side) {
        const video = document.getElementById(side + 'Video');
        const fallback = document.getElementById(side + 'CameraFallback');
        const hint = document.getElementById(side + 'CameraHint');
        fallback.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            fallback.style.display = '';
            hint.textContent = 'Live camera is not supported in this browser.';
            return;
        }
        stopIdCamera(side);
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
            .then(function(stream) {
                idCameraStreams[side] = stream;
                video.srcObject = stream;
                video.play().catch(function() {});
            })
            .catch(function() {
                fallback.style.display = '';
                hint.textContent = 'Camera access was blocked. Allow camera access or use the photo fallback below.';
            });
    }

    function stopIdCamera(side) {
        const stream = idCameraStreams[side];
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); });
            idCameraStreams[side] = null;
        }
        const video = document.getElementById(side + 'Video');
        if (video) video.srcObject = null;
    }

    function stopAllIdCameras() {
        stopIdCamera('front');
        stopIdCamera('back');
    }

    function captureIdPhoto(side) {
        const video = document.getElementById(side + 'Video');
        const canvas = document.getElementById(side + 'Canvas');
        if (!video.videoWidth) {
            alert('Camera is still starting — please wait a moment and try again.');
            return;
        }
        const maxDim = 1600;
        const scale = Math.min(1, maxDim / Math.max(video.videoWidth, video.videoHeight));
        canvas.width = Math.round(video.videoWidth * scale);
        canvas.height = Math.round(video.videoHeight * scale);
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        setIdPhoto(side, dataUrl, 'Captured with camera · ' + new Date().toLocaleString());
        video.classList.add('captured');
        document.getElementById(side + 'CaptureBtn').style.display = 'none';
        document.getElementById(side + 'RetakeBtn').style.display = '';
        document.getElementById(side + 'CameraHint').textContent = capSide(side) + ' of ID captured ✓ — retake if blurry.';
        stopIdCamera(side);
    }

    function retakeIdPhoto(side) {
        clearIdPhoto(side);
        document.getElementById(side + 'Video').classList.remove('captured');
        document.getElementById(side + 'CaptureBtn').style.display = '';
        document.getElementById(side + 'RetakeBtn').style.display = 'none';
        document.getElementById(side + 'CameraHint').textContent = 'Point your camera at the ' + side + ' of your ID, then capture.';
        startIdCamera(side);
    }

    function flagMissingId(side) {
        document.getElementById(side + 'IdError').style.display = '';
        const pane = document.getElementById(side + 'CameraPane').style.display !== 'none'
            ? document.getElementById(side + 'CameraPane')
            : document.getElementById(side + 'UploadArea');
        pane.classList.remove('missing');
        void pane.offsetWidth; // restart shake animation
        pane.classList.add('missing');
        pane.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ── HARD REQUIREMENT: block submit until BOTH ID photos exist ──
    document.getElementById('regForm').addEventListener('submit', function(e) {
        const front = document.getElementById('id_image_data_front').value;
        const back = document.getElementById('id_image_data_back').value;
        if (!front || !back) {
            e.preventDefault();
            if (currentStep !== 5) goStep(5);
            if (!front) flagMissingId('front');
            if (!back) flagMissingId('back');
            return false;
        }
        stopAllIdCameras(); // release webcam before submit
        // Modern loading feedback: disable + spinner while creating the account
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('is-loading');
            btn.disabled = true;
            btn.innerHTML = 'Creating your account…';
        }
    });
    window.addEventListener('pagehide', stopAllIdCameras);

    // ── If there are server-side errors, try to jump to the relevant step ──
    @if ($errors->any())
    (function() {
        const errs = @json($errors->keys());
        if (errs.some(k => ['email','password','password_confirmation'].includes(k))) goStep(1);
        else if (errs.some(k => ['first_name','last_name','date_of_birth','contact_number'].includes(k))) goStep(2);
        else if (errs.some(k => ['purok_id'].includes(k))) goStep(3);
        else if (errs.some(k => k.startsWith('emergency_'))) goStep(4);
        else if (errs.some(k => k.startsWith('id_image'))) goStep(5);
    })();
    @endif

    // ── Camel Case (Title Case) auto-format for name fields ──
    function toTitleCase(str) {
        return str.replace(/\w\S*/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    }

    const nameFields = [
        'first_name', 'last_name', 'partner_name',
        'emergency_name_1', 'emergency_name_2', 'emergency_name_3',
        'emergency_relationship_1', 'emergency_relationship_2', 'emergency_relationship_3'
    ];

    nameFields.forEach(id => {
        const el = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
        if (!el) return;
        el.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.value = toTitleCase(this.value.trim());
            }
        });
        el.addEventListener('input', function() {
            // Capitalize first letter live as user types
            if (this.value.length === 1) {
                this.value = this.value.toUpperCase();
            }
        });
    });

    // Also capitalize middle initial
    const mi = document.getElementById('middle_initial');
    if (mi) {
        mi.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
</script>

<script>
    // ── Barangay Searchable & Scrollable Dropdown ──
    function setupBarangayDropdown() {
        const input = document.getElementById('barangayInput');
        const toggleBtn = document.getElementById('brgyDropdownToggle');
        const dropdown = document.getElementById('brgyDropdownList');
        const items = document.querySelectorAll('.brgy-option-item');
        const matchCountEl = document.getElementById('brgyMatchCount');
        const emptyState = document.getElementById('brgyEmptyState');

        if (!input || !dropdown) return;

        function openDropdown() {
            dropdown.style.display = 'block';
            toggleBtn?.classList.add('open');
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            toggleBtn?.classList.remove('open');
        }

        function filterList(query) {
            const q = query.trim().toLowerCase();
            let count = 0;
            items.forEach(item => {
                const val = (item.dataset.value || '').toLowerCase();
                if (!q || val.includes(q)) {
                    item.classList.remove('hidden');
                    count++;
                } else {
                    item.classList.add('hidden');
                }
            });
            if (matchCountEl) matchCountEl.textContent = count;
            if (emptyState) emptyState.style.display = count === 0 ? 'block' : 'none';
        }

        input.addEventListener('focus', function() {
            openDropdown();
            filterList(this.value);
        });

        input.addEventListener('input', function() {
            openDropdown();
            filterList(this.value);
            this.classList.remove('is-error');
            clearFieldError(this);
        });

        toggleBtn?.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dropdown.style.display === 'block') {
                closeDropdown();
            } else {
                openDropdown();
                filterList(input.value);
            }
        });

        items.forEach(item => {
            item.addEventListener('click', function() {
                const selectedVal = this.dataset.value;
                input.value = selectedVal;
                items.forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                closeDropdown();
                input.classList.remove('is-error');
                clearFieldError(input);
            });
        });

        document.addEventListener('click', function(e) {
            const wrap = document.querySelector('.brgy-combobox-wrap');
            if (wrap && !wrap.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupBarangayDropdown();
    });
</script>
@endpush
