@extends('cho.layout')

@section('title', 'Dashboard - CHO Portal | ReproCare')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    /* Per-metric icon tints — borderless, semantic */
    body .main-content .stat-card.stat-purple .stat-icon { background:var(--color-primary-soft) !important; background-color:var(--color-primary-soft) !important; color:var(--color-primary-text) !important; }
    body .main-content .stat-card.stat-rose .stat-icon { background:var(--color-secondary-soft) !important; background-color:var(--color-secondary-soft) !important; color:var(--color-secondary-text) !important; }
    body .main-content .stat-card.stat-cyan .stat-icon { background:var(--color-peach-soft) !important; background-color:var(--color-peach-soft) !important; color:var(--color-warning-text) !important; }
    body .main-content .stat-card.stat-danger .stat-icon { background:var(--color-danger-soft) !important; background-color:var(--color-danger-soft) !important; color:var(--color-danger-text) !important; }
    body .main-content .stat-card .stat-icon { border:none !important; box-shadow:none !important; }
    body .main-content .stat-card { border:none !important; }
    .cho-dashboard-risk-card #cho-dashboard-risk-map { background:var(--color-surface-soft); }
    .cho-dashboard-risk-card .cho-risk-pin { display:grid; place-items:center; width:30px; height:30px; border-radius:50%; background:var(--color-surface); border:2.5px solid var(--cho-pin, var(--color-text-muted)); color:var(--cho-pin, var(--color-text)); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); font-size:.72rem; font-weight:800; line-height:1; font-variant-numeric:tabular-nums; }
    .cho-dashboard-risk-card .cho-risk-legend { display:flex; flex-wrap:wrap; gap:10px 18px; font-size:.72rem; color:var(--color-text-muted); padding:10px 16px 0; }
    .cho-dashboard-risk-card .cho-dot { display:inline-block; width:8px; height:8px; border-radius:3px; margin-right:5px; }
    .cho-dashboard-risk-card .cho-pin-danger { --cho-pin:var(--color-danger); }
    .cho-dashboard-risk-card .cho-pin-warning { --cho-pin:var(--color-warning); }
    .cho-dashboard-risk-card .cho-pin-success { --cho-pin:var(--color-success); }
    .cho-dashboard-risk-card .cho-pin-purple { --cho-pin:var(--color-purple); }
    .cho-dashboard-risk-card .cho-pin-text-muted { --cho-pin:var(--color-text-muted); }
</style>
@endpush

