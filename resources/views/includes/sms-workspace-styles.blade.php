{{-- Single source of truth for the SMS workspace shell (midwife + RHU admin).
     Consumers wrap this in their own @push('styles') block and set --rc-accent*
     on .rc-sms, because the two portals differ only in accent hue.
     Colours come from design tokens only, so light/dark and the accent picker
     both work; theme.css keeps ownership of .rc-btn-pink, .rc-send and the
     pagination pills, so nothing here !important-fights it. --}}
<style>
    .rc-sms { max-width:1280px; margin:0 auto; padding-bottom:1.25rem; }

    /* ── Bar: title + the two actions, one line on a laptop ── */
    .rc-sms-bar { display:flex; align-items:center; justify-content:space-between; gap:.5rem .75rem; flex-wrap:wrap; margin-bottom:.7rem; }
    .rc-sms-heading { min-width:0; }
    .rc-sms-bar h1 { display:flex; align-items:center; gap:.5rem; margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:1.06rem; font-weight:800; letter-spacing:-.01em; color:var(--color-text); }
    .rc-sms-bar h1 i { color:var(--rc-accent-text); }
    .rc-sms-bar p { margin:.1rem 0 0; font-size:.76rem; color:var(--color-text-muted); }
    .rc-sms-tools { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; flex:1 1 auto; justify-content:flex-end; }
    .rc-optin { display:inline-flex; align-items:center; gap:.35rem; font-size:.74rem; font-weight:700; color:var(--color-success-text); background:var(--color-success-soft); border-radius:999px; padding:.34rem .7rem; white-space:nowrap; }

    /* ── Status tabs: counted, and each one filters the same list below ── */
    .rc-seg { display:inline-flex; gap:.15rem; padding:.15rem; background:var(--color-surface-soft); border-radius:999px; }
    /* !important on every anchor colour, because theme.css paints all body links
       with the primary accent at (0,11,2) specificity — the shared .wd-tab pills
       do the same. Without it the RHU tabs would silently turn midwife-violet. */
    .rc-seg-btn { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .7rem; border-radius:999px; font-size:.75rem; font-weight:700; color:var(--color-text-muted) !important; text-decoration:none; white-space:nowrap; transition:background .16s ease,color .16s ease; }
    .rc-seg-btn:hover { color:var(--color-text) !important; background:var(--color-surface); }
    .rc-seg-btn b { font-weight:800; opacity:.75; }
    .rc-seg-btn.active { background:var(--rc-accent-soft); color:var(--rc-accent-text) !important; box-shadow:inset 0 0 0 1px color-mix(in srgb, var(--rc-accent-text) 35%, transparent); }
    .rc-seg-btn.active b { opacity:1; }

    /* ── One form carries search + type, so no filter can drop another ── */
    .rc-sms-form { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; }
    .rc-search { position:relative; }
    .rc-search i { position:absolute; left:.7rem; top:50%; transform:translateY(-50%); font-size:.78rem; color:var(--color-text-muted); pointer-events:none; }
    .rc-search input { width:230px; min-width:0; padding:.4rem .8rem .4rem 1.9rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface); font-size:.8rem; color:var(--color-text); outline:none; }
    .rc-select { width:auto; flex:0 0 auto; padding:.4rem .6rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface); font-size:.78rem; font-weight:600; color:var(--color-text); }
    .rc-submit { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text-muted); cursor:pointer; }
    .rc-submit:hover { color:var(--rc-accent-text); background:var(--rc-accent-soft); border-color:transparent; }
    .rc-reset { display:inline-flex; align-items:center; gap:.3rem; font-size:.74rem; font-weight:700; color:var(--color-text-muted) !important; text-decoration:none; padding:.35rem .55rem; border-radius:999px; }
    .rc-reset:hover { color:var(--color-danger-text) !important; background:var(--color-danger-soft); }

    /* ── Workspace: fixed-height columns that scroll inside ── */
    .rc-sms-shell { display:grid; grid-template-columns:minmax(280px,330px) 1fr; gap:.85rem; align-items:stretch; }
    .rc-panel { display:flex; flex-direction:column; overflow:hidden; background:var(--color-surface); border-radius:16px; height:var(--rc-panel-h, clamp(400px,calc(100vh - 210px),760px)); height:var(--rc-panel-h, clamp(400px,calc(100dvh - 210px),760px)); box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .rc-panel-head { display:flex; align-items:center; justify-content:space-between; gap:.5rem; padding:.7rem .85rem; border-bottom:1px solid var(--color-border); }
    .rc-panel-head h2, .rc-panel-head h3 { margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:.86rem; font-weight:800; color:var(--color-text); display:flex; align-items:center; gap:.4rem; }
    .rc-panel-head h2 i, .rc-panel-head h3 i { color:var(--rc-accent-text); font-size:.9rem; }
    .rc-count { font-size:.72rem; font-weight:800; color:var(--color-text-muted); background:var(--color-surface-soft); border-radius:999px; padding:.2rem .55rem; white-space:nowrap; }

    .rc-conv-list { flex:1; min-height:0; overflow-y:auto; padding:.4rem; display:flex; flex-direction:column; gap:.15rem; }
    .rc-conv { display:flex; align-items:center; gap:.6rem; padding:.5rem .55rem; border-radius:12px; text-decoration:none; color:inherit !important; }
    .rc-conv:hover { background:var(--color-bg); color:inherit !important; }
    .rc-conv.active { background:var(--rc-accent-soft); box-shadow:inset 2px 0 0 var(--rc-accent-text); }
    .rc-conv-meta { flex:1; min-width:0; }
    .rc-conv-top { display:flex; align-items:baseline; justify-content:space-between; gap:.4rem; }
    .rc-conv-name { font-weight:800; font-size:.82rem; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-conv-time { font-size:.66rem; color:var(--color-text-muted); white-space:nowrap; flex-shrink:0; }
    .rc-conv-sub { display:block; font-size:.72rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:.1rem; }
    .rc-conv.has-thread .rc-conv-sub { color:var(--color-text); font-size:.76rem; }
    .rc-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; background:var(--color-border); }
    .rc-dot.sent { background:var(--color-success); } .rc-dot.failed { background:var(--color-danger); } .rc-dot.pending { background:var(--color-warning); }

    /* ── Thread ── */
    .rc-thread-head { display:flex; align-items:center; gap:.6rem; padding:.6rem .85rem; border-bottom:1px solid var(--color-border); }
    .rc-peer { flex:1; min-width:0; }
    .rc-peer h2, .rc-peer h4 { margin:0; font-size:.88rem; font-weight:800; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-peer small { display:block; font-size:.73rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-peer .on { color:var(--color-success-text); font-weight:700; } .rc-peer .off { color:var(--color-danger-text); font-weight:700; }
    .rc-back { display:inline-flex; align-items:center; gap:.25rem; font-size:.76rem; font-weight:700; color:var(--rc-accent-text) !important; background:var(--rc-accent-soft); border-radius:999px; padding:.35rem .65rem; text-decoration:none; white-space:nowrap; }
    .rc-thread-body { flex:1; min-height:0; overflow-y:auto; padding:1rem; background:var(--color-bg); display:flex; flex-direction:column; gap:.6rem; }
    .rc-day { align-self:center; font-size:.62rem; font-weight:800; letter-spacing:.5px; text-transform:uppercase; color:var(--color-text-muted); background:var(--color-surface); border-radius:999px; padding:.22rem .7rem; }
    .rc-bubble-row { display:flex; max-width:min(84%,560px); }
    .rc-bubble-row.out { align-self:flex-end; }
    .rc-bubble { padding:.6rem .8rem; border-radius:14px; border-bottom-right-radius:5px; font-size:.84rem; line-height:1.5; word-break:break-word; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text); }
    .rc-bubble-row.out .rc-bubble { background:var(--rc-accent-soft); border-color:transparent; }
    .rc-type-tag { display:inline-block; font-size:.6rem; font-weight:800; letter-spacing:.4px; text-transform:uppercase; color:var(--color-text-muted); background:var(--color-surface-soft); border-radius:999px; padding:.12rem .5rem; margin-bottom:.28rem; }
    .rc-bubble-meta { display:flex; align-items:center; justify-content:flex-end; gap:.3rem; font-size:.66rem; color:var(--color-text-muted); margin-top:.25rem; }
    .rc-bubble-meta .st-sent { color:var(--color-success-text); font-weight:700; } .rc-bubble-meta .st-failed { color:var(--color-danger-text); font-weight:700; } .rc-bubble-meta .st-pending { color:var(--color-warning-text); font-weight:700; }

    /* ── Composer: every field the old form had, in less space ── */
    .rc-composer { padding:.6rem .7rem .7rem; background:var(--color-surface); border-top:1px solid var(--color-border); }
    .rc-lang { display:flex; align-items:center; gap:.3rem; margin-bottom:.45rem; }
    .rc-lang button { border:1px solid transparent; background:var(--color-surface-soft); border-radius:999px; font-size:.7rem; font-weight:800; color:var(--color-text-muted); padding:.28rem .7rem; cursor:pointer; }
    .rc-lang button.active { background:var(--rc-accent-soft); color:var(--rc-accent-text); border-color:color-mix(in srgb, var(--rc-accent-text) 30%, transparent); }
    .rc-lang .hint { margin-left:auto; font-size:.66rem; color:var(--color-text-muted); }
    .rc-composer-bar { display:flex; align-items:flex-end; gap:.45rem; background:var(--color-surface-soft); border-radius:14px; padding:.35rem .35rem .35rem .8rem; }
    .rc-composer-bar:focus-within { background:var(--color-surface); box-shadow:0 0 0 3px var(--focus-ring); }
    .rc-composer-bar textarea { flex:1; min-width:0; border:none; outline:none; background:transparent; font-size:.84rem; color:var(--color-text); resize:none; min-height:38px; max-height:120px; padding:.35rem 0; font-family:inherit; box-shadow:none !important; }
    .rc-send { width:38px; height:38px; border-radius:50%; border:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; }
    .rc-char { font-size:.66rem; color:var(--color-text-muted); text-align:right; margin-top:.25rem; }

    .rc-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.2rem; text-align:center; padding:2.2rem 1.25rem; color:var(--color-text-muted); margin:auto; }
    .rc-empty i { font-size:2rem; color:var(--rc-accent-text); opacity:.5; margin-bottom:.3rem; }
    .rc-empty strong { font-size:.84rem; font-weight:800; color:var(--color-text); }
    .rc-empty span { font-size:.76rem; max-width:34ch; }

    .rc-pager { padding:.5rem .7rem; border-top:1px solid var(--color-border); background:var(--color-surface); }
    .rc-pager .pagination { gap:.25rem; }
    .rc-pager .page-link { font-size:.76rem; font-weight:800; border-radius:9px; padding:.25rem .6rem; min-width:32px; line-height:1.4; }

    /* Below a full workspace width the bar becomes two rows — title with
       Broadcast beside it, then the controls — instead of three. */
    @media (max-width:1200px) {
        .rc-sms-bar { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; }
        .rc-sms-heading { grid-area:1 / 1 / 2 / 2; }
        .rc-sms-broadcast { grid-area:1 / 2 / 2 / 3; justify-self:end; }
        .rc-sms-tools { grid-area:2 / 1 / 3 / 3; justify-content:flex-start; }
        .rc-seg { order:-1; flex:1 1 auto; min-width:0; overflow-x:auto; scrollbar-width:none; }
        .rc-seg::-webkit-scrollbar { display:none; }
        .rc-sms-form { flex:1 1 100%; }
        .rc-search { flex:1 1 0; min-width:0; }
        .rc-search input { width:100%; }
    }
    @media (max-width:991px) {
        /* One job per screen: the list OR the open thread, never both stacked. */
        .rc-sms-shell { grid-template-columns:1fr; }
        .rc-sms-shell[data-thread="0"] .rc-thread-panel { display:none; }
        .rc-sms-shell[data-thread="1"] .rc-conv-panel { display:none; }
    }
    @media (min-width:992px) { .rc-back { display:none; } }
    @media (max-width:575px) {
        .rc-sms-bar h1 { font-size:1rem; }
        .rc-bubble-row { max-width:92%; }
        /* Every pixel of height is conversation room on a phone; the same numbers
           appear in the panel header and the pager count. */
        .rc-sms-heading p, .rc-optin { display:none; }
    }
</style>
