{{-- Single source of truth for the BHW monthly-report validation queues
     (midwife validation + RHU admin approval). Consumers wrap this in their own
     @push('styles') block alongside includes.women-directory-styles, which keeps
     powering the shared list-toolbar tabs and search box. --}}
<style>
    /* ── Head bar: what the queue is (left) + how big it is (right) ── */
    .rq-bar { display:flex; flex-wrap:wrap; align-items:center; gap:0.45rem 1rem; padding:0.6rem 0.9rem; border:1px solid var(--color-border); border-radius:16px; background:var(--color-surface); box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .rq-bar-id { min-width:0; }
    .rq-bar-title { display:flex; align-items:center; gap:0.45rem; font-size:0.95rem; font-weight:800; color:var(--color-text); line-height:1.25; }
    .rq-bar-title i { font-size:1rem; color:var(--color-primary-text); }
    .rq-bar-subtitle { margin:0; font-size:0.74rem; color:var(--color-text-muted); line-height:1.35; }
    .rq-chips { display:flex; flex-wrap:wrap; gap:0.35rem; margin-left:auto; }

    /* The queue sits under .main-content, so the framework's chip sizing needs
       body + the modifier class to be beaten. */
    body .main-content .summary-chip.rq-chip { padding:0.2rem 0.62rem !important; font-size:0.72rem !important; font-weight:700; gap:0.32rem; white-space:nowrap; }
    .rq-chip b { font-size:0.88rem; font-weight:800; line-height:1; }

    /* ── Queue table shell ── */
    .rq-wrap { border:none !important; border-radius:16px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .rq-wrap .card-body { padding:0 !important; }
    .rq-wrap .table { font-size:0.78rem; line-height:1.2; margin-bottom:0; }
    /* portal-theme pins cells at `.main-content .table tbody td` / `thead th`
       (0,2,2) with 0.9rem/1.25rem !important padding, so the compact values need
       body + .rq-wrap to outrank it. */
    body .main-content .rq-wrap .table :is(thead th, tbody td) { padding:0.4rem 0.55rem !important; white-space:nowrap; }
    .rq-wrap table thead th { background:transparent !important; border-bottom:1px solid var(--color-border) !important; font-size:0.63rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted) !important; }
    .rq-wrap table tbody tr:last-child td { border-bottom:none !important; }
    body .main-content .summary-chip.rq-status { padding:0.15rem 0.55rem !important; font-size:0.68rem !important; font-weight:700; }

    /* workspace-stack spaces its children 1.5rem apart, which reads as dead air
       between a slim bar, a filter row and a table. */
    .workspace-stack.rq-stack { gap:0.6rem; }

    /* One line per cell: the clipped text stays readable in the title tooltip. */
    .rq-cell { display:flex; align-items:center; gap:0.4rem; min-width:0; }
    .rq-truncate { display:inline-block; min-width:0; max-width:230px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom; }
    .rq-w-name { max-width:160px; }
    .rq-w-notes { max-width:170px; }
    .rq-title { font-weight:700; color:var(--color-text); }
    .rq-soft { font-size:0.72rem; color:var(--color-text-muted); }
    .rq-num { font-variant-numeric:tabular-nums; }
    /* color-mix on the strong tokens, not *-soft: in dark mode --color-secondary-soft
       equals the card background, which would erase the badge entirely. */
    .rq-type { display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; width:21px; height:21px; border-radius:7px; font-size:0.7rem; background:color-mix(in srgb, var(--color-info) 15%, transparent); color:var(--color-info-text); }
    .rq-type-pregnancy { background:color-mix(in srgb, var(--color-danger) 15%, transparent); color:var(--color-danger-text); }

    .rq-actions { display:flex; align-items:center; justify-content:flex-end; gap:0.3rem; }
    .rq-actions form { margin:0; }
    .rq-actions .btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:29px; padding:0 !important; border-radius:9px; font-size:0.78rem; }

    .rq-foot { display:flex; justify-content:center; padding:0.5rem; border-top:1px solid var(--color-border); }
    .rq-empty { padding:1.6rem 1rem; text-align:center; }
    .rq-empty .bi { font-size:1.9rem; }

    @media (max-width: 991.98px) {
        .rq-chips { margin-left:0; }
    }
    @media (max-width: 767.98px) {
        .rq-bar { padding:0.55rem 0.7rem; }
        .rq-bar-title { font-size:0.86rem; }
        .rq-bar-subtitle { font-size:0.7rem; }
        /* One swipeable strip instead of four wrapped chips stacking into a block. */
        .rq-chips { width:100%; flex-wrap:nowrap; overflow-x:auto; padding-bottom:0.15rem; scrollbar-width:none; -webkit-mask-image:linear-gradient(90deg, #000 92%, transparent 100%); mask-image:linear-gradient(90deg, #000 92%, transparent 100%); }
        .rq-chips::-webkit-scrollbar { display:none; }
        .rq-chip { flex:0 0 auto; }
        body .main-content .rq-wrap .table :is(thead th, tbody td) { padding:0.32rem 0.45rem !important; }
        .rq-wrap .table { font-size:0.72rem; }
        .rq-truncate { max-width:44vw; }
        body .main-content .summary-chip.rq-status { font-size:0.62rem !important; }
    }
</style>
