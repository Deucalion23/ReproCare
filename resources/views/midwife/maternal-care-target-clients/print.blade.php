<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Maternal Client List</title>
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

    <h1>Maternal Client List</h1>
    <p class="meta">Tab: {{ strtoupper(str_replace('-', ' ', $tab)) }} | Search: {{ $search !== '' ? $search : 'All records' }} | Printed: {{ now()->format('F j, Y g:i A') }}</p>

    <div class="print-scroll">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Client</th>
                <th>Barangay</th>
                <th>Age</th>
                <th>LMP</th>
                <th>EDD</th>
                <th>Profile Summary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr>
                    <td>{{ $client['number'] }}</td>
                    <td>{{ $client['woman']->name }}</td>
                    <td>{{ $client['woman']->barangay ?? '-' }}</td>
                    <td>{{ $client['age'] ?? '-' }}</td>
                    <td>{{ optional($client['pregnancy']?->lmp)->format('m/d/Y') ?? '-' }}</td>
                    <td>{{ optional($client['pregnancy']?->edd)->format('m/d/Y') ?? '-' }}</td>
                    <td>
                        Gravida: {{ $client['profile']?->gravida ?? '-' }}<br>
                        Parity: {{ $client['profile']?->parity ?? '-' }}<br>
                        1st tri (1): {{ !empty($client['prenatal']['visits']['first'][0]) ? \Illuminate\Support\Carbon::parse($client['prenatal']['visits']['first'][0])->format('m/d/Y') : '-' }}<br>
                        2nd tri (2): @foreach($client['prenatal']['visits']['second'] as $visitDate){{ $visitDate ? \Illuminate\Support\Carbon::parse($visitDate)->format('m/d/Y') : '-' }}@if(!$loop->last), @endif@endforeach<br>
                        3rd tri (5): @foreach($client['prenatal']['visits']['third'] as $visitDate){{ $visitDate ? \Illuminate\Support\Carbon::parse($visitDate)->format('m/d/Y') : '-' }}@if(!$loop->last), @endif@endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">No maternal client records found.</td>
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
