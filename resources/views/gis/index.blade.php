@extends($layout)

@section('title', 'Maternal Risk Heat Map - ReproCare')

@push('styles')
<style>
    #riskMap { height:560px; border-radius:18px; border:none; z-index:1; max-width:100%; }
    @media (max-width: 768px) { #riskMap { height:420px; } }
    .gis-legend { background:var(--color-surface); background-color:var(--color-surface); border:1px solid var(--color-border); border-radius:999px; padding:.55rem 1rem; font-size:.76rem; font-weight:600; color:var(--color-text); box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent); display:inline-flex; align-items:center; gap:.35rem; }
    .gis-legend .dot { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:.25rem; vertical-align:baseline; box-shadow:inset 0 0 0 2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 65%, transparent); }
    .gis-summary-badge { background:var(--color-surface-strong); color:var(--color-on-solid); border-radius:999px; padding:.55rem 1rem; font-size:.76rem; font-weight:700; white-space:nowrap; }
    .gis-map-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }

    /* ── Improved hero ── */
    .gis-hero-eyebrow { display:inline-flex; align-items:center; gap:.5rem; font-size:.68rem; font-weight:800; letter-spacing:1.4px; text-transform:uppercase; background:var(--color-danger-soft); color:var(--color-danger-text); border:1px solid var(--color-danger-soft); border-radius:999px; padding:.32rem .8rem; margin-bottom:.7rem; }
    .gis-hero-eyebrow .pulse { width:8px; height:8px; border-radius:50%; background:var(--color-danger); box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 50%, transparent); animation:gis-pulse 1.8s infinite; }
    @keyframes gis-pulse { 0% { box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent); } 70% { box-shadow:0 0 0 7px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 0%, transparent); } 100% { box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 0%, transparent); } }
    .gis-hero-title-row { display:flex; align-items:center; gap:.9rem; }
    .gis-hero-icon { width:48px; height:48px; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; color:var(--color-on-solid); background:linear-gradient(135deg, var(--color-danger), var(--color-warning)); box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); flex-shrink:0; }
    .gis-hero-meta { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.8rem; }
    .gis-hero-chip { display:inline-flex; align-items:center; gap:.4rem; font-size:.74rem; font-weight:700; color:var(--color-text-muted); background:var(--color-bg); border:1px solid var(--color-border); border-radius:999px; padding:.3rem .75rem; }
    .gis-hero-chip i { font-size:.8rem; }
    /* Segmented layer switcher — readable on both white & black heroes */
    .gis-layer-wrap { display:flex; flex-direction:column; gap:.45rem; align-items:flex-end; }
    .gis-layer-label { font-size:.66rem; font-weight:800; letter-spacing:1.4px; text-transform:uppercase; opacity:.55; }
    .gis-layer-group { display:inline-flex; gap:4px; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:1px solid color-mix(in srgb, var(--color-text) 7%, transparent) !important; border-radius:14px !important; padding:4px !important; box-shadow:inset 0 1px 2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); }
    .gis-layer-btn { border:none !important; border-radius:10px !important; font-weight:700 !important; font-size:.78rem !important; background:transparent !important; background-color:transparent !important; color:var(--color-text-muted) !important; padding:0.55rem .95rem !important; transition:all .18s ease !important; display:inline-flex !important; align-items:center !important; gap:.45rem !important; box-shadow:none !important; }
    .gis-layer-btn i { font-size:.85rem; }
    .gis-layer-btn:hover { background:color-mix(in srgb, var(--color-surface) 80%, transparent) !important; background-color:color-mix(in srgb, var(--color-surface) 80%, transparent) !important; color:var(--color-text) !important; }
    .gis-layer-btn.active { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border-color:transparent !important; color:var(--color-text) !important; box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 14%, transparent) !important; }
    .gis-layer-hint { font-size:.7rem; opacity:.5; }
    @media (max-width: 768px) {
        .gis-layer-wrap { align-items:stretch; width:100%; }
        .gis-layer-group { display:flex; width:100%; }
        .gis-layer-btn { flex:1; justify-content:center; }
        .gis-hero-title-row { align-items:flex-start; }
        .gis-hero-icon { width:42px; height:42px; font-size:1.15rem; }
    }
</style>
@endpush

@section($section)
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="min-width:240px; flex:1 1 320px;">
            <span class="gis-hero-eyebrow"><span class="pulse"></span> San Carlos City &middot; Live clustering</span>
            <div class="gis-hero-title-row">
                <span class="gis-hero-icon"><i class="bi bi-geo-alt-fill"></i></span>
                <div>
                    <div class="page-hero-title mb-0">Maternal Risk Heat Map</div>
                    <p class="page-hero-subtitle mb-0">Risk clustering across San Carlos City puroks — target resources where they cluster.</p>
                </div>
            </div>
            <div class="gis-hero-meta">
                <span class="gis-hero-chip"><i class="bi bi-people-fill" style="color:var(--color-primary-text);"></i> Purok-level clusters</span>
                <span class="gis-hero-chip"><i class="bi bi-aspect-ratio-fill" style="color:var(--color-success-text);"></i> Circle size = case count</span>
            </div>
        </div>
        <div class="gis-layer-wrap">
            <span class="gis-layer-label">Map indicator</span>
            <div class="d-flex gap-2 flex-wrap gis-layer-group" role="group" aria-label="Map layer">
                <button type="button" class="btn btn-sm gis-layer-btn active" data-layer="high_bp"><i class="bi bi-heart-pulse-fill" style="color:var(--color-danger-text);"></i>High BP ≥140/90</button>
                <button type="button" class="btn btn-sm gis-layer-btn" data-layer="anemia"><i class="bi bi-droplet-fill" style="color:var(--color-warning-text);"></i>Anemia Hgb &lt;10</button>
                <button type="button" class="btn btn-sm gis-layer-btn" data-layer="missed"><i class="bi bi-calendar-x-fill" style="color:var(--color-text-muted);"></i>Missed Checkups</button>
            </div>
            <small class="gis-layer-hint">Click an indicator to re-cluster the map</small>
        </div>
    </div>