@section('cho-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
@endphp

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">
                Good {{ $timeOfDay }}, {{ auth()->user()->name }}!
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
            </p>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3" style="position:relative;z-index:1;">
        <span class="summary-chip chip-primary" style="padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-shield-check me-1"></i> ANC Coverage: {{ $ancCoverageRate }}%
        </span>
        <span class="summary-chip chip-success" style="padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-people-fill me-1"></i> {{ $totalPatients }} Patients Enrolled
        </span>
        @if($pendingSupplyRequests > 0)
        <span class="summary-chip chip-warning" style="background:color-mix(in srgb, var(--color-warning) 25%, transparent); border:1px solid color-mix(in srgb, var(--color-warning) 40%, transparent); color:var(--color-warning-text); padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-box-seam me-1"></i> {{ $pendingSupplyRequests }} Pending Supplies
        </span>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Total Staff Accounts</div>
            <div class="stat-number" data-count="{{ $totalUsers }}">0</div>
            <div class="stat-trend">
                <i class="bi bi-shield-lock-fill"></i> Health workers & admins
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-rose fade-in-card">
            <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="stat-label">Active Pregnancies</div>
            <div class="stat-number" data-count="{{ $activePregnancies }}">0</div>
            <div class="stat-trend">
                <i class="bi bi-activity"></i> Monitoring city-wide
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
            <div class="stat-label">Supply Requests</div>
            <div class="stat-number" data-count="{{ $totalSupplyRequests }}">0</div>
            <div class="stat-trend up">
                <i class="bi bi-clock-history"></i> {{ $pendingSupplyRequests }} pending review
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-danger fade-in-card">
            <div class="stat-icon"><i class="bi bi-journal-x"></i></div>
            <div class="stat-label">Maternal Deaths</div>
            <div class="stat-number" data-count="{{ $totalDeaths }}">0</div>
            <div class="stat-trend danger">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $totalNearMiss }} Near-miss events
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Recent Supply Requests --}}
    <div class="col-lg-8 align-self-start">
        <div class="card fade-in-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Recent Supply Requests
                </h5>
                <a href="{{ route('cho.supply-requests.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Requested By</th>
                                    <th>Quantity</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $request)
                                    <tr>
                                        <td>
                                            <span style="font-weight:600;">{{ $request->supply_name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ ucfirst($request->supply_category) }}</div>
                                        </td>
                                        <td style="font-size:0.875rem;">
                                            {{ $request->requestedBy->name ?? 'Unknown' }}
                                        </td>
                                        <td style="font-size:0.875rem;">
                                            {{ $request->quantity_requested }} {{ $request->unit ?? 'pcs' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->urgency === 'emergency' ? 'danger' : ($request->urgency === 'urgent' ? 'warning' : 'info') }} text-white text-xs">
                                                {{ ucfirst($request->urgency) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'declined' ? 'danger' : 'secondary') }} text-white text-xs">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('cho.supply-requests.show', $request->id) }}" class="btn btn-xs btn-primary py-1 px-2" style="font-size:0.75rem; border-radius:8px;">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam" style="font-size:2.5rem; color:var(--text-muted);"></i>
                        <h6 class="mt-3">No Supply Requests</h6>
                        <p class="text-muted text-xs">Supply requests from RHU centers will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Risk heat map: in this column so it directly follows supply requests. --}}
        <div class="card fade-in-card shadow-sm border mt-4 cho-dashboard-risk-card" style="border-radius:16px; background:var(--bg-card); border-color:var(--border) !important;">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                <div>
                    <h5 class="fw-800 mb-0 text-dark d-flex align-items-center gap-2 cho-risk-map-title" style="font-family:'Plus Jakarta Sans',sans-serif;"><i class="bi bi-geo-alt-fill" style="color:var(--color-danger-text);"></i> Risk heat map — San Carlos City, Pangasinan</h5>
                    <small class="text-muted">Live barangay risk indicators. Shape color and number show the current recorded workload.</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                    <select id="cho-dashboard-risk-layer" class="form-select form-select-sm" aria-label="Risk map indicator" style="width:210px; border-radius:8px; font-size:.75rem;">
                        <option value="risk_status">Current area risk status</option>
                        <option value="high_risk">High / Critical pregnancies</option>
                        <option value="deaths">Maternal deaths</option>
                        <option value="complications">Complications</option>
                        <option value="registrations">Pregnancy registrations</option>
                    </select>
                    <a href="{{ route('cho.analytics') }}#risk-map" class="btn btn-xs btn-outline-secondary" style="border-radius:8px; font-size:.75rem;">
                        <i class="bi bi-arrows-fullscreen me-1"></i> Full map
                    </a>
                </div>
            </div>
            <div class="card-body p-0 overflow-hidden" style="border-radius:0 0 16px 16px;">
                <div class="cho-risk-legend" aria-label="Area risk status legend">
                    <span><i class="cho-dot" style="background:var(--color-success)"></i>Green: Low risk</span>
                    <span><i class="cho-dot" style="background:var(--color-warning)"></i>Amber: Medium risk</span>
                    <span><i class="cho-dot" style="background:var(--color-danger)"></i>Red: High / Critical or emergency</span>
                    <span><i class="cho-dot" style="background:var(--color-text-muted)"></i>Gray: incomplete / no assessment</span>
                </div>
                <div id="cho-dashboard-risk-map" style="height:380px;"></div>
                <p id="cho-dashboard-map-status" class="text-muted px-3 py-2 mb-0" style="font-size:.75rem;" role="status"></p>
            </div>
        </div>
    </div>

    {{-- Right: Quick Actions & Recent Deaths --}}
    <div class="col-lg-4">
        {{-- Quick Actions --}}
        <div class="card fade-in-card mb-4">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('cho.users.create') }}" class="quick-action-tile qa-green" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1rem; border-radius:12px; text-decoration:none; transition:all var(--transition-base);">
                            <i class="bi bi-person-plus-fill mb-2" style="font-size:1.5rem;"></i>
                            <span style="font-size:0.8rem; font-weight:600;">Add Health Worker</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('cho.users.index') }}" class="quick-action-tile qa-blue" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1rem; border-radius:12px; text-decoration:none; transition:all var(--transition-base);">
                            <i class="bi bi-people-fill mb-2" style="font-size:1.5rem;"></i>
                            <span style="font-size:0.8rem; font-weight:600;">Manage Accounts</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('cho.analytics') }}" class="quick-action-tile qa-violet" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1rem; border-radius:12px; text-decoration:none; transition:all var(--transition-base);">
                            <i class="bi bi-bar-chart-fill mb-2" style="font-size:1.5rem;"></i>
                            <span style="font-size:0.8rem; font-weight:600;">View Analytics</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('cho.maternal-deaths.index') }}" class="quick-action-tile qa-red" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1rem; border-radius:12px; text-decoration:none; transition:all var(--transition-base);">
                            <i class="bi bi-journal-x mb-2" style="font-size:1.5rem;"></i>
                            <span style="font-size:0.8rem; font-weight:600;">Maternal Audit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Maternal Deaths --}}
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Recent Deaths overview
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentDeaths->count() > 0)
                    <ul class="list-group list-group-flush mb-0">
                        @foreach($recentDeaths as $death)
                            <li class="list-group-item bg-transparent" style="border-color:var(--border); padding:0.85rem 1.25rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div style="font-weight:600; font-size:0.875rem;">
                                            {{ $death->patient_name }}
                                        </div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">
                                            {{ $death->death_date->format('M j, Y') }} &bull; {{ $death->age_at_death ?? 'N/A' }} yrs old
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $death->audit_status === 'closed' ? 'success' : 'warning' }} text-white text-xs">
                                        {{ ucfirst($death->audit_status) }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shield-check" style="font-size:2rem; color:var(--success);"></i>
                        <p class="text-muted text-xs mt-2 mb-0">No maternal deaths recorded.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script type="application/json" id="cho-dashboard-risk-data">{!! json_encode($dashboardMapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script type="application/json" id="cho-dashboard-barangay-boundaries">{!! file_get_contents(public_path('data/san-carlos-city-barangays.geojson')) !!}</script>
<script>
function animateStatNumbers() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        if (target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, 30);
        } else {
            stat.textContent = 0;
        }
    });
}
document.addEventListener('DOMContentLoaded', animateStatNumbers);

