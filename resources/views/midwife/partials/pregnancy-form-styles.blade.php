{{-- Shared compact styling for the pregnancy create/edit forms. --}}
<style>
    body .main-content .page-hero { padding:0.85rem 1.1rem !important; margin-bottom:0.75rem !important; border-radius:14px !important; }
    body .main-content .page-hero .page-hero-title { font-size:1.2rem !important; margin-bottom:0.1rem !important; }
    body .main-content .page-hero .page-hero-subtitle { font-size:0.82rem !important; }
    body .main-content .page-hero .btn-hero-primary,
    body .main-content .page-hero .btn-hero-secondary { padding:0.4rem 0.85rem !important; font-size:0.85rem !important; }

    .pf-grid { display:grid; gap:0.75rem; }
    .pf-card { border:1px solid var(--border); border-radius:14px; background:var(--bg-card); padding:0.8rem 1rem 1rem; }
    .pf-title { font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--text); margin-bottom:0.6rem; }
    .pf-sub { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin:0.15rem 0 0.4rem; }
    /* The portal theme pins .main-content .form-label/.form-control sizes, so these
       overrides need the extra class to win on specificity. */
    .main-content .pf .form-label { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-muted) !important; margin-bottom:0.2rem; }
    .main-content .pf .form-control,
    .main-content .pf .form-select { font-size:0.85rem; padding:0.4rem 0.6rem; min-height:36px; border-radius:10px; }
    .main-content .pf .form-text { font-size:0.68rem; line-height:1.3; }
    .main-content .pf .form-check { padding-top:0.35rem; margin-bottom:0; }
    .main-content .pf .form-check-label { font-size:0.82rem; color:var(--text) !important; }
    .main-content .pf .btn-check + .btn { font-size:0.8rem; padding:0.35rem 0.85rem; border-radius:10px; }
    .pf-identity { display:flex; align-items:center; gap:0.6rem; height:100%; padding:0.5rem 0.7rem; border:1px solid var(--border); border-radius:12px; background:var(--bg-card2); }
    .pf-avatar { width:46px; height:46px; border-radius:14px; object-fit:cover; border:1px solid var(--border); flex:0 0 auto; }
    .pf-chip { display:flex; align-items:center; padding:0.4rem 0.7rem; border:1px solid var(--border); border-radius:10px; background:var(--bg-card2); font-size:0.82rem; font-weight:600; color:var(--text); min-height:38px; }
    .pf-actions { position:sticky; bottom:0; z-index:5; display:flex; justify-content:flex-end; align-items:center; gap:0.5rem; padding:0.6rem 0.75rem; border:1px solid var(--border); border-radius:12px; background:color-mix(in srgb, var(--bg-card) 94%, transparent); backdrop-filter:blur(6px); }
</style>
