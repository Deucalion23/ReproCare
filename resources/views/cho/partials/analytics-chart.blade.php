@php
    $maxValue = max(array_merge([1], ...array_column($chartSeries, 'values')));
    $step = max(1, (int) ceil($maxValue / 4));
    $ceiling = $step * 4;
    $isAreaChart = $chartId === 'areas';
    $isLineChart = $chartId === 'registrations';
    $chartWidth = max(480, count($chartLabels) * ($isAreaChart ? 72 : 42) + 60);
    $labelInterval = $isAreaChart ? 1 : max(1, (int) ceil(count($chartLabels) / 8));
    $groupWidth = ($chartWidth - 60) / max(1, count($chartLabels));
    $barWidth = min(26, ($groupWidth - 16) / count($chartSeries));
    $hasValues = collect($chartSeries)->sum(fn ($series) => array_sum($series['values'])) > 0;
@endphp
<div class="an-legend">
    @foreach($chartSeries as $series)
        <span><span class="an-dot" style="background:{{ !empty($series['striped']) ? 'repeating-linear-gradient(45deg, '.$series['color'].' 0 2px, var(--color-surface) 2px 4px)' : $series['color'] }}"></span>{{ $series['label'] }}</span>
    @endforeach
</div>
@if(! $hasValues)
    <div class="an-chart-empty" role="status">
        <i class="bi bi-bar-chart" aria-hidden="true"></i>
        <div>
            <p>No matching events recorded for this chart.</p>
            <span>{{ $emptyDescription ?? 'No matching records for the selected dates and area. Monthly totals are available below.' }}</span>
        </div>
    </div>
@endif
@if($hasValues || $isLineChart)
<div class="an-chart" tabindex="0" aria-label="{{ $chartTitle }}; scroll horizontally if needed">
    <svg viewBox="0 0 {{ $chartWidth }} {{ $isAreaChart ? 285 : 250 }}" style="min-width:{{ $chartWidth }}px" role="img" aria-labelledby="chart-title-{{ $chartId }}">
        <title id="chart-title-{{ $chartId }}">{{ $chartTitle }}. Exact counts are available in the data tables.</title>
        <defs>
            @foreach($chartSeries as $seriesIndex => $series)
                @if(!empty($series['striped']))
                    <pattern id="stripe-{{ $chartId }}-{{ $seriesIndex }}" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                        <rect width="6" height="6" fill="{{ $series['color'] }}" />
                        <line x1="0" y1="0" x2="0" y2="6" stroke="var(--color-surface)" stroke-width="2" />
                    </pattern>
                @endif
            @endforeach
        </defs>
        @for($tick = 0; $tick <= 4; $tick++)
            @php
                $y = 205 - $tick * 45;
            @endphp
            <line class="an-grid-line" x1="42" y1="{{ $y }}" x2="{{ $chartWidth - 10 }}" y2="{{ $y }}" stroke="var(--color-text)" />
            <text x="34" y="{{ $y + 4 }}" text-anchor="end" font-size="11" fill="var(--color-text-muted)">{{ $tick * $step }}</text>
        @endfor
        @if($isLineChart)
            @foreach($chartSeries as $series)
                @php
                    $points = [];
                    foreach ($chartLabels as $pointIndex => $pointLabel) {
                        $points[] = (42 + $groupWidth * ($pointIndex + .5)).','.(205 - (($series['values'][$pointIndex] ?? 0) / $ceiling * 180));
                    }
                @endphp
                <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="{{ $series['color'] }}" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
            @endforeach
        @endif
        @foreach($chartLabels as $index => $label)
            @php
                $center = 42 + $groupWidth * ($index + .5);
            @endphp
            @foreach($chartSeries as $seriesIndex => $series)
                @php
                    $value = $series['values'][$index] ?? 0;
                    $height = $value / $ceiling * 180;
                    $x = $center + ($seriesIndex - count($chartSeries) / 2) * $barWidth;
                @endphp
                @if($isLineChart)
                    <circle cx="{{ $center }}" cy="{{ 205 - $height }}" r="4" fill="{{ $series['color'] }}" stroke="var(--color-surface)" stroke-width="2">
                        <title>{{ $label }}: {{ $series['label'] }} = {{ $value }}</title>
                    </circle>
                @else
                <rect x="{{ $x }}" y="{{ 205 - $height }}" width="{{ max(1, $barWidth - 3) }}" height="{{ $height }}" rx="3" fill="{{ !empty($series['striped']) ? 'url(#stripe-'.$chartId.'-'.$seriesIndex.')' : $series['color'] }}">
                    <title>{{ $label }}: {{ $series['label'] }} = {{ $value }}</title>
                </rect>
                @endif
                @if($value > 0)<text x="{{ $isLineChart ? $center : $x + ($barWidth - 3) / 2 }}" y="{{ ($isLineChart ? 196 : 200) - $height }}" text-anchor="middle" font-size="10" fill="var(--color-text)">{{ $value }}</text>@endif
            @endforeach
            @if($isAreaChart)
                <text transform="translate({{ $center }}, 224) rotate(-32)" text-anchor="end" font-size="10"><title>{{ $label }}</title>{{ \Illuminate\Support\Str::limit($label, 19) }}</text>
            @elseif($index % $labelInterval === 0 || $loop->last)
                <text x="{{ $center }}" y="230" text-anchor="middle" font-size="9"><title>{{ $label }}</title>{{ $label }}</text>
            @endif
        @endforeach
    </svg>
</div>
@endif
