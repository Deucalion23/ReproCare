<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>City-Wide BHW Monthly Report — {{ $month }}</title>
    <style>
        body { font-family:DejaVu Sans, sans-serif; color:var(--color-text); font-size:12px; }
        h1 { font-size:20px; margin:0 0 4px; }
        .sub { color:var(--color-text-muted); margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; margin-top:12px; }
        th, td { border:1px solid var(--color-border); padding:6px 8px; text-align:left; }
        th { background:var(--color-surface-soft); }
        .sign { margin-top:48px; display:flex; justify-content:flex-end; }
        .sign-box { text-align:center; min-width:240px; border-top:1px solid var(--color-text); padding-top:6px; }
        .sign-box img { max-height:70px; max-width:220px; display:block; margin:0 auto 4px; }
        .meta { color:var(--color-text-muted); font-size:11px; }
    </style>
</head>
<body>
    <h1>City-Wide BHW Monthly Report</h1>
    <div class="sub">San Carlos City · {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }} · Generated {{ now()->format('M d, Y h:i A') }}</div>

    <table>
        <thead>
            <tr>
                <th>BHW</th>
                <th>Month</th>
                <th>Pregnant Women</th>
                <th>Checkups</th>
                <th>Deliveries</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ optional($report->submittedBy)->name ?? '—' }}</td>
                    <td>{{ $report->month }}</td>
                    <td>{{ $report->pregnant_count }}</td>
                    <td>{{ $report->checkup_count }}</td>
                    <td>{{ $report->delivery_count }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No reports submitted for this month.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sign">
        <div class="sign-box">
            @if(!empty($signatory) && $signatory->signature_image_url)
                <img src="{{ public_path(ltrim(parse_url($signatory->signature_image_url, PHP_URL_PATH), '/')) }}" alt="Official signature">
            @endif
            <strong>{{ $signatory->name ?? '' }}</strong><br>
            <span class="meta">{{ $signatory->official_title ?? 'City Health Officer' }}{{ !empty($signatory->license_number) ? ' · Lic. ' . $signatory->license_number : '' }}</span>
        </div>
    </div>
</body>
</html>