</div>

<div class="card gis-map-card">
    <div class="card-body p-3" style="border:none;">
        <div id="riskMap"></div>
        <div class="d-flex flex-wrap gap-3 align-items-center mt-3">
            <div class="gis-legend">
                <span class="dot" style="background:var(--color-success);"></span>Low
                <span class="dot ms-2" style="background:var(--color-warning);"></span>Moderate
                <span class="dot ms-2" style="background:var(--color-danger);"></span>High cluster
            </div>
            <small class="text-muted">Circle size = case count · markers without stored coordinates use approximate purok positions.</small>
            <small class="ms-auto gis-summary-badge" id="gisSummary"></small>
        </div>
    </div>
</div>

@push('scripts')
@if(config('services.google_maps.api_key'))
<script src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google_maps.api_key')) }}&amp;v=weekly"></script>
@endif
<script>
(function () {
    var mapElement = document.getElementById('riskMap');
    if (!window.google || !google.maps) {
        mapElement.textContent = 'Google Maps could not load. Add GOOGLE_MAPS_API_KEY to the application configuration and refresh.';
        document.getElementById('gisSummary').textContent = 'Map unavailable.';
        return;
    }
    var map = new google.maps.Map(mapElement, { center: { lat: 15.9281, lng: 120.3478 }, zoom: 13, mapTypeControl: false, streetViewControl: false });
    map.setView = function (coordinates, zoom) { this.setCenter({ lat: Number(coordinates[0]), lng: Number(coordinates[1]) }); if (zoom) this.setZoom(zoom); };
    var infoWindow = new google.maps.InfoWindow();
    var L = {
        layerGroup: function () {
            var layers = [];
            return {
                addTo: function () { return this; },
                clearLayers: function () { layers.forEach(function (layer) { layer.setMap(null); }); layers = []; },
                addLayer: function (layer) { layers.push(layer); }
            };
        },
        circleMarker: function (coordinates, options) {
            var marker = new google.maps.Circle({
                map: map, center: { lat: Number(coordinates[0]), lng: Number(coordinates[1]) },
                radius: 250 + Math.max(0, Number(options.radius) - 8) * 30,
                fillColor: options.fillColor, fillOpacity: options.fillOpacity,
                strokeColor: options.color, strokeWeight: options.weight
            });
            marker.bindPopup = function (content) {
                marker.addListener('click', function () { infoWindow.setContent(content); infoWindow.setPosition(marker.getCenter()); infoWindow.open(map); });
                return marker;
            };
            return marker;
        }
    };

    var layerGroup = L.layerGroup().addTo(map);
    var currentLayer = 'high_bp';
    var labels = { high_bp: 'High BP cases', anemia: 'Anemia cases', missed: 'Missed checkups' };

    function colorFor(count) {
        if (count >= 5) return ReproCareCharts.color('danger');
        if (count >= 2) return ReproCareCharts.color('warning');
        return ReproCareCharts.color('success');
    }

    function render(puroks) {
        layerGroup.clearLayers();
        var total = 0;
        puroks.forEach(function (p) {
            var count = p[currentLayer] || 0;
            total += count;
            var marker = L.circleMarker([p.lat, p.lng], {
                radius: 8 + Math.min(count, 10) * 2.5,
                fillColor: colorFor(count),
                color: ReproCareCharts.color('surface'),
                weight: 2,
                fillOpacity: 0.75
            });
            marker.bindPopup(
                '<strong>' + p.name + '</strong><br>' +
                (p.barangay || '') + '<br>' +
                labels[currentLayer] + ': <strong>' + count + '</strong><br>' +
                '<small>Patients: ' + p.patients + ' · Active pregnancies: ' + p.active_pregnancies + '<br>' +
                'High BP: ' + p.high_bp + ' · Anemia: ' + p.anemia + ' · Missed: ' + p.missed +
                (p.approximate ? '<br><em>Approximate position</em>' : '') + '</small>'
            );
            layerGroup.addLayer(marker);
        });
        document.getElementById('gisSummary').textContent =
            'Total ' + labels[currentLayer].toLowerCase() + ': ' + total;
    }

    var cache = null;
    fetch(@json($dataUrl))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            cache = data.puroks || [];
            if (data.center) map.setView(data.center, 13);
            render(cache);
        })
        .catch(function () {
            document.getElementById('gisSummary').textContent = 'Could not load map data.';
        });

    document.querySelectorAll('.gis-layer-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.gis-layer-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentLayer = btn.dataset.layer;
            if (cache) render(cache);
        });
    });
})();
</script>
@endpush
@endsection
