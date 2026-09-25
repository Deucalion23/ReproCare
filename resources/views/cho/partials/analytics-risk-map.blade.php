<section id="risk-map" class="an-card an-panel mb-4" aria-labelledby="risk-map-title">
    <div class="an-section-heading an-map-heading">
        <div>
            <h2 id="risk-map-title">Risk heat map</h2>
        </div>
        <div class="an-no-print">
            <label for="analytics-map-layer" class="form-label small">Show on map</label>
            <select id="analytics-map-layer" class="form-select">
                <option value="risk_status">Current area risk status</option>
                <option value="high_risk">High / Critical pregnancies · now</option>
                <option value="deaths">Maternal deaths · selected period</option>
                <option value="complications">Complications · selected period</option>
                <option value="registrations">Pregnancy registrations · selected period</option>
            </select>
        </div>
    </div>
    <div class="an-legend" aria-label="Area risk status legend">
        <span><i class="an-dot" style="background:var(--color-success)"></i>Green: Low risk</span>
        <span><i class="an-dot" style="background:var(--color-warning)"></i>Amber: Medium risk</span>
        <span><i class="an-dot" style="background:var(--color-danger)"></i>Red: High / Critical or emergency</span>
        <span><i class="an-dot" style="background:var(--color-text-muted)"></i>Gray: incomplete / no assessment</span>
    </div>
    <div id="analytics-risk-map" class="an-map-canvas" aria-label="Maternal health area map" data-boundaries-url="{{ asset('data/san-carlos-city-barangays.geojson') }}"></div>
    <p id="analytics-map-status" class="an-subtitle mt-2" role="status">Loading map. All counts remain available in the barangay comparison table.</p>
    <noscript><p>The interactive map requires JavaScript. Use the barangay comparison table below.</p></noscript>
    @if(!count($mapData['areas']))
        <p class="an-subtitle mt-2">Showing the city base map. No matching areas have stored coordinates, so no patient or risk markers are placed. Counts remain available in the barangay comparison table below.</p>
    @endif
    @if(count($mapData['unmapped']))
        <details class="mt-2"><summary>{{ count($mapData['unmapped']) }} area(s) without mapped coordinates</summary><p class="an-subtitle">{{ implode('; ', $mapData['unmapped']) }}</p></details>
    @endif
    <div class="an-indicators">
        <div class="an-indicators-head">
            <h3>Current area indicators</h3>
            <span class="an-badge">{{ collect($mapData['statuses'])->count() }} areas</span>
        </div>
        @php
            $riskGroups = ['danger' => 'High / Critical', 'warning' => 'Medium', 'success' => 'Low', 'text-muted' => 'Unassessed'];
            $groupedAreas = collect($mapData['statuses'])->groupBy('status_color');
        @endphp
        <div class="an-indicator-chips">
            @foreach($riskGroups as $color => $label)
                <div class="an-indicator-chip an-group-{{ $color }}">
                    <i class="an-dot" aria-hidden="true"></i>
                    <div><strong>{{ $groupedAreas->get($color, collect())->count() }}</strong><br><span>{{ $label }} areas</span></div>
                </div>
            @endforeach
        </div>
        <div class="table-responsive">
            <table class="table an-table align-middle">
                <thead><tr><th scope="col">Location</th><th scope="col">RHU</th><th scope="col">Status</th><th scope="col" class="an-num">Low</th><th scope="col" class="an-num">Med</th><th scope="col" class="an-num">High</th><th scope="col" class="an-num">Unass.</th><th scope="col">Notes</th></tr></thead>
                @foreach($riskGroups as $color => $label)
                    @php($group = $groupedAreas->get($color, collect())->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE))
                    @if($group->isNotEmpty())
                        <tbody>
                            <tr class="an-group-head an-group-{{ $color }}"><th colspan="8">
                                <button type="button" class="an-risk-toggle an-group-toggle" id="risk-toggle-{{ $color }}" aria-expanded="true" aria-controls="risk-rows-{{ $color }}">
                                    <i class="bi bi-chevron-down an-risk-chevron" aria-hidden="true"></i>
                                    <i class="an-dot" aria-hidden="true"></i>
                                    {{ $label }}
                                    <span class="an-badge">{{ $group->count() }}</span>
                                    <span class="an-risk-toggle-hint">Collapse</span>
                                </button>
                            </th></tr>
                        </tbody>
                        <tbody id="risk-rows-{{ $color }}" class="an-risk-group-rows" aria-labelledby="risk-toggle-{{ $color }}">
                            @foreach($group as $areaStatus)
                                <tr>
                                    <th scope="row" class="an-loc">{{ $areaStatus['label'] }}</th>
                                    <td><span class="an-rhu">{{ $areaStatus['rhu_assignment'] }}</span></td>
                                    <td><span class="an-status-pill an-group-{{ $color }}"><i class="an-dot" aria-hidden="true"></i>{{ $areaStatus['status_label'] }}</span></td>
                                    <td class="an-num">{{ $areaStatus['risk_counts']['Low'] }}</td><td class="an-num">{{ $areaStatus['risk_counts']['Medium'] }}</td><td class="an-num">{{ $areaStatus['high_risk'] }}</td><td class="an-num">{{ $areaStatus['risk_counts']['Unassessed'] }}</td>
                                    <td><span class="an-note">@if(!$areaStatus['has_coordinates'])No saved coordinates.@elseif($areaStatus['emergencies'] > 0){{ $areaStatus['emergencies'] }} emergency flag(s).@else &mdash; @endif</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                @endforeach
                @if($groupedAreas->isEmpty())<tbody><tr><td colspan="8" class="text-center text-muted py-4">No area records match this scope.</td></tr></tbody>@endif
            </table>
        </div>
    </div>
</section>
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush
@push('scripts')
<script type="application/json" id="analytics-map-data">{!! json_encode($mapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/analytics-map.js') }}?v={{ filemtime(public_path('js/analytics-map.js')) }}"></script>
@endpush
