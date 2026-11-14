{{-- Shared stylesheet for the women's health record print documents
     (women.print and women.print-bulk). Self-contained: no CDN, no app
     layout, so the printed file never depends on portal chrome. --}}
<style>
    :root {
        --pr-ink: #241d26;
        --pr-muted: #6f6472;
        --pr-line: #ddd3de;
        --pr-accent: #7d0f38;
        --pr-accent-soft: #f8eef2;
        --pr-band: #faf7f9;
        --pr-danger: #a3122f;
        --pr-warning: #8a5a00;
        --pr-success: #1c6b3c;
    }

    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        font-size: 9.5pt;
        line-height: 1.4;
        color: var(--pr-ink);
        background: #e8e2e9;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ---------- Screen-only toolbar ---------- */
    .pr-toolbar {
        position: sticky; top: 0; z-index: 20;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        padding: 0.65rem 1.1rem;
        background: #2b2130; color: #f4eef5;
        box-shadow: 0 2px 14px rgba(43, 33, 48, 0.35);
    }
    .pr-toolbar-title { font-size: 0.85rem; font-weight: 700; }
    .pr-toolbar-hint { font-size: 0.72rem; color: #cbb9cf; margin-top: 2px; }
    .pr-toolbar-actions { display: flex; gap: 0.5rem; align-items: center; }
    .pr-btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        border-radius: 999px; border: 1px solid transparent;
        font: inherit; font-size: 0.78rem; font-weight: 700;
        padding: 0.42rem 1.05rem; cursor: pointer; text-decoration: none;
    }
    .pr-btn svg { width: 14px; height: 14px; }
    .pr-btn-primary { background: #b94a6b; color: #fff; }
    .pr-btn-primary:hover { background: #a03a59; }
    .pr-btn-ghost { background: transparent; color: #f4eef5; border-color: #5c4a61; }
    .pr-btn-ghost:hover { border-color: #b94a6b; color: #ffd9e2; }
    .pr-notice {
        max-width: 210mm; margin: 0.8rem auto 0; padding: 0.55rem 0.9rem;
        background: #fff7e6; border: 1px solid #e8c98a; border-radius: 10px;
        color: #7a5200; font-size: 0.78rem;
    }

    /* ---------- Paper preview on screen ---------- */
    .pr-paper {
        max-width: 210mm; margin: 16px auto 48px;
        background: #fff; padding: 13mm 12mm;
        box-shadow: 0 10px 40px rgba(43, 33, 48, 0.22);
    }

    /* ---------- Document header ---------- */
    .pr-doc-head {
        display: flex; justify-content: space-between; align-items: flex-start; gap: 8mm;
        border-bottom: 2.2pt solid var(--pr-accent);
        padding-bottom: 3.5mm; margin-bottom: 4mm;
    }
    .pr-brand { display: flex; align-items: center; gap: 3mm; }
    .pr-brand img { width: 12mm; height: 12mm; }
    .pr-brand-name { font-size: 14pt; font-weight: 800; color: var(--pr-accent); letter-spacing: 0.01em; line-height: 1.1; }
    .pr-brand-sub { font-size: 7pt; color: var(--pr-muted); margin-top: 1mm; }
    .pr-doc-title { text-align: right; }
    .pr-doc-title h1 { margin: 0; font-size: 11pt; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; }
    .pr-doc-meta { font-size: 7.2pt; color: var(--pr-muted); margin-top: 1.5mm; line-height: 1.55; }
    .pr-doc-meta strong { color: var(--pr-ink); font-weight: 700; }

    /* ---------- Identity band ---------- */
    .pr-identity {
        background: var(--pr-band);
        border: 1px solid var(--pr-line); border-left: 3pt solid var(--pr-accent);
        padding: 3.5mm 4.5mm; margin-bottom: 4.5mm;
        break-inside: avoid;
    }
    .pr-identity-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 4mm; flex-wrap: wrap; }
    .pr-name { margin: 0; font-size: 15.5pt; font-weight: 800; line-height: 1.15; }
    .pr-pills { display: flex; gap: 1.5mm; flex-wrap: wrap; align-items: center; }
    .pr-pill {
        display: inline-block; border: 1px solid currentColor; border-radius: 999px;
        font-size: 6.8pt; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
        padding: 0.6mm 2.2mm; white-space: nowrap;
    }
    .pr-pill-accent { color: var(--pr-accent); }
    .pr-pill-muted { color: var(--pr-muted); }
    .pr-pill-success { color: var(--pr-success); }
    .pr-pill-warning { color: var(--pr-warning); }
    .pr-identity-facts { display: flex; flex-wrap: wrap; gap: 1mm 6mm; margin-top: 2mm; font-size: 8.2pt; color: var(--pr-ink); }
    .pr-identity-facts span strong { font-weight: 700; }
    .pr-identity-facts .pr-fact-label { color: var(--pr-muted); font-size: 7pt; text-transform: uppercase; letter-spacing: 0.06em; margin-right: 1.2mm; }

    /* ---------- Sections ---------- */
    .pr-section { margin-bottom: 4.5mm; }
    .pr-section-head {
        display: flex; align-items: center; gap: 2.5mm;
        border-bottom: 1px solid var(--pr-line);
        padding-bottom: 1.4mm; margin-bottom: 2.4mm;
        break-after: avoid;
    }
    .pr-section-num {
        display: inline-grid; place-items: center;
        min-width: 4.6mm; height: 4.6mm; padding: 0 1.2mm;
        background: var(--pr-accent); color: #fff;
        border-radius: 1.2mm; font-size: 7.5pt; font-weight: 800;
    }
    .pr-section-title { margin: 0; font-size: 9pt; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; }
    .pr-section-count { margin-left: auto; font-size: 7.2pt; color: var(--pr-muted); white-space: nowrap; }

    /* ---------- Field grid ---------- */
    .pr-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.4mm 5mm; break-inside: avoid; }
    .pr-field { min-width: 0; }
    .pr-field-label { font-size: 6.4pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--pr-muted); }
    .pr-field-value { font-size: 9pt; margin-top: 0.6mm; overflow-wrap: anywhere; }
    .pr-span-2 { grid-column: span 2; }
    .pr-span-3 { grid-column: 1 / -1; }
    .pr-subnote {
        margin-top: 2.5mm; padding: 2mm 3mm;
        background: var(--pr-band); border: 1px solid var(--pr-line); border-radius: 1.5mm;
        font-size: 8pt; break-inside: avoid;
    }
    .pr-subnote strong { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pr-muted); display: block; margin-bottom: 0.8mm; }

    /* ---------- Tables ---------- */
    .pr-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 7.8pt; }
    .pr-table th {
        background: var(--pr-accent-soft); color: var(--pr-accent);
        font-size: 6.6pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
        text-align: left; padding: 1.5mm 1.8mm; border: 1px solid var(--pr-line);
        overflow-wrap: anywhere;
    }
    .pr-table td { padding: 1.4mm 1.8mm; border: 1px solid var(--pr-line); vertical-align: top; overflow-wrap: anywhere; }
    .pr-table thead { display: table-header-group; }
    .pr-table tr { break-inside: avoid; }
    .pr-table tbody tr:nth-child(even) td { background: #fbf9fb; }
    .pr-table .pr-num { text-align: center; color: var(--pr-muted); }
    .pr-empty { font-size: 8.2pt; font-style: italic; color: var(--pr-muted); padding: 1.5mm 0; }

    /* ---------- Risk / status tags ---------- */
    .pr-tag {
        display: inline-block; border: 1px solid currentColor; border-radius: 999px;
        font-size: 6.6pt; font-weight: 700; padding: 0.2mm 1.8mm; white-space: nowrap;
    }
    .pr-tag-danger { color: var(--pr-danger); }
    .pr-tag-warning { color: var(--pr-warning); }
    .pr-tag-success { color: var(--pr-success); }
    .pr-tag-muted { color: var(--pr-muted); }
    .pr-tag + .pr-tag { margin-left: 1mm; }

    /* ---------- ID documents ---------- */
    .pr-id-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4mm; break-inside: avoid; }
    .pr-id-figure { margin: 0; }
    .pr-id-figure img { width: 100%; max-height: 42mm; object-fit: contain; border: 1px solid var(--pr-line); background: var(--pr-band); }
    .pr-id-caption { font-size: 6.8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pr-muted); margin-bottom: 1.2mm; }

    /* ---------- Record footer ---------- */
    .pr-record-foot {
        display: flex; justify-content: space-between; gap: 6mm; flex-wrap: wrap;
        margin-top: 4mm; padding-top: 2.2mm; border-top: 1px solid var(--pr-line);
        font-size: 6.9pt; color: var(--pr-muted); break-inside: avoid;
    }

    /* ---------- Bulk cover + record separation ---------- */
    .pr-record + .pr-record { break-before: page; }
    .pr-cover-summary {
        display: flex; gap: 6mm; flex-wrap: wrap;
        background: var(--pr-band); border: 1px solid var(--pr-line); border-left: 3pt solid var(--pr-accent);
        padding: 3mm 4.5mm; margin-bottom: 4.5mm; font-size: 8.5pt;
        break-inside: avoid;
    }
    .pr-cover-summary strong { font-weight: 800; }

    /* ---------- Print ---------- */
    @page { size: A4 portrait; margin: 12mm 11mm 14mm; }
    @media print {
        body { background: #fff; }
        .pr-toolbar, .pr-notice, .no-print { display: none !important; }
        .pr-paper { max-width: none; margin: 0; padding: 0; box-shadow: none; }
        a { color: inherit; text-decoration: none; }
    }
</style>
