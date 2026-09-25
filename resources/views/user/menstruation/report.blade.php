<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menstrual Cycle Report - {{ $user->name }}</title>
    <style>
        body {
            font-family:"Segoe UI", Arial, sans-serif;
            line-height:1.6;
            color:var(--color-text);
            margin:0;
            background:var(--color-primary-soft);
            padding:28px;
        }
        .page {
            max-width:980px;
            margin:0 auto;
            background:var(--color-surface);
            border-radius:24px;
            box-shadow:0 24px 60px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent);
            overflow:hidden;
        }
        .header {
            text-align:left;
            padding:32px;
            background:linear-gradient(135deg, var(--color-primary-text) 0%, var(--color-primary) 58%, var(--color-secondary) 100%);
            color:var(--color-on-solid);
        }
        .header h1 {
            margin:0;
            font-size:28px;
            letter-spacing:-0.03em;
        }
        .header p {
            margin:6px 0 0;
            color:color-mix(in srgb, var(--color-on-solid) 86%, transparent);
        }
        .content {
            padding:28px 32px 36px;
        }
        .info-box {
            background:var(--color-secondary-soft);
            border:none;
            padding:18px 20px;
            border-radius:18px;
            margin-bottom:20px;
        }
        .info-box h3 {
            margin-top:0;
            color:var(--color-secondary-text);
            font-size:16px;
        }
        .stats-grid {
            display:flex;
            justify-content:space-between;
            flex-wrap:wrap;
            gap:14px;
            margin-bottom:30px;
        }
        .table-scroll { overflow-x:auto; margin-bottom:20px; border-radius:16px; }
        .table-scroll table { margin-bottom:0; }
        @media (max-width: 640px) {
            body { padding:14px; }
            .header { padding:24px 20px; }
            .header h1 { font-size:22px; }
            .content { padding:20px 18px 28px; }
            .stat-item { flex:1 1 140px; }
            th, td { padding:9px 8px; font-size:13px; }
        }
        .stat-item {
            text-align:center;
            padding:18px 16px;
            background:linear-gradient(180deg, var(--color-secondary-soft) 0%, var(--color-secondary-soft) 100%);
            border:none;
            border-radius:18px;
            flex:1;
        }
        .stat-value {
            font-size:30px;
            font-weight:700;
            color:var(--color-primary-text);
        }
        .stat-label {
            font-size:12px;
            color:var(--color-text-muted);
            text-transform:uppercase;
            letter-spacing:0.08em;
        }
        table {
            width:100%;
            border-collapse:collapse;
            margin-bottom:20px;
            overflow:hidden;
            border-radius:16px;
        }
        th, td {
            padding:12px;
            text-align:left;
            border-bottom:1px solid var(--color-border);
        }
        th {
            background-color:var(--color-primary-text);
            color:var(--color-on-solid);
            font-weight:bold;
        }
        .normal {
            color:var(--color-success-text);
            font-weight:bold;
        }
        .abnormal {
            color:var(--color-danger-text);
            font-weight:bold;
        }
        .section-title {
            background:linear-gradient(135deg, var(--color-primary-text) 0%, var(--color-primary) 100%);
            color:var(--color-on-solid);
            padding:12px 16px;
            margin:30px 0 15px 0;
            font-size:16px;
            font-weight:bold;
            border-radius:14px;
        }
        .footer {
            text-align:center;
            margin-top:40px;
            padding-top:20px;
            border-top:1px solid var(--color-border);
            font-size:12px;
            color:var(--color-text-muted);
        }
        .note {
            background-color:var(--color-warning-soft);
            border:1px solid var(--color-warning);
            border-left:4px solid var(--color-warning);
            padding:12px 15px;
            margin:15px 0;
            border-radius:12px;
        }
    </style>
