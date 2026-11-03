{{-- Single source of truth for the maternal-death and near-miss case record
     pages (RHU death file, RHU near-miss file, CHO audit file). Pair with
     includes.report-queue-styles, which keeps powering the slim head bar.

     The pages previously laid their fields out with Bootstrap `.row.g-4` +
     `.col-md-6` blocks inside a default-padded card body, which spent about
     60px of vertical space per field and left the note paragraphs floating in
     the middle of the card. --}}
<style>
    /* Two columns of cards: the record on the left, the audit panel on the
       right. A grid instead of .row.g-4 so the gap is the same 0.6rem the
       queue pages use. */
    .cr-body { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(0,1fr); gap:0.6rem; align-items:start; }

    .cr-card { border:none !important; border-radius:16px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    /* portal-theme pins `.main-content .card-header` / `.card-body` padding at
       (0,2,x) with !important, so the compact values need body + .cr-card. */
    body .main-content .cr-card > .card-header { padding:0.45rem 0.8rem !important; border-bottom:1px solid var(--color-border) !important; background:transparent !important; }
    body .main-content .cr-card > .card-body { padding:0.7rem 0.85rem !important; }
    .cr-card-title { margin:0; font-size:0.85rem; font-weight:800; color:var(--color-text); }

    /* Field grid: one line per field, label above value. */
    .cr-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:0.5rem 1rem; }
    .cr-item { min-width:0; }
    .cr-wide { grid-column:1 / -1; }
    .cr-label { font-size:0.63rem; font-weight:800; letter-spacing:0.07em; text-transform:uppercase; color:var(--color-text-muted); margin-bottom:0.1rem; }
    .cr-value { font-size:0.82rem; font-weight:700; color:var(--color-text); line-height:1.35; overflow-wrap:anywhere; }
    .cr-sub { font-size:0.72rem; color:var(--color-text-muted); }
    .cr-rule { grid-column:1 / -1; border-top:1px solid var(--color-border); margin:0.1rem 0; }

    /* Free-text blocks stay inside the card instead of each getting its own
       bordered, padded panel with a heading above it. */
    .cr-note { margin:0.15rem 0 0; padding:0.45rem 0.6rem; border:1px solid var(--color-border); border-radius:10px; background:var(--color-surface-soft); font-size:0.78rem; line-height:1.5; color:var(--color-text); white-space:pre-wrap; overflow-wrap:anywhere; }
    .cr-note-quiet { font-style:italic; }

    /* Audit panel state: the centred 2.5rem icon inside a py-4 block was the
       tallest element on a page that had nothing else to say. */
    .cr-state { display:flex; align-items:flex-start; gap:0.5rem; padding:0.55rem 0.65rem; border:1px solid var(--color-border); border-radius:12px; background:var(--color-surface-soft); }
    .cr-state > i { font-size:1.05rem; line-height:1.3; flex:0 0 auto; }
    .cr-state-body { min-width:0; }
    .cr-state-title { font-size:0.78rem; font-weight:800; color:var(--color-text); }
    .cr-state-sub { margin:0; font-size:0.72rem; color:var(--color-text-muted); line-height:1.4; }

    /* The audit form sits in the same panel as the state it replaces, so its
       fields keep the record's label style rather than Bootstrap's defaults. */
    .cr-form .form-label { font-size:0.63rem; font-weight:800; letter-spacing:0.07em; text-transform:uppercase; color:var(--color-text-muted); margin-bottom:0.2rem; }
    .cr-form .form-control, .cr-form .form-select { font-size:0.8rem; border-radius:10px !important; border-color:var(--color-input-border) !important; }
    .cr-form .form-control { min-height:0; }
    body .main-content .cr-form > * + * { margin-top:0.55rem; }

    @media (max-width: 1199.98px) { .cr-body { grid-template-columns:1fr; } }
    @media (max-width: 575.98px) { .cr-grid { grid-template-columns:1fr; } }
</style>
