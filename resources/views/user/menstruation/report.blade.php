<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Menstrual Cycle Report - {{ $user->name }}</title>
    <style>
        body { font-family: dejavusans, sans-serif; color: #1e293b; font-size: 9pt; line-height: 1.35; }
        h1, h2, h3, p { margin: 0; }
        .header { background-color: #6d3caf; color: #ffffff; padding: 16px 18px; }
        .header h1 { font-size: 20pt; }
        .header p { font-size: 9pt; margin-top: 4px; color: #f3e8ff; }
        .section-title { background-color: #f3e8ff; border-left: 4px solid #7c3aed; color: #4c1d95; font-size: 12pt; font-weight: bold; margin: 18px 0 8px; padding: 7px 10px; }
        .info-table, .stats-table, .report-table { border-collapse: collapse; width: 100%; }
        .info-table td { border: 1px solid #ddd6fe; padding: 8px 10px; }
        .info-label { background-color: #faf5ff; color: #5b21b6; font-weight: bold; width: 25%; }
        .stats-table { margin: 10px 0 4px; }
        .stats-table td { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center; width: 33.33%; }
        .stat-value { color: #6d3caf; font-size: 20pt; font-weight: bold; }
        .stat-label { color: #64748b; font-size: 7.5pt; margin-top: 3px; text-transform: uppercase; }
        .report-table { font-size: 8pt; }
        .report-table th { background-color: #6d3caf; color: #ffffff; font-weight: bold; padding: 7px 5px; text-align: left; }
        .report-table td { border: 1px solid #e2e8f0; padding: 6px 5px; vertical-align: top; }
        .report-table tr:nth-child(even) td { background-color: #fafafa; }
        .normal { color: #15803d; font-weight: bold; }
        .abnormal { color: #b91c1c; font-weight: bold; }
        .note { background-color: #fffbeb; border: 1px solid #fbbf24; color: #78350f; margin-top: 10px; padding: 9px 11px; }
        .muted { color: #64748b; }
        .footer { border-top: 1px solid #cbd5e1; color: #64748b; font-size: 8pt; margin-top: 22px; padding-top: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Menstrual Cycle Medical Report</h1>
        <p>ReproCare Health System | Generated {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="section-title">Patient Information</div>
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr><td class="info-label">Name</td><td>{{ $user->name }}</td></tr>
        <tr><td class="info-label">Email</td><td>{{ $user->email }}</td></tr>
        <tr><td class="info-label">Age</td><td>{{ $user->age !== null ? $user->age . ' years' : 'Not provided' }}</td></tr>
    </table>

    <table class="stats-table" cellpadding="0" cellspacing="0">
        <tr>
            <td><div class="stat-value">{{ $averageCycle ?? 'N/A' }}</div><div class="stat-label">Average Cycle Length</div></td>
            <td><div class="stat-value">{{ $averagePeriod ?? 'N/A' }}</div><div class="stat-label">Average Period Duration</div></td>
            <td><div class="stat-value">{{ $records->count() }}</div><div class="stat-label">Periods Recorded</div></td>
        </tr>
    </table>

    <div class="section-title">Cycle History (Last 6 Periods)</div>
    <table class="report-table" cellpadding="0" cellspacing="0">
        <thead><tr><th width="7%">#</th><th width="19%">Start Date</th><th width="19%">End Date</th><th width="16%">Duration</th><th width="20%">Cycle Length</th><th width="19%">Status</th></tr></thead>
        <tbody>
            @php $previousDate = null; @endphp
            @forelse($records as $index => $record)
                @php
                    $cycleLength = $previousDate ? abs($previousDate->diffInDays($record->period_start_date, false)) : null;
                    $isNormal = $cycleLength !== null ? ($cycleLength >= 21 && $cycleLength <= 35) : null;
                    $previousDate = $record->period_start_date;
                @endphp
                <tr>
                    <td>{{ $records->count() - $index }}</td>
                    <td>{{ $record->period_start_date->format('M d, Y') }}</td>
                    <td>{{ $record->period_end_date ? $record->period_end_date->format('M d, Y') : 'Ongoing' }}</td>
                    <td>{{ $record->period_length ? $record->period_length . ' day' . ($record->period_length != 1 ? 's' : '') : '-' }}</td>
                    <td>{{ $cycleLength ? $cycleLength . ' days' : '-' }}</td>
                    <td>@if($isNormal === null)-@elseif($isNormal)<span class="normal">Within range</span>@else<span class="abnormal">Outside range</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No period records are available.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="note"><strong>Clinical note:</strong> Typical menstrual cycles range from 21 to 35 days. This report supports discussion with a healthcare provider and is not a diagnosis.</div>

    <div class="section-title">Daily Tracking Summary (Last 3 Months)</div>
    @if($dailyData->isNotEmpty())
        <table class="report-table" cellpadding="0" cellspacing="0">
            <thead><tr><th width="22%">Date</th><th width="16%">Period</th><th width="62%">Notes</th></tr></thead>
            <tbody>
                @foreach($dailyData->take(20) as $day)
                    <tr><td>{{ $day->date->format('M d, Y') }}</td><td>{{ $day->is_period ? 'Yes' : 'No' }}</td><td>{{ $day->notes ? \Illuminate\Support\Str::limit($day->notes, 90) : '-' }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @if($dailyData->count() > 20)<p class="muted" style="margin-top:6px;">{{ $dailyData->count() - 20 }} additional daily entries are not shown.</p>@endif
    @else
        <p class="muted">No daily tracking data was recorded in the last three months.</p>
    @endif

    <div class="section-title">Cycle Pattern Summary</div>
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr><td class="info-label">Average cycle</td><td>@if($averageCycle){{ $averageCycle }} days — <span class="{{ $averageCycle >= 21 && $averageCycle <= 35 ? 'normal' : 'abnormal' }}">{{ $averageCycle >= 21 && $averageCycle <= 35 ? 'within the typical range' : 'outside the typical range' }}</span>@else Insufficient data @endif</td></tr>
        <tr><td class="info-label">Average period</td><td>@if($averagePeriod){{ $averagePeriod }} days — <span class="{{ $averagePeriod >= 2 && $averagePeriod <= 7 ? 'normal' : 'abnormal' }}">{{ $averagePeriod >= 2 && $averagePeriod <= 7 ? 'within the typical range' : 'outside the typical range' }}</span>@else Insufficient data @endif</td></tr>
    </table>

    <div class="footer">Generated by ReproCare Health System<br>For medical advice, please consult your healthcare provider.<br>Report ID: MCR-{{ $user->id }}-{{ now()->format('Ymd') }}</div>
</body>
</html>
