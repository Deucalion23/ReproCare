@extends(($portal ?? 'cho').'.layout')

@section('title', 'Analytics | ReproCare')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cho-analytics.css') }}?v={{ filemtime(public_path('css/cho-analytics.css')) }}">
@endpush

@section(($portal ?? 'cho').'-content')
<div class="analytics">
    <header class="an-card an-heading an-page-header-card">
        <div>
            <div class="an-eyebrow">{{ strtoupper($portal ?? 'cho') }} / Maternal health intelligence</div>
            <h1>Analytics</h1>
            <p class="an-page-subtitle mb-0" style="font-weight:600;">A live operational view of maternal care, risk flags, and barangay workloads.</p>
        </div>
        <button type="button" id="analytics-print" class="an-quiet-button an-no-print"><i class="bi bi-printer" aria-hidden="true"></i> Print report</button>
    </header>

    <form method="get" action="{{ route(($portal ?? 'cho').'.analytics') }}" class="an-card an-panel an-no-print">
        <div class="an-filter-title"><i class="bi bi-sliders2" aria-hidden="true"></i><span>Report filters</span></div>
        <div class="an-filter-grid">
            <div>
                <label for="analytics-from" class="form-label small fw-bold">From</label>
                <input type="date" id="analytics-from" name="from" class="form-control" value="{{ $report['filters']['from'] }}" max="{{ today()->toDateString() }}" required>
            </div>
            <div>
                <label for="analytics-to" class="form-label small fw-bold">To</label>
                <input type="date" id="analytics-to" name="to" class="form-control" value="{{ $report['filters']['to'] }}" max="{{ today()->toDateString() }}" required>
            </div>
            <div>
                <label for="analytics-rhu" class="form-label small fw-bold">RHU scope</label>
                @if(($portal ?? 'cho') === 'cho')
                    <select id="analytics-rhu" name="rhu" class="form-select">
                        <option value="">City-wide · All RHUs</option>
                        @foreach($rhuOptions as $rhu)<option value="{{ $rhu }}" @selected(($report['filters']['rhu'] ?? null) === $rhu)>{{ $rhu }}</option>@endforeach
                    </select>
                @else
                    <input id="analytics-rhu" class="form-control" value="{{ $report['scope_label'] }}" readonly>
                @endif
            </div>
            <div class="an-filter-area">
                <label for="analytics-area" class="form-label small fw-bold">Barangay</label>
                <select id="analytics-area" name="barangay" class="form-select">
                    <option value="">All barangays</option>
                    @if($report['filters']['barangay'] && !array_key_exists($report['filters']['barangay'], $areaOptions))
                        <option value="{{ $report['filters']['barangay'] }}" selected>{{ $report['area_label'] }} (no matching area records)</option>
                    @endif
                    @foreach($areaOptions as $key => $label)
                        <option value="{{ $key }}" @selected($report['filters']['barangay'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="an-filter-actions">
                <button class="an-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Apply filters</button>
                <a href="{{ route(($portal ?? 'cho').'.analytics') }}" class="text-secondary small">Reset</a>
            </div>
        </div>
    </form>
    @if(($portal ?? 'cho') === 'cho')
        <script>
            document.getElementById('analytics-rhu')?.addEventListener('change', function () {
                document.getElementById('analytics-area').value = '';
                this.form.submit();
            });
        </script>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="an-report-meta">
        <span><i class="bi bi-geo-alt" aria-hidden="true"></i> <strong>{{ $report['area_label'] }}</strong></span>
        <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $report['filters']['from'] }} to {{ $report['filters']['to'] }}</span>
        <span><i class="bi bi-clock" aria-hidden="true"></i> Updated {{ $report['generated_at'] }} ({{ config('app.timezone') }})</span>
    </div>
    <div class="an-report-links an-no-print"><a href="#risk-map" class="an-quiet-button"><i class="bi bi-geo-alt" aria-hidden="true"></i> Risk heat map</a><a href="#analytics-trends" class="an-quiet-button"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Trends</a></div>
    <div class="an-metrics">
        @foreach([
            ['Open pregnancies', 'open', 'Current records, including overdue', 'people'],
            ['High / Critical risk', 'high_risk', 'Current stored risk flags', 'exclamation-triangle'],
            ['New registrations', 'registrations', 'In the selected period', 'person-plus'],
            ['Maternal deaths', 'deaths', 'By date of death, selected period', 'clipboard2-pulse'],
            ['Complication events', 'complications', 'By event date, selected period', 'activity'],
        ] as [$label, $key, $hint, $icon])
            <div class="an-card an-metric" data-metric="{{ $key }}">
                <div class="an-metric-top">
                    <div class="an-metric-label">{{ $label }}</div>
                    <span class="an-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span>
                </div>
                <div class="an-number">{{ number_format($report['totals'][$key]) }}</div>
                <div class="an-subtitle">{{ $hint }}</div>
            </div>
        @endforeach
    </div>
    @include('cho.partials.analytics-risk-map')

    <div class="an-overview">
        <div class="an-overview-column">
            <section class="an-card an-panel" aria-labelledby="analytics-suggestions-title">
                <div class="an-section-heading">
                    <div class="an-title-group">
                        <span class="an-icon"><i class="bi bi-list-check" aria-hidden="true"></i></span>
                        <div>
                            <h2 id="analytics-suggestions-title">Suggested next steps</h2>
                            <p class="an-subtitle mb-0">Based on recorded data, for your team's review.</p>
                        </div>
                    </div>
                </div>
                <div class="an-suggestions">
                    @foreach($suggestions as $suggestion)
                        <article class="an-suggestion {{ $suggestion['severity'] }}">
                            <div class="an-suggestion-title">
                                <i class="bi bi-{{ $suggestion['severity'] === 'danger' ? 'exclamation-octagon' : ($suggestion['severity'] === 'warning' ? 'exclamation-circle' : 'info-circle') }}" aria-hidden="true"></i>
                                <h3>{{ $suggestion['title'] }}</h3>
                            </div>
                            <p>{{ $suggestion['evidence'] }}</p>
                            <div class="an-suggestion-action"><i class="bi bi-arrow-return-right" aria-hidden="true"></i><p>{{ $suggestion['action'] }}</p></div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
        <section class="an-card an-panel an-assistant" aria-labelledby="analytics-assistant-title">
            <div class="an-section-heading">
                <div class="an-title-group">
                    <span class="an-icon"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="analytics-assistant-title">Ask about this report</h2>
                        <p class="an-subtitle mb-0">Explore priorities, patterns and patient situations. Report answers include next steps.</p>
                    </div>
                </div>
                <span id="analytics-provider-status" class="an-badge">{{ $aiStatus['label'] === 'Groq needs setup' ? $aiStatus['label'] : 'Assistant' }}</span>
            </div>
            <form id="analytics-chat" action="{{ route(($portal ?? 'cho').'.analytics.chat') }}" class="an-no-print">
                @csrf
                <label for="analytics-question" class="form-label small fw-bold">Your question</label>
                <textarea id="analytics-question" name="question" rows="3" maxlength="500" class="form-control mb-2" placeholder="Ask about maternal health, reproductive health, ReproCare, or this report?" required></textarea>
                <div class="an-topics" role="group" aria-label="Suggested report questions">
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Which records need priority review?">Priorities</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Summarize maternal deaths.">Maternal deaths</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Summarize the monthly registration trend.">Trends</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Which barangays need follow-up planning?">Areas</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Which barangay has the most high-risk pregnancies and what should we do?">Highest high-risk area</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Which barangay has the most pregnant women right now and what should we do?">Busiest barangay</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="Which barangay has the most maternal deaths and what should we do?">Most deaths</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="List women with open pregnancies and what follow-up is needed.">Open women + to do</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="List all high-risk women and what recommended follow-up is needed.">All high-risk + to do</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="List critical-risk patients and what follow-up is needed.">Critical + to do</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="List women with no open pregnancy record and what should staff do.">No open record + to do</button>
                    <button type="button" class="an-topic" aria-pressed="false" data-analytics-question="How do I use the portal and approve records?">Portal help</button>
                </div>
                <button id="analytics-ask" class="an-button" type="submit">Ask assistant</button>
            </form>
        </section>
        <div id="analytics-chat-result" class="an-result an-result-wide" role="status" aria-live="polite" hidden>
            <div id="analytics-chat-notice" class="an-subtitle mb-2"></div>
            <div id="analytics-chat-answer" class="an-answer small"></div>
            <div id="analytics-chat-context" class="an-subtitle mt-3"></div>
        </div>
    </div>

    <div class="an-risk-events">
        <section class="an-card an-panel" aria-labelledby="analytics-risk-title">
            <div class="an-section-heading">
                <div>
                    <h2 id="analytics-risk-title">Recorded risk distribution</h2>
                    <p class="an-subtitle mb-0">Current open pregnancies by stored risk.</p>
                </div>
                <span class="an-badge">{{ $report['totals']['open'] }} open now</span>
            </div>
            <div class="an-risk-list">
                @foreach($report['risk_counts'] as $risk => $count)
                    <div class="an-risk-row an-risk-color {{ $risk }}">
                        <span class="an-risk-label"><span class="an-risk-dot" aria-hidden="true"></span>{{ $risk }}</span>
                        <div class="an-meter" aria-hidden="true"><span style="width:{{ $report['totals']['open'] ? round($count / $report['totals']['open'] * 100, 2) : 0 }}%"></span></div>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="an-card an-panel">
            <h2>Maternal deaths &amp; complications</h2>
            <p class="an-subtitle">Recorded events by month. Complications may overlap with deaths.</p>
            @include('cho.partials.analytics-chart', [
                'chartId' => 'deaths', 'chartTitle' => 'Monthly recorded maternal deaths and complications',
                'chartLabels' => array_column($report['monthly'], 'label'),
                'chartSeries' => [
                    ['label' => 'Deaths', 'color' => 'var(--color-danger)', 'values' => array_column($report['monthly'], 'deaths')],
                    ['label' => 'Complications', 'color' => 'var(--color-warning)', 'values' => array_column($report['monthly'], 'complications')],
                ],
            ])
        </section>
    </div>

    <div class="an-charts an-single-chart">
        <section class="an-card an-panel">
            <h2 id="analytics-trends">Pregnancy registration trend</h2>
            <p class="an-subtitle">New records by registration month. First and last months may be partial.</p>
            @include('cho.partials.analytics-chart', [
                'chartId' => 'registrations', 'chartTitle' => 'Monthly pregnancy registrations',
                'chartLabels' => array_column($report['monthly'], 'label'),
                'chartSeries' => [['label' => 'Registrations', 'color' => 'var(--color-purple)', 'values' => array_column($report['monthly'], 'registrations')]],
            ])
        </section>
    </div>

    <section class="an-card an-panel" aria-labelledby="analytics-rhu-areas-title">
        <div class="an-section-heading">
            <div>
                <h2 id="analytics-rhu-areas-title">Pregnant women by barangay</h2>
                <p class="an-subtitle mb-0">Current open pregnancies per catchment barangay — busiest areas first.</p>
            </div>
        </div>
        @if(count($rhuAreaChart) > 1)
            <div class="an-topics an-no-print" role="group" aria-label="Choose RHU">
                @foreach(array_keys($rhuAreaChart) as $rhu)
                    <button type="button" class="an-topic" aria-pressed="{{ $rhu === $rhuAreaDefault ? 'true' : 'false' }}" data-rhu-areas="{{ $rhu }}">{{ $rhu }}</button>
                @endforeach
            </div>
        @endif
        @foreach($rhuAreaChart as $rhu => $rows)
            @php
                $rhuOpenTotal = array_sum(array_column($rows, 'open'));
                $rhuOpenMax = max(array_merge([1], array_column($rows, 'open')));
            @endphp
            <div data-rhu-areas-panel="{{ $rhu }}" @if($rhu !== $rhuAreaDefault) hidden @endif>
                <p class="an-subtitle"><strong>{{ $rhu }}</strong> · {{ $rhuOpenTotal }} open now</p>
                @if($rhuOpenTotal > 0)
                    <div class="an-risk-list">
                        @foreach($rows as $row)
                            <div class="an-risk-row">
                                <span class="an-risk-label">{{ $row['label'] }}</span>
                                <div class="an-meter" aria-hidden="true"><span style="width:{{ round($row['open'] / $rhuOpenMax * 100, 2) }}%"></span></div>
                                <strong>{{ $row['open'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="an-subtitle">No open pregnancies recorded in {{ $rhu }}.</p>
                @endif
            </div>
        @endforeach
    </section>
    <script>
        document.querySelectorAll('[data-rhu-areas]').forEach(function (pill) {
            pill.addEventListener('click', function () {
                document.querySelectorAll('[data-rhu-areas]').forEach(function (other) {
                    other.setAttribute('aria-pressed', String(other === pill));
                });
                document.querySelectorAll('[data-rhu-areas-panel]').forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-rhu-areas-panel') !== pill.getAttribute('data-rhu-areas');
                });
            });
        });
    </script>

    <details class="an-card an-panel an-data-notes an-barangay-comparison mb-4">
        <summary>
            <span><span class="an-summary-title">Barangay comparison</span><small>Compare registrations, open pregnancies, risk flags, deaths, and complications by area.</small></span>
            <span class="an-badge">{{ count($report['areas']) }} barangays</span>
        </summary>
        <div class="an-collapsible-content">
        @php
            $chartedAreas = array_slice($report['areas'], 0, 10);
        @endphp
        @include('cho.partials.analytics-chart', [
            'chartId' => 'areas', 'chartTitle' => 'Current high-risk pregnancies and selected-period maternal deaths by barangay',
            'chartLabels' => array_column($chartedAreas, 'label'),
            'emptyDescription' => 'No High/Critical records or deaths match this chart. Other area counts are listed below.',
            'chartSeries' => [
                ['label' => 'High/Critical now', 'color' => 'var(--color-danger)', 'values' => array_column($chartedAreas, 'high_risk')],
                ['label' => 'Deaths in period (striped)', 'striped' => true, 'color' => 'var(--color-danger-text)', 'values' => array_column($chartedAreas, 'deaths')],
            ],
        ])
        <div class="table-responsive mt-3">
            <table class="table an-table an-table-numeric mb-0">
                <thead><tr><th>Barangay</th><th>Registrations<br>in period</th><th>Open<br>now</th><th>High/Critical<br>now</th><th>Deaths<br>in period</th><th>Complications<br>in period</th></tr></thead>
                <tbody>
                    @forelse($report['areas'] as $area)
                        <tr><td>{{ $area['label'] }}</td><td>{{ $area['registrations'] }}</td><td>{{ $area['open'] }}</td><td>{{ $area['high_risk'] }}</td><td>{{ $area['deaths'] }}</td><td>{{ $area['complications'] }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No records for this selection.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </details>


    <details class="an-card an-panel an-data-notes mb-4">
        <summary>Monthly data &amp; how to read this report</summary>
        <div class="table-responsive mt-3">
            <table class="table an-table an-table-numeric">
                <thead><tr><th>Month</th><th>Registrations</th><th>Maternal deaths</th><th>Complication events</th></tr></thead>
                <tbody>@foreach($report['monthly'] as $month)<tr><td>{{ $month['label'] }}</td><td>{{ $month['registrations'] }}</td><td>{{ $month['deaths'] }}</td><td>{{ $month['complications'] }}</td></tr>@endforeach</tbody>
            </table>
        </div>
        <p class="small">A zero means no matching records were found, not that no event occurred. This report excludes archived records. Event counts are not mortality ratios, incidence rates or forecasts; those require verified definitions, reporting completeness and suitable denominators.</p>
        <p class="small">Open pregnancies have no recorded end, delivery date or outcome. Known deceased patients are excluded from the current queue. Old open records may need an outcome update. Historical risk records from other pregnancies are excluded; unlinked appointments cannot be assigned to a pregnancy automatically.</p>
        <p class="small mb-0">Online AI receives your question, exact aggregate counts, numbered months and area aliases. Do not include patient details in your question. Local Ollama receives aggregate figures and your question. Neither can change risk classifications, create appointments, or send messages. Staff must verify any generated summary.</p>
    </details>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/analytics-support.js') }}?v={{ filemtime(public_path('js/analytics-support.js')) }}" defer></script>
<script type="application/json" id="analytics-filter-data">{!! \Illuminate\Support\Js::encode($report['filters']) !!}</script>
@endpush
