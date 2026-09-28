<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }} - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size:12pt;
                line-height:1.4;
            }
            .no-print {
                display:none !important;
            }
            .page-break {
                page-break-before:always;
            }
            .container-fluid {
                padding:0;
            }
            table {
                font-size:10pt;
            }
            .card {
                border:1px solid var(--color-border) !important;
                break-inside:avoid;
            }
        }

        @media screen {
            body {
                background-color:var(--color-surface-soft);
                padding:20px;
            }
            .print-container {
                max-width:1000px;
                margin:0 auto;
                background:var(--color-surface);
                padding:40px;
                box-shadow:0 0 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent);
            }
        }

        .report-header {
            border-bottom:3px solid var(--color-info);
            padding-bottom:20px;
            margin-bottom:30px;
        }

        .org-logo {
            width:80px;
            height:80px;
            background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary) 100%);
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--color-on-solid);
            font-size:2rem;
            font-weight:bold;
        }

        .stats-grid {
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:15px;
            margin-bottom:30px;
        }

        .stat-box {
            background:var(--color-surface-soft);
            border:1px solid var(--color-border);
            border-radius:8px;
            padding:15px;
            text-align:center;
        }

        .stat-number {
            font-size:2rem;
            font-weight:bold;
            color:var(--color-info-text);
        }

        .stat-label {
            font-size:0.875rem;
            color:var(--color-text-muted);
        }

        .table th {
            background-color:var(--color-surface-soft) !important;
            font-weight:600;
        }

        .risk-badge {
            display:inline-block;
            padding:4px 8px;
            border-radius:4px;
            font-size:0.75rem;
            font-weight:600;
        }

        .risk-low {
            background-color:var(--color-success-soft);
            color:var(--color-success-text);
        }

        .risk-medium {
            background-color:var(--color-warning-soft);
            color:var(--color-warning-text);
        }

        .risk-high {
            background-color:var(--color-danger-soft);
            color:var(--color-danger-text);
        }

        .bhw-signature {
            margin-top:50px;
            border-top:1px solid var(--color-border);
            padding-top:20px;
        }

        .signature-line {
            border-bottom:1px solid var(--color-border);
            width:300px;
            margin-top:60px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Print Controls (Screen only) -->
        <div class="no-print mb-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Print Preview</h5>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Now
                </button>
                <a href="{{ route('bhw.reports.show', $report->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Report
                </a>
            </div>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="org-logo">RC</div>
                </div>
                <div class="col">
                    <h3 class="mb-1">ReproCare</h3>
                    <p class="text-muted mb-0">Reproductive Healthcare Management System</p>
                    <p class="text-muted mb-0">Barangay Health Worker Monthly Report</p>
                </div>
                <div class="col-auto text-end">
                    <div class="text-muted small">Report Generated</div>
                    <div class="fw-bold">{{ now()->format('F d, Y \a\\t g:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Report Title -->
        <div class="text-center mb-4">
            <h2 class="mb-2">{{ $report->title }}</h2>
            <p class="lead text-muted">{{ $report->reportPeriod }}</p>
            @if($report->description)
                <p class="text-muted">{{ $report->description }}</p>
            @endif
        </div>

        <!-- BHW Information -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">BHW Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td width="120" class="text-muted">Name:</td>
                                <td class="fw-medium">{{ $report->bhw->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td>{{ $report->bhw->email }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Barangay:</td>
                                <td>{{ $report->bhw->barangay ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td width="150" class="text-muted">Report Period:</td>
                                <td class="fw-medium">{{ $report->reportPeriod }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Records:</td>
                                <td class="fw-medium">{{ $report->total_records }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Unique Patients:</td>
                                <td>{{ $uniquePatients }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number text-primary">{{ $report->total_records }}</div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-box">
                <div class="stat-number text-info">{{ $uniquePatients }}</div>
                <div class="stat-label">Unique Patients</div>
            </div>
            <div class="stat-box">
                <div class="stat-number text-success">{{ $riskDistribution['low'] }}</div>
                <div class="stat-label">Low Risk</div>
            </div>
            <div class="stat-box">
                <div class="stat-number text-danger">{{ $riskDistribution['high'] }}</div>
                <div class="stat-label">High Risk</div>
            </div>
        </div>

        <!-- Health Records Table -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Health Records Detail</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>BP</th>
                                <th>Weight</th>
                                <th>HR</th>
                                <th>Temp</th>
                                <th>Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($healthRecords as $index => $record)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $record->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-medium">{{ optional($record->woman)->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>{{ $record->bp }}</td>
                                    <td>{{ $record->weight }} kg</td>
                                    <td>{{ $record->heart_rate }}</td>
                                    <td>{{ $record->temperature }}°C</td>
                                    <td>
                                        @if($record->risk_level === 'High')
                                            <span class="risk-badge risk-high">HIGH</span>
                                        @elseif($record->risk_level === 'Medium')
                                            <span class="risk-badge risk-medium">MEDIUM</span>
                                        @else
                                            <span class="risk-badge risk-low">LOW</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($record->notes)
                                    <tr class="table-light">
                                        <td></td>
                                        <td colspan="7">
                                            <small><strong>Notes:</strong> {{ $record->notes }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        No health records found for this report period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="bhw-signature">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-line"></div>
                    <p class="mt-2 mb-0 fw-medium">{{ $report->bhw->name }}</p>
                    <p class="text-muted small">Barangay Health Worker</p>
                    <p class="text-muted small">Date: {{ now()->format('F d, Y') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted small mb-0">Report ID: #{{ $report->id }}</p>
                    <p class="text-muted small">Generated by ReproCare System</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top text-center text-muted small">
            <p class="mb-0">This report was generated electronically by the ReproCare Health Management System.</p>
            <p class="mb-0">For verification, please contact your assigned midwife or health center.</p>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog if not already printed
        @if(!$report->printed_at)
            window.onload = function() {
                // Uncomment below to auto-print on load
                // window.print();
            };
        @endif
    </script>
</body>
</html>
