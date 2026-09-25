@push('styles')
<style>
    /* ============================================================
       REPROCARE STAFF PORTAL THEME — White + Soft Pink
       Shared by Midwife, BHW, BHW President, RHU & CHO portals.
       Clean white surfaces, soft shadows, centered alignment.
       ============================================================ */
    

    /* ── Page background: pure white ── */
    .main-content {
        background-color:var(--color-surface) !important;
        min-height:calc(100vh - 60px);
        font-family:'Inter', sans-serif;
        color:var(--mw-body);
        padding:2rem 2rem 3.5rem !important;
    }

    /* Centered content column for consistent alignment */
    .main-content > .mw-container,
    .main-content > .page-hero,
    .main-content > .row,
    .main-content > .card {
        max-width:1280px;
        margin-left:auto;
        margin-right:auto;
    }
    .main-content > .row,
    .main-content > .card {
        width:100%;
    }

    /* ── Page hero: white card, soft shadow, aligned ── */
    .page-hero {
        background:var(--color-surface) !important;
        border:1px solid var(--mw-border) !important;
        border-radius:18px !important;
        padding:1.75rem 2rem !important;
        color:var(--mw-heading) !important;
        box-shadow:var(--mw-shadow-sm) !important;
        margin-bottom:1.5rem !important;
        overflow:hidden !important;
    }
    .page-hero::before,
    .page-hero::after { display:none !important; }

    .page-hero .d-flex.justify-content-between {
        align-items:center !important;
    }

    .page-hero-title, .page-title, h1.page-title {
        font-family:'Plus Jakarta Sans', sans-serif !important;
        font-size:1.65rem !important;
        font-weight:800 !important;
        letter-spacing:-0.02em;
        line-height:1.25;
        color:var(--mw-heading) !important;
        margin-bottom:0.35rem;
    }

    .page-hero-subtitle, .page-subtitle {
        color:var(--mw-body) !important;
        font-size:0.9rem !important;
        font-weight:500 !important;
        margin-bottom:0;
    }

    /* ── Buttons: dark solid (consistent across all portals) ── */
    .btn-hero-primary, .btn-primary,
    .main-content .btn-primary {
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        gap:0.5rem;
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;
        font-size:0.88rem;
        padding:0.65rem 1.4rem;
        border-radius:12px;
        border:1px solid var(--color-text) !important;
        text-decoration:none;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        transition:all 0.2s ease;
        white-space:nowrap;
    }
    .btn-hero-primary:hover, .btn-primary:hover,
    .main-content .btn-primary:hover {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        border-color:var(--color-text) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px);
        box-shadow:0 8px 22px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
    }
    .btn-hero-primary i, .btn-primary i,
    .main-content .btn-primary i { color:var(--color-on-solid) !important; }

    /* Semantic buttons - appropriate colors, not all pink */
    .main-content .btn-view, .btn-view {
        background:var(--color-info-text) !important;
        background-color:var(--color-info-text) !important;
        border:1px solid var(--color-info-text) !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
    }
    .main-content .btn-view:hover, .btn-view:hover {
        background:var(--color-info-text) !important;
        background-color:var(--color-info-text) !important;
        border-color:var(--color-info-text) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px);
    }
    .main-content .btn-view i, .btn-view i { color:var(--color-on-solid) !important; }
    .main-content .btn-filter, .btn-filter {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        border:1px solid var(--color-text) !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent);
    }
    .main-content .btn-filter:hover, .btn-filter:hover {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        border-color:var(--color-text) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px);
    }
    .main-content .btn-filter i, .btn-filter i { color:var(--color-on-solid) !important; }

    /* Secondary hero buttons: dark solid (contrasts the pink primary) */
    .btn-hero-secondary, .btn-secondary-card {
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        gap:0.5rem;
        background:var(--color-surface-strong) !important;
        border:1px solid var(--color-text) !important;
        color:var(--color-on-solid) !important;
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;
        font-size:0.88rem;
        padding:0.65rem 1.4rem;
        border-radius:12px;
        text-decoration:none;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent);
        transition:all 0.2s ease;
        white-space:nowrap;
    }
    .btn-hero-secondary:hover, .btn-secondary-card:hover {
        background:var(--color-surface-strong) !important;
        border-color:var(--color-text) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px);
    }
    .btn-hero-secondary i, .btn-secondary-card i { color:var(--color-on-solid) !important; }

    /* Outline buttons keep appropriate semantic colors (not all pink) */
    .main-content .btn-outline-primary {
        background:var(--color-surface) !important;
        border:1px solid var(--color-text) !important;
        color:var(--color-text) !important;
        box-shadow:none;
    }
    .main-content .btn-outline-primary:hover {
        background:var(--color-surface-strong) !important;
        border-color:var(--color-text) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px);
    }
    .main-content .btn-outline-primary i { color:inherit !important; }
    .main-content .btn-outline-warning {
        background:var(--color-surface) !important;
        border:1px solid var(--color-warning) !important;
        color:var(--color-warning-text) !important;
    }
    .main-content .btn-outline-warning:hover {
        background:var(--color-warning) !important;
        border-color:var(--color-warning) !important;
        color:var(--color-on-solid) !important;
    }
    .main-content .btn-outline-danger {
        background:var(--color-surface) !important;
        border:1px solid var(--color-danger) !important;
        color:var(--color-danger-text) !important;
    }
    .main-content .btn-outline-danger:hover {
        background:var(--color-danger) !important;
        border-color:var(--color-danger) !important;
        color:var(--color-on-solid) !important;
    }
    .main-content .btn-outline-success {
        background:var(--color-surface) !important;
        border:1px solid var(--color-success-text) !important;
        color:var(--color-success-text) !important;
    }
    .main-content .btn-outline-success:hover {
        background:var(--color-success-text) !important;
        border-color:var(--color-success-text) !important;
        color:var(--color-on-solid) !important;
    }
    .main-content .btn-outline-info {
        background:var(--color-surface) !important;
        border:1px solid var(--color-info) !important;
        color:var(--color-info-text) !important;
    }
    .main-content .btn-outline-info:hover {
        background:var(--color-info-text) !important;
        border-color:var(--color-info-text) !important;
        color:var(--color-on-solid) !important;
    }

    /* Summary chips */
    .summary-chip {
        display:inline-flex;
        align-items:center;
        gap:0.45rem;
        padding:0.45rem 1rem;
        border-radius:9999px;
        font-size:0.82rem;
        font-weight:700;
        text-decoration:none;
        line-height:1.4;
    }
    .summary-chip.chip-primary { background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text); }
    .summary-chip.chip-danger  { background:var(--color-danger-soft); border:1px solid var(--color-danger-soft); color:var(--color-danger-text); }
    .summary-chip.chip-warning { background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning-text); }
    .summary-chip.chip-success { background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); }
    .summary-chip.chip-info    { background:var(--color-info-soft); border:1px solid var(--color-info-soft); color:var(--color-info-text); }

    /* ── Cards: white, soft shadow, aligned headers ── */
    .main-content .card {
        background:var(--color-surface) !important;
        border-radius:18px !important;
        border:1px solid var(--mw-border) !important;
        box-shadow:var(--mw-shadow-sm) !important;
        overflow:hidden;
    }
    .main-content .card:hover {
        transform:none !important;
        box-shadow:var(--mw-shadow-md) !important;
        border-color:var(--color-border) !important;
    }
    .main-content .card-header {
        background:var(--color-surface) !important;
        border-bottom:1px solid var(--mw-border) !important;
        padding:1.15rem 1.5rem !important;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        flex-wrap:wrap;
    }
    .main-content .card-header h5,
    .main-content .card-header h6 {
        font-family:'Plus Jakarta Sans', sans-serif !important;
        font-weight:800 !important;
        font-size:1rem !important;
        color:var(--mw-heading) !important;
        margin:0 !important;
        display:flex;
        align-items:center;
        gap:0.5rem;
    }
    .main-content .card-body { padding:1.5rem !important; }
    .main-content .card-body.p-0 { padding:0 !important; }

    /* ── Stat cards: white, soft shadow, equal height ── */
    .main-content .stat-card,
    .main-content .metric-card {
        background:var(--color-surface) !important;
        border:1px solid var(--mw-border) !important;
        border-radius:18px !important;
        padding:1.4rem 1.5rem !important;
        box-shadow:var(--mw-shadow-sm) !important;
        color:var(--mw-heading);
        overflow:hidden;
        height:100%;
        transition:transform 0.2s ease, box-shadow 0.2s ease;
    }
    .main-content .stat-card:hover,
    .main-content .metric-card:hover {
        transform:translateY(-3px);
        box-shadow:var(--mw-shadow-md) !important;
    }

    .main-content .stat-icon,
    .main-content .metric-card-icon {
        width:44px;
        height:44px;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:1.2rem;
        margin-bottom:0.9rem;
        background:var(--color-secondary-soft) !important;
        color:var(--color-secondary-text) !important;
        box-shadow:none !important;
    }

    /* Gradient stat cards from the base theme render as white cards here —
       force their white icon tiles / white text into the portal palette. */
    .main-content .stat-purple .stat-icon, .main-content .stat-rose .stat-icon,
    .main-content .stat-cyan .stat-icon, .main-content .stat-amber .stat-icon,
    .main-content .stat-green .stat-icon, .main-content .stat-lime .stat-icon,
    .main-content .stat-violet .stat-icon {
        background:var(--color-secondary-soft) !important;
        color:var(--color-secondary-text) !important;
        box-shadow:none !important;
    }
    .main-content .stat-purple .stat-label, .main-content .stat-rose .stat-label,
    .main-content .stat-cyan .stat-label, .main-content .stat-amber .stat-label,
    .main-content .stat-green .stat-label, .main-content .stat-lime .stat-label,
    .main-content .stat-violet .stat-label,
    .main-content .stat-purple .stat-trend, .main-content .stat-rose .stat-trend,
    .main-content .stat-cyan .stat-trend, .main-content .stat-amber .stat-trend,
    .main-content .stat-green .stat-trend, .main-content .stat-lime .stat-trend,
    .main-content .stat-violet .stat-trend { color:var(--mw-body) !important; }
    .main-content .stat-purple .stat-number, .main-content .stat-rose .stat-number,
    .main-content .stat-cyan .stat-number, .main-content .stat-amber .stat-number,
    .main-content .stat-green .stat-number, .main-content .stat-lime .stat-number,
    .main-content .stat-violet .stat-number { color:var(--mw-heading) !important; }

    .main-content .stat-label,
    .main-content .metric-card-label {
        font-size:0.75rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:0.06em;
        color:var(--mw-body) !important;
        margin-bottom:0.35rem;
    }
    .main-content .stat-number,
    .main-content .metric-card-value {
        font-family:'Plus Jakarta Sans', sans-serif !important;
        font-size:2rem !important;
        font-weight:800 !important;
        line-height:1.1;
        color:var(--mw-heading) !important;
        margin-bottom:0.4rem;
    }
    .main-content .stat-trend {
        font-size:0.8rem;
        font-weight:600;
        display:flex;
        align-items:center;
        gap:0.25rem;
        color:var(--mw-body);
    }

    /* ── Tables: clean white, aligned cells ── */
    .main-content .table {
        margin-bottom:0;
        font-size:0.9rem;
    }
    .main-content .table thead th {
        background:var(--color-surface) !important;
        color:var(--mw-body) !important;
        font-size:0.72rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:0.06em;
        padding:0.9rem 1.25rem !important;
        border-bottom:1px solid var(--mw-border) !important;
        white-space:nowrap;
        vertical-align:middle;
    }
    .main-content .table tbody td {
        padding:0.9rem 1.25rem !important;
        vertical-align:middle !important;
        border-bottom:1px solid var(--mw-border-soft) !important;
        color:var(--color-text) !important;
    }
    .main-content .table tbody tr:last-child td { border-bottom:none !important; }
    .main-content .table-hover tbody tr:hover td {
        background-color:var(--color-secondary-soft) !important;
    }

    /* Status pills */
    .main-content .status-scheduled { background:var(--color-secondary-soft); color:var(--color-secondary-text); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-secondary-soft); }
    .main-content .status-completed { background:var(--color-success-soft); color:var(--color-success-text); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-success-soft); }
    .main-content .status-missed    { background:var(--color-danger-soft); color:var(--color-danger-text); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-danger-soft); }
    .main-content .status-pending   { background:var(--color-warning-soft); color:var(--color-warning-text); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-warning); }
    .main-content .status-active    { background:var(--color-success-soft); color:var(--color-success-text); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-success-soft); }
    .main-content .status-cancelled { background:var(--color-surface-soft); color:var(--color-text-muted); border-radius:9999px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; border:1px solid var(--color-border); }

    /* ── Forms: white inputs, pink focus ── */
    .main-content .form-control,
    .main-content .form-select {
        border-radius:12px !important;
        border:1px solid var(--color-border) !important;
        background:var(--color-surface) !important;
        font-size:0.88rem;
        min-height:42px;
        color:var(--color-text) !important;
    }
    .main-content .form-control:focus,
    .main-content .form-select:focus {
        border-color:var(--mw-primary) !important;
        box-shadow:0 0 0 3px var(--mw-primary-glow) !important;
        outline:none;
    }
    .main-content .form-label {
        font-weight:600;
        font-size:0.85rem;
        color:var(--color-text) !important;
        margin-bottom:0.4rem;
    }

    /* ── Quick action tiles: borderless dashboard pastels ── */
    .main-content .quick-action-tile {
        display:flex !important;
        flex-direction:column !important;
        align-items:center !important;
        justify-content:center !important;
        gap:0.6rem;
        text-align:center;
        background:var(--color-surface-soft) !important;
        border:none !important;
        border-radius:18px !important;
        padding:1.25rem 0.75rem !important;
        text-decoration:none !important;
        font-size:0.8rem;
        font-weight:700;
        color:var(--color-text) !important;
        box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent) !important;
        transition:all 0.2s ease;
        height:100%;
        min-height:104px;
    }
    .main-content .quick-action-tile i {
        font-size:1.5rem !important;
        color:var(--color-text) !important;
        line-height:1;
    }
    .main-content .quick-action-tile:hover {
        background:var(--color-border) !important;
        border:none !important;
        transform:translateY(-3px);
        box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent) !important;
    }
    /* Soft tinted variants — borderless, deep icons */
    .main-content .quick-action-tile.qa-pink { background:var(--color-secondary-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-pink i { color:var(--color-secondary-text) !important; }
    .main-content .quick-action-tile.qa-teal { background:var(--color-success-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-teal i { color:var(--color-success-text) !important; }
    .main-content .quick-action-tile.qa-blue { background:var(--color-primary-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-blue i { color:var(--color-primary-text) !important; }
    .main-content .quick-action-tile.qa-amber { background:var(--color-peach-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-amber i { color:var(--color-warning-text) !important; }
    .main-content .quick-action-tile.qa-red { background:var(--color-danger-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-red i { color:var(--color-danger-text) !important; }
    .main-content .quick-action-tile.qa-violet { background:var(--color-primary-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-violet i { color:var(--color-primary-text) !important; }
    .main-content .quick-action-tile.qa-green { background:var(--color-success-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-green i { color:var(--color-success-text) !important; }
    .main-content .quick-action-tile.qa-slate { background:var(--color-surface-soft) !important; border:none !important; }
    .main-content .quick-action-tile.qa-slate i { color:var(--color-text) !important; }

    /* ── Sidebar (all staff portals): light gray transparent + pink active ── */
    .sidebar {
        background:color-mix(in srgb, var(--color-surface-soft) 72%, transparent) !important;
        background-color:color-mix(in srgb, var(--color-surface-soft) 72%, transparent) !important;
        -webkit-backdrop-filter:blur(14px) saturate(1.2);
        backdrop-filter:blur(14px) saturate(1.2);
        border-right:1px solid color-mix(in srgb, var(--color-border) 60%, transparent) !important;
        box-shadow:2px 0 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent) !important;
    }
    .sidebar .nav-link {
        align-items:center !important;
        color:var(--color-text-muted) !important;
    }
    .sidebar .nav-link:hover {
        background:var(--color-secondary-soft) !important;
        color:var(--color-secondary-text) !important;
    }
    .sidebar .nav-link:hover i { color:var(--color-secondary-text) !important; }
    .sidebar .nav-link.active {
        background:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        font-weight:700;
        box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent) !important;
    }
    .sidebar .nav-link.active i { color:var(--color-on-solid) !important; }
    /* Higher specificity wins over base layouts.app rules (which load later). */
    body .layout-wrapper nav.sidebar .nav-link.active,
    body nav.sidebar .nav-link.active {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent) !important;
    }
    body .layout-wrapper nav.sidebar .nav-link.active i,
    body nav.sidebar .nav-link.active i { color:var(--color-on-solid) !important; }
    body .layout-wrapper nav.sidebar .nav-link:hover,
    body nav.sidebar .nav-link:hover {
        background:var(--color-secondary-soft) !important;
        color:var(--color-secondary-text) !important;
    }
    body .layout-wrapper nav.sidebar .nav-link:hover i,
    body nav.sidebar .nav-link:hover i { color:var(--color-secondary-text) !important; }
    .sidebar-portal-label {
        background:none !important;
        -webkit-text-fill-color:var(--color-secondary-text) !important;
        color:var(--color-secondary-text) !important;
    }

    /* ── Top navbar accents on midwife pages ── */
    .navbar .navbar-brand div.rounded-3 {
        background:linear-gradient(135deg, var(--color-secondary-soft), var(--color-secondary)) !important;
    }
    .navbar .navbar-brand span span { color:var(--color-secondary-text) !important; }

    /* ── Badges / pills: soft pink default ── */
    .main-content .badge.bg-primary { background:var(--color-secondary-soft) !important; color:var(--color-secondary-text) !important; border:1px solid var(--color-secondary-soft); }
    .main-content .badge { vertical-align:middle; }

    /* ── Empty states: centered ── */
    .main-content .empty-state {
        text-align:center;
        padding:2.5rem 1.5rem;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
    }

    /* ── Alerts: white-pink, aligned ── */
    .main-content .alert {
        border-radius:14px !important;
        display:flex;
        align-items:flex-start;
        gap:0.75rem;
    }

    /* ── Responsive alignment (any phone size, items stay visible) ── */
    @media (max-width: 768px) {
        .main-content { padding:1.25rem 1rem calc(3rem + env(safe-area-inset-bottom, 0px)) !important; }
        .page-hero { padding:1.25rem 1.25rem !important; }
        .main-content .card-body { padding:1.1rem !important; }
        .page-hero .d-flex.justify-content-between { align-items:stretch !important; flex-direction:column; gap:0.75rem; }
        .page-hero .btn, .main-content .btn-primary, .main-content .btn-outline-primary { width:100%; }
        .main-content .table-responsive, .main-content .card .table-responsive { overflow-x:auto !important; -webkit-overflow-scrolling:touch; }
        .main-content img, .main-content video, .main-content canvas { max-width:100%; height:auto; }
    }
    @media (max-width: 480px) {
        .page-hero-title, .page-title, h1.page-title { font-size:1.3rem !important; }
        .main-content .stat-number, .main-content .metric-card-value { font-size:1.6rem !important; }
        .main-content .card-header { align-items:flex-start !important; flex-direction:column; }
        .summary-chip { font-size:0.76rem; }
    }
</style>
@endpush

