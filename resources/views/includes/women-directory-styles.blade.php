{{-- Single source of truth for the "All Women" directory shell (midwife, bhw, bhw-president, cho, rhu).
     Consumers wrap this in their own @push('styles') block. --}}
<style>
    .wd-mini .card { border:none !important; border-radius:18px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; transition:transform .2s ease, box-shadow .2s ease; }
    .wd-mini .card:hover { transform:translateY(-3px); box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .wd-mini .card.is-active { border:1px solid var(--color-primary) !important; box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .wd-mini .card a { color:inherit; text-decoration:none; }
    .wd-mini .card .text-muted { font-size:0.7rem !important; }
    .wd-mini .card.is-static { cursor:default; }
    .wd-mini .card.is-static:hover { transform:none; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    .wd-wrap { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .wd-wrap .card-header { border:none !important; padding-top:0.9rem; padding-bottom:0.9rem; }
    .wd-wrap .card-header > .d-flex { gap:0.5rem; }
    /* Custom tab class instead of Bootstrap's .nav-link: theme.css pins
       :root[data-theme][data-bs-theme] body .nav-pills .nav-link.active to a tinted
       pill with an inset sidebar bar, which outranks anything page-level. */
    .wd-tabs { display:flex; gap:0.5rem; flex-wrap:wrap; margin:0; padding:0; list-style:none; }
    .wd-tab { display:inline-flex; align-items:center; gap:0.4rem; border:1px solid var(--color-border) !important; border-radius:999px !important; font-weight:800 !important; font-size:0.78rem !important; padding:0.42rem 1rem !important; text-decoration:none; color:var(--color-text) !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; }
    .wd-tab:hover { border-color:var(--color-primary) !important; color:var(--color-primary-text) !important; }
    .wd-tab.active { background:var(--color-text) !important; background-color:var(--color-text) !important; border-color:var(--color-text) !important; color:var(--color-bg) !important; box-shadow:0 6px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent); }
    .wd-tab.active i { color:var(--color-bg) !important; }
    /* The wrapper owns the only border and the only focus ring. theme.css:75/79
       re-applies border-color + a 3px !important ring to every .form-control and
       :focus input at (0,3,2)/(0,4,2), so the inner reset needs the same
       :root[data-theme][data-bs-theme] body prefix plus .wd-search to outrank it. */
    .wd-search { position:relative; box-sizing:border-box; background:var(--color-surface) !important; background-color:var(--color-surface) !important; border:1px solid var(--color-input-border) !important; border-radius:10px !important; max-width:320px; min-height:36px; }
    .wd-search:focus-within { border-color:var(--color-primary-text) !important; box-shadow:0 0 0 3px var(--focus-ring) !important; }
    :root[data-theme][data-bs-theme] body .wd-search :is(.form-control,.form-select,input,textarea) {
        display:block; min-width:0; width:100%; min-height:34px; appearance:none;
        background:transparent !important; background-color:transparent !important;
        border:0 !important; border-color:transparent !important; border-radius:10px !important;
        box-shadow:none !important; outline:0 !important;
        font-size:0.82rem; padding:0.375rem 2.75rem 0.375rem 0.9rem !important;
    }
    :root[data-theme][data-bs-theme] body .wd-search :is(.form-control,input,textarea):focus { border:0 !important; border-color:transparent !important; box-shadow:none !important; outline:0 !important; }
    .wd-search .btn { position:absolute; top:0; right:0; bottom:0; display:grid; place-items:center; border:0 !important; border-radius:0 10px 10px 0 !important; background:transparent !important; box-shadow:none !important; outline:0 !important; color:var(--color-text-muted) !important; width:2.5rem; padding:0; }
    .wd-search .btn:hover, .wd-search .btn:focus-visible { color:var(--color-primary-text) !important; background:transparent !important; box-shadow:none !important; outline:0 !important; }
    .wd-filters { display:flex; flex-wrap:wrap; gap:0.5rem; align-items:flex-end; }
    .wd-filters .wd-field { display:flex; flex-direction:column; gap:0.25rem; }
    .wd-filters label { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); }
    .wd-filters .wd-select-field .form-control, .wd-filters .wd-select-field .form-select,
    .wd-filters .wd-date-field .form-control, .wd-filters .wd-date-field .form-select { border-radius:10px !important; border-color:var(--color-input-border) !important; font-size:0.82rem; min-height:36px; }
    .wd-filters .wd-search-field { flex:1 1 240px; max-width:320px; }
    .wd-filters .wd-select-field { flex:0 1 150px; min-width:130px; }
    .wd-filters .wd-date-field { flex:0 1 150px; min-width:130px; }
    .wd-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-primary) !important; background-color:var(--color-primary) !important; color:#ffffff !important; padding:0.45rem 1.15rem !important; font-size:0.8rem !important; }
    .wd-clear-btn { border:1px solid var(--color-border) !important; border-radius:999px !important; padding:0.45rem 0.9rem !important; font-size:0.8rem !important; font-weight:700; color:var(--color-text-muted) !important; background:transparent !important; }
    .wd-wrap table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .wd-wrap table tbody td { border:none !important; vertical-align:middle; }
    .wd-wrap table tbody tr:hover { background:var(--color-bg); }
    .wd-name { font-weight:700; color:var(--color-text); line-height:1.25; }
    .wd-sub { font-size:0.75rem; color:var(--color-text-muted); }
    .wd-pill { font-size:0.7rem; font-weight:800; padding:0.25em 0.75em; border-radius:999px; white-space:nowrap; display:inline-block; }
    .wd-pill-registered { background:var(--color-success-soft); color:var(--color-success-text); }
    .wd-pill-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .wd-pill-muted { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .wd-pill-pregnant { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .wd-count { font-size:0.78rem; font-weight:700; color:var(--color-text-muted); }
    @media (max-width: 991.98px) {
        .wd-filters .wd-search-field { max-width:none; flex:1 1 100%; }
        .wd-filters .wd-select-field, .wd-filters .wd-date-field { flex:1 1 calc(50% - 0.5rem); }
    }
    @media (max-width: 767.98px) {
        .wd-filters .wd-select-field, .wd-filters .wd-date-field { flex:1 1 100%; }
        .wd-wrap .table { font-size:0.78rem; }
        .wd-wrap .table th, .wd-wrap .table td { padding:0.5rem; }
    }
    /* Force table to fit screen without scroll */
    .wd-wrap .table-responsive { overflow:visible !important; }
    .wd-wrap { overflow:hidden; }
    .wd-wrap .table { font-size:0.7rem !important; min-width:100%; table-layout:auto; line-height:1.1; }
    .wd-wrap .table th, .wd-wrap .table td { padding:0.2rem 0.3rem !important; white-space:nowrap; }
    .wd-wrap .table td.wd-cell-wrap { white-space:normal; }
    .wd-wrap .table tbody tr { height:28px !important; }
    .wd-wrap .table td .wd-sub { font-size:0.65rem; line-height:1.1; }
    .wd-wrap .table td .wd-name { font-size:0.78rem; line-height:1.1; }
    .wd-wrap .table td .wd-pill { font-size:0.6rem; padding:0.1em 0.35em; }
    .wd-wrap .table .btn { padding:0.15rem 0.45rem !important; font-size:0.65rem !important; }
    /* The row action is the one button worth more than a 0.65rem chip: it sat
       right-aligned inside a column already ~150px wide from its own header, so it
       read as clipped against the card edge. Needs the same .table .btn specificity
       plus !important to beat the rule above. */
    .wd-wrap .table .wd-action-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:0.35rem;
        min-width:112px; padding:0.4rem 0.9rem !important; font-size:0.78rem !important;
        font-weight:700; white-space:nowrap;
    }
    .wd-wrap .card-header { padding-top:0.4rem !important; padding-bottom:0.4rem !important; }
    .wd-wrap .card-body { padding:0 !important; }
    .wd-wrap .table-responsive { border:none; }
    .wd-wrap .p-3 { padding:0.3rem !important; }
    .wd-wrap .py-2 { padding-top:0.2rem !important; padding-bottom:0.2rem !important; }
</style>
