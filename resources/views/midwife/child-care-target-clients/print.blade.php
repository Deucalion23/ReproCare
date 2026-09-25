<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Child Care Client List</title>
    <style>
        body { font-family:Arial, sans-serif; margin:24px; color:var(--color-text); }
        h1, p { margin:0 0 8px; }
        .meta { margin-bottom:20px; color:var(--color-text-muted); }
        table { width:100%; border-collapse:collapse; font-size:12px; }
        th, td { border:1px solid var(--color-border); padding:8px; text-align:left; vertical-align:top; }
        th { background:var(--color-surface-soft); }
        .right { text-align:right; }
        .print-scroll { overflow-x:auto; }
        @media print { .no-print { display:none; } body { margin:12px; } .print-scroll { overflow:visible; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()">Print</button>
    </div>

    <h1>Child Care Client List</h1>
    <p class="meta">Tab: {{ strtoupper(str_replace('-', ' ', $tab)) }} | Search: {{ $search !== '' ? $search : 'All records' }} | Printed: {{ now()->format('F j, Y g:i A') }}</p>

    <div class="print-scroll">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Child</th>
                <th>Mother</th>
                <th>Barangay</th>
                <th>Date of Birth</th>
                <th>Gender</th>
                <th>Profile Summary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr>
                    <td>{{ $client['number'] }}</td>
                    <td>{{ $client['child']->full_name }}</td>
                    <td>{{ $client['child']->mother?->name ?? '-' }}</td>
                    <td>{{ $client['child']->barangay ?? $client['child']->mother?->barangay ?? '-' }}</td>
                    <td>{{ optional($client['child']->date_of_birth)->format('m/d/Y') ?? '-' }}</td>
                    <td>{{ ucfirst($client['child']->gender ?? '-') }}</td>
                    <td>
                        Registration: {{ $client['profile']?->date_of_registration ? \Illuminate\Support\Carbon::parse($client['profile']->date_of_registration)->format('m/d/Y') : '-' }}<br>
                        BCG: {{ $client['profile']?->bcg_date ? \Illuminate\Support\Carbon::parse($client['profile']->bcg_date)->format('m/d/Y') : '-' }}<br>
                        Hepa B: {{ $client['profile']?->hepa_b_bd_date ? \Illuminate\Support\Carbon::parse($client['profile']->hepa_b_bd_date)->format('m/d/Y') : '-' }}<br>
                        Remarks: {{ $client['profile']?->remarks ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">No child care client records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <script>
    window.addEventListener('load', function () {
        window.print();
    });
    </script>
</body>
</html>