document.addEventListener('DOMContentLoaded', function () {
    var element = document.getElementById('cho-dashboard-risk-map');
    var source = document.getElementById('cho-dashboard-risk-data');
    var boundariesSource = document.getElementById('cho-dashboard-barangay-boundaries');
    var select = document.getElementById('cho-dashboard-risk-layer');
    var statusEl = document.getElementById('cho-dashboard-map-status');
    if (!element || !source || !select || !window.L) return;
    var areas = JSON.parse(source.textContent).areas || [];
    var map = L.map(element).setView([15.9281, 120.3478], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    var layers = L.layerGroup().addTo(map);
    var root = getComputedStyle(document.documentElement);
    var color = function (token) { return root.getPropertyValue('--color-' + token).trim(); };
    var aliases = { 'burgos': 'burgos padlan', 'burgos st': 'burgos padlan', 'barangay burgos padlan, san carlos city, pangasinan': 'burgos padlan', 'san pedro st': 'san pedro-taloy', 'bugallon st': 'bugallon-posadas street', 'caoayan kiling': 'caoayan-kiling', 'pnr site': 'pnr station site', 'nelintap': 'nilentap', 'padilla st': 'padilla-gomez', 'paitan': 'paitan-panoypoy', 'tarec': 'tarece', 'mabalabalino': 'mabalbalino' };
    var normalize = function (value) { return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\(.*?\)/g, ' ').replace(/\bblvd\.?/g, 'boulevard').replace(/\s+/g, ' ').trim(); };
    var shapeIndex = {};
    var shapeFor = function (area) { var key = normalize(area.label); return shapeIndex && (shapeIndex[key] || shapeIndex[aliases[key]] || shapeIndex[key.replace(/\s+(st|street|ave|avenue)$/, '')]); };
    try {
        var boundaryCollection = boundariesSource ? JSON.parse(boundariesSource.textContent) : null;
        (boundaryCollection && Array.isArray(boundaryCollection.features) ? boundaryCollection.features : []).forEach(function (feature) {
            if (feature.properties && feature.properties.name && feature.geometry) {
                shapeIndex[normalize(feature.properties.name)] = feature.geometry;
            }
        });
    } catch (error) {
        shapeIndex = {};
    }
    var popup = function (area, count, statusLayer) {
        var content = document.createElement('div');
        var title = document.createElement('strong'); title.textContent = area.label;
        var details = document.createElement('p'); details.textContent = (area.rhu_assignment || 'RHU not assigned') + ' — ' + (statusLayer ? area.status_label + '. ' + count + ' open pregnancy record(s).' : select.selectedOptions[0].textContent + ': ' + count);
        content.append(title, details);
        if (statusLayer) {
            var risks = area.risk_counts || {};
            var breakdown = document.createElement('p');
            breakdown.textContent = 'Low: ' + (risks.Low || 0) + '; Medium: ' + (risks.Medium || 0) + '; High: ' + (risks.High || 0) + '; Critical: ' + (risks.Critical || 0) + '; Unassessed: ' + (risks.Unassessed || 0) + '; Emergency flags: ' + (area.emergencies || 0) + '.';
            content.append(breakdown);
        }
        var location = document.createElement('p'); location.textContent = (area.location_basis || 'Approximate area location') + '. Not a patient address.'; content.append(location);
        if (area.location_source) {
            var sourceLink = document.createElement('a'); sourceLink.href = area.location_source; sourceLink.textContent = 'Location source: PhilAtlas'; sourceLink.target = '_blank'; sourceLink.rel = 'noopener noreferrer'; content.append(sourceLink);
        }
        return content;
    };
    var addPin = function (latLng, count, token) {
        L.marker(latLng, { interactive: false, icon: L.divIcon({ className: 'cho-risk-pin cho-pin-' + token, html: String(count), iconSize: [30, 30], iconAnchor: [15, 15] }) }).addTo(layers);
    };
    var render = function () {
        layers.clearLayers();
        var metric = select.value;
        var statusLayer = metric === 'risk_status';
        var metricToken = { high_risk: 'danger', deaths: 'danger', complications: 'warning', registrations: 'purple' }[metric];
        var features = [];
        var total = 0;
        areas.forEach(function (area) {
            var count = Number(area[statusLayer ? 'open' : metric]) || 0;
            total += count;
            var token = statusLayer ? area.status_color : (count ? metricToken : 'text-muted');
            var geometry = shapeFor(area);
            if (geometry) { features.push({ type: 'Feature', properties: { area: area, count: count, token: token }, geometry: geometry }); return; }
            var circle = L.circle([Number(area.lat), Number(area.lng)], { radius: 220 + Math.min(24, Math.sqrt(count) * 4) * 55, fillColor: color(token), fillOpacity: .48, color: '#ffffff', weight: 2 });
            circle.bindPopup(popup(area, count, statusLayer)).bindTooltip(area.label + ' · ' + (area.rhu_assignment || 'RHU not assigned'), { direction: 'top', sticky: true });
            circle.on('mouseover', function () { circle.setStyle({ weight: 3, fillOpacity: .62 }); }).on('mouseout', function () { circle.setStyle({ weight: 2, fillOpacity: .48 }); });
            circle.addTo(layers); addPin([Number(area.lat), Number(area.lng)], count, token);
        });
        if (features.length) {
            var boundaries = L.geoJSON({ type: 'FeatureCollection', features: features }, {
                style: function (feature) { return { fillColor: color(feature.properties.token), fillOpacity: .42, color: '#ffffff', weight: 1.5 }; },
                onEachFeature: function (feature, layer) {
                    var p = feature.properties;
                    layer.bindPopup(popup(p.area, p.count, statusLayer)).bindTooltip(p.area.label + ' · ' + p.count + (statusLayer ? ' open' : ' recorded'), { direction: 'top', sticky: true });
                    layer.on('mouseover', function () { layer.setStyle({ weight: 3, fillOpacity: .58 }); }).on('mouseout', function () { boundaries.resetStyle(layer); });
                    addPin(layer.getBounds().getCenter(), p.count, p.token);
                }
            });
            layers.addLayer(boundaries);
        }
        if (statusEl) statusEl.textContent = areas.length
            ? total + (statusLayer ? ' open pregnancy records' : ' recorded ' + select.selectedOptions[0].textContent.toLowerCase()) + ' across ' + areas.length + ' mapped area(s). Each colored shape and numbered dot is an area indicator.'
            : 'No mapped areas to display.';
    };
    var fit = function () { if (layers.getLayers().length) map.fitBounds(layers.getBounds(), { padding: [28, 28], maxZoom: 13 }); };
    render(); fit();
    select.addEventListener('change', render);
});
</script>
@endpush

@endsection