</head>
<body>
    <div class="page">
    <div class="header">
        <h1>Menstrual Cycle Medical Report</h1>
        <p><strong>ReproCare Health System</strong></p>
        <p>Generated on: {{ now()->format('F j, Y') }}</p>
    </div>
    <div class="content">

    <div class="info-box">
        <h3>Patient Information</h3>
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Age:</strong> {{ $user->birth_date ? $user->birth_date->age . ' years' : 'Not provided' }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value">{{ $averageCycle ?? 'N/A' }}</div>
            <div class="stat-label">Avg Cycle Length</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $averagePeriod ?? 'N/A' }}</div>
            <div class="stat-label">Avg Period Duration</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $records->count() }}</div>
            <div class="stat-label">Total Periods Recorded</div>
        </div>
    </div>

    <div class="section-title">Cycle History (Last 6 Periods)</div>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Period #</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Duration</th>
                <th>Cycle Length</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $prevDate = null; @endphp
            @foreach($records as $index => $record)
                @php
                    $cycleLength = $prevDate ? $prevDate->diffInDays($record->period_start_date, false) : null;
                    $isNormal = $cycleLength ? ($cycleLength >= 21 && $cycleLength <= 35) : null;
                    $prevDate = $record->period_start_date;
                @endphp
                <tr>
                    <td>{{ $records->count() - $index }}</td>
                    <td>{{ $record->period_start_date->format('M d, Y') }}</td>
                    <td>{{ $record->period_end_date ? $record->period_end_date->format('M d, Y') : 'Ongoing' }}</td>
                    <td>{{ $record->period_length ?? '-' }} {{ $record->period_length ? ($record->period_length != 1 ? 'days' : 'day') : '' }}</td>
                    <td>{{ $cycleLength ? $cycleLength . ' days' : '-' }}</td>
                    <td>
                        @if($isNormal === null)
                            -
                        @elseif($isNormal)
                            <span class="normal">Normal</span>
                        @else
                            <span class="abnormal">Abnormal</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="note">
        <strong>Note:</strong> Normal menstrual cycle length ranges from 21 to 35 days. Cycles outside this range may indicate hormonal irregularities or other health conditions requiring medical attention.
    </div>

    <div class="section-title">Daily Tracking Summary (Last 3 Months)</div>
    @if($dailyData->count() > 0)
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Period</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyData->take(20) as $day)
                    <tr>
                        <td>{{ $day->date->format('M d, Y') }}</td>
                        <td>{{ $day->is_period ? 'Yes' : 'No' }}</td>
                        <td>{{ $day->notes ? \Illuminate\Support\Str::limit($day->notes, 30) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @if($dailyData->count() > 20)
            <p style="text-align:center; color:var(--color-text-muted); font-style:italic;">... and {{ $dailyData->count() - 20 }} more entries</p>
        @endif
    @else
        <p style="text-align:center; color:var(--color-text-muted);">No daily tracking data recorded in the last 3 months.</p>
    @endif

    <div class="section-title">Medical Interpretation</div>
    <div class="info-box">
        <h3>Cycle Pattern Analysis</h3>
        @if($averageCycle)
            <p><strong>Average Cycle Length:</strong> {{ $averageCycle }} days</p>
            <p>
                @if($averageCycle >= 21 && $averageCycle <= 35)
                    <span class="normal">✓ Within normal range (21-35 days)</span>
                @elseif($averageCycle < 21)
                    <span class="abnormal">⚠ Short cycle - May indicate hormonal imbalance or polycystic ovary syndrome (PCOS)</span>
                @else
                    <span class="abnormal">⚠ Long cycle - May indicate anovulation, thyroid issues, or other hormonal conditions</span>
                @endif
            </p>
        @else
            <p>Insufficient data to determine cycle pattern.</p>
        @endif

        @if($averagePeriod)
            <p style="margin-top:15px;"><strong>Average Period Duration:</strong> {{ $averagePeriod }} days</p>
            <p>
                @if($averagePeriod >= 2 && $averagePeriod <= 7)
                    <span class="normal">✓ Normal period duration (2-7 days)</span>
                @elseif($averagePeriod > 7)
                    <span class="abnormal">⚠ Prolonged bleeding - May indicate fibroids, polyps, or hormonal issues</span>
                @else
                    <span class="abnormal">⚠ Very short period - May indicate hormonal imbalance</span>
                @endif
            </p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated by ReproCare Health System</p>
        <p>For medical advice, please consult with your healthcare provider</p>
        <p style="margin-top:10px;"><strong>Report ID:</strong> MCR-{{ $user->id }}-{{ now()->format('Ymd') }}</p>
    </div>
    </div>
    </div>
</body>
</html>
