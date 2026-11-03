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

    /* Primary action lives on the same bar. Only it (no chips) has to reach the
       right edge on its own; with chips present they already carry the push. */
    .rq-action { display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.8rem; border-radius:999px; background:var(--color-surface-strong); font-size:0.76rem; font-weight:800; text-decoration:none; white-space:nowrap; }
    /* !important because theme.css paints every content-area link with the
       primary accent behind an eight-deep :not() chain that no plain selector
       can outrank — purple text on a dark pill would be unreadable. */
    body .main-content a.rq-action { color:var(--color-on-solid) !important; }
    body .main-content a.rq-action:hover, body .main-content a.rq-action:focus-visible { color:var(--color-on-solid) !important; opacity:0.9; }
    .rq-bar-id + .rq-action { margin-left:auto; }
    /* Only the first button reaches for the right edge; a page with two exports
       would otherwise strand one in the middle of the bar. */
    .rq-action + .rq-action { margin-left:0; }

    /* The queue sits under .main-content, so the framework's chip sizing needs
       body + the modifier class to be beaten. */
    body .main-content .summary-chip.rq-chip { padding:0.2rem 0.62rem !important; font-size:0.72rem !important; font-weight:700; gap:0.32rem; white-space:nowrap; }
    .rq-chip b { font-size:0.88rem; font-weight:800; line-height:1; }

    /* ── Queue table shell ── */
    .rq-wrap { border:none !important; border-radius:16px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .rq-wrap .card-body { padding:0 !important; }
    /* Pages that keep their filter row in this header (the supply-request desks)
       need the slim padding .wd-wrap gives its own toolbar header. portal-theme
       pins `.main-content .card-header` at (0,2,1) with 1.15rem !important, so the
       override needs body + the wrapper class. */
    body .main-content .rq-wrap .card-header { padding:0.5rem 0.8rem !important; }
    .rq-wrap .table { font-size:0.78rem; line-height:1.2; margin-bottom:0; }
    /* portal-theme lands after this partial and paints `.main-content .table` with
       0.9rem at (0,2,0), which outranks the same-specificity rule above — the queue
       tables need body + the wrapper to actually stay compact. */
    body .main-content .rq-wrap .table { font-size:0.78rem; }
    /* portal-theme pins cells at `.main-content .table tbody td` / `thead th`
       (0,2,2) with 0.9rem/1.25rem !important padding, so the compact values need
       body + .rq-wrap to outrank it. */
    body .main-content .rq-wrap .table :is(thead th, tbody td) { padding:0.4rem 0.55rem !important; white-space:nowrap; }
    /* The table-level size above only reaches cells that inherit: portal-theme's
       `.main-content .table thead th` (0,2,2) and the app shell's `.table td`
       (0,1,1) both paint the cells directly, which always beats an inherited
       size. Setting the cell sizes on the cells is what actually makes a queue
       row shorter than the framework's 0.875rem default. */
    body .main-content .rq-wrap .table tbody td { font-size:0.78rem; }
    body .main-content .rq-wrap .table thead th { font-size:0.63rem; }
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
    /* On phones two framework rules outrank the sizing above and crushed every
       row action into a 16x44 sliver: portal-theme's mobile block sets
       `.main-content .btn-outline-primary { width:100% }` (equal specificity,
       later in the cascade) and the app CSS sets `.btn { min-height:44px }`
       under 576px. !important on the width, and a longer selector than the bare
       `.btn`, are both required. */
    @media (max-width: 767.98px) {
        .rq-actions .btn { width:30px !important; }
    }
    @media (max-width: 575.98px) {
        .rq-actions .btn { min-height:0; }
    }

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
        .rq-chips { order:2; width:100%; flex-wrap:nowrap; overflow-x:auto; padding-bottom:0.15rem; scrollbar-width:none; -webkit-mask-image:linear-gradient(90deg, #000 92%, transparent 100%); mask-image:linear-gradient(90deg, #000 92%, transparent 100%); }
        /* The button keeps the title's line; the count strip drops below both. */
        .rq-action { order:1; margin-left:auto; padding:0.25rem 0.65rem; font-size:0.72rem; }
        .rq-action + .rq-action { margin-left:0; }
        .rq-chips::-webkit-scrollbar { display:none; }
        .rq-chip { flex:0 0 auto; }
        body .main-content .rq-wrap .table :is(thead th, tbody td) { padding:0.32rem 0.45rem !important; }
        .rq-wrap .table { font-size:0.72rem; }
        .rq-truncate { max-width:44vw; }
        body .main-content .summary-chip.rq-status { font-size:0.62rem !important; }
    }
</style>
