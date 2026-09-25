<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternal MCH Registry Report - ReproCare</title>
    <!-- Include Bootstrap CSS for layout/styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family:'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:var(--color-surface);
            color:var(--color-text);
            padding:20px;
        }
        .report-header {
            border-bottom:3px double var(--color-border);
            padding-bottom:15px;
            margin-bottom:30px;
        }
        .report-title {
            font-size:24px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:1px;
        }
        .table th {
            background-color:var(--color-surface-soft) !important;
            color:var(--color-text) !important;
            border-bottom:2px solid var(--color-border) !important;
            font-weight:700;
            text-transform:uppercase;
            font-size:11px;
        }
        .table td {
            font-size:13px;
            border-bottom:1px solid var(--color-border);
        }
        .timestamp {
            font-size:11px;
            color:var(--color-text-muted);
            text-align:right;
        }
        @media print {
            body {
                padding:0;
            }
            .no-print {
                display:none;
            }
            .page-break {
                page-break-after:always;
            }
            .table-responsive {
                overflow:visible;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Print Header -->
        <div class="report-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-800 text-uppercase" style="letter-spacing:0.5px;">Republic of the Philippines</h5>
                <h6 class="mb-1 text-muted text-uppercase" style="font-size:12px;">City Health Office - Rural Health Unit</h6>
                <div class="report-title">MNCHN Maternal Registry</div>
            </div>
            <div class="text-end">
                <div class="timestamp">Generated: {{ now()->format('F j, Y \a\t g:i A') }}</div>
                <div class="timestamp">Scope: Rural Health Unit 1</div>
                <button onclick="window.print()" class="btn btn-sm btn-dark mt-2 no-print">
                    <i class="bi bi-printer"></i> Print / Save as PDF
                </button>
            </div>
        </div>

        <!-- Registry Table -->
        <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th style="width:25%;">Patient Name</th>
                    <th style="width:10%;">Age</th>
                    <th style="width:15%;">Contact</th>
                    <th style="width:20%;">Barangay</th>
                    <th style="width:15%;">Pregnancy Status</th>
                    <th style="width:15%;">Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td class="fw-bold">{{ $patient->name }}</td>
                        <td>{{ $patient->age ?? 'N/A' }} yrs</td>
                        <td>{{ $patient->contact_number ?? 'N/A' }}</td>
                        <td>{{ $patient->barangay ?? 'N/A' }}</td>
                        <td>
                            <span class="text-uppercase fw-semibold" style="font-size:11px;">
                                {{ str_replace('_', ' ', $patient->pregnancy_status ?? 'unknown') }}
                            </span>
                        </td>
                        <td>{{ $patient->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <!-- Footer signature lines -->
        <div class="row mt-5 pt-4">
            <div class="col-12 col-sm-6 mb-4 mb-sm-0">
                <div style="border-top:1px solid var(--color-border); width:220px; max-width:100%; margin-top:40px; text-align:center;">
                    <strong style="font-size:13px;">RHU Health Officer</strong>
                    <div style="font-size:11px; color:var(--color-text-muted);">Signature over Printed Name</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 d-flex justify-content-sm-end">
                <div style="border-top:1px solid var(--color-border); width:220px; max-width:100%; margin-top:40px; text-align:center;">
                    <strong style="font-size:13px;">Data Clerk / Audited By</strong>
                    <div style="font-size:11px; color:var(--color-text-muted);">Signature over Printed Name</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Trigger print immediately if printed query parameter is passed
        if (window.location.search.includes('print=true')) {
            window.print();
        }
    </script>
</body>
</html>
