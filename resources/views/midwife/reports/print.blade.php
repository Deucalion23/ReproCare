<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patients Report - ReproCare Midwife</title>
    <style>
        body { font-family:sans-serif; font-size:12px; }
        h1 { font-size:18px; }
        table { width:100%; border-collapse:collapse; margin-top:12px; }
        th, td { border:1px solid var(--color-border); padding:6px; text-align:left; }
        .print-scroll { overflow-x:auto; }
        @media print { .print-scroll { overflow:visible; } }
</head>
<body onload="window.print()">
    <h1>Patients Report ({{ now()->format('F j, Y') }})</h1>
    <div class="print-scroll">
    <table>
        <thead>
            <tr><th>#</th><th>Patient Name</th><th>Age</th><th>Status</th><th>Email</th><th>Contact</th><th>Barangay</th></tr>
        </thead>
        <tbody>
            @forelse($patients as $i => $patient)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $patient->name }}</td>
                    <td>{{ $patient->age ?? 'N/A' }}</td>
                    <td>{{ $patient->pregnancy_status ?? 'N/A' }}</td>
                    <td>{{ $patient->email }}</td>
                    <td>{{ $patient->contact_number ?? 'N/A' }}</td>
                    <td>{{ $patient->barangay ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No patients found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</body>
</html>
