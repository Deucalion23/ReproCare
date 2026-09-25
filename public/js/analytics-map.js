(() => {
    'use strict';
    document.querySelectorAll('.an-risk-toggle').forEach(button => {
        const rows = document.getElementById(button.getAttribute('aria-controls'));
        if (!rows) return;
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') === 'true';
            rows.hidden = expanded;
            button.setAttribute('aria-expanded', String(!expanded));
            button.querySelector('.an-risk-toggle-hint').textContent = expanded ? 'Expand' : 'Collapse';
        });
    });

    const element = document.getElementById('analytics-risk-map');
    if (!element) return;
    const status = document.getElementById('analytics-map-status');
    if (!window.L) {
        element.classList.add('an-map-unavailable');
        element.textContent = 'The risk map could not load. Check your internet connection and refresh.';
        if (status) status.textContent = 'Area counts remain available in the Current area indicators table.';
        return;
    }

    const data = JSON.parse(document.getElementById('analytics-map-data').textContent);
    const select = document.getElementById('analytics-map-layer');
    const map = L.map(element, { zoomControl: true }).setView([15.9281, 120.3478], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    const layers = L.layerGroup().addTo(map);
    const color = token => getComputedStyle(document.documentElement).getPropertyValue(`--color-${token}`).trim();

    // Exact barangay shapes (NAMRIA-derived). Areas without a matching shape keep the circle marker.
    // Recorded names use street abbreviations and old variants, so normalize + alias them.
    const SHAPE_ALIASES = {
        'burgos': 'burgos padlan',
        'burgos st': 'burgos padlan',
        'barangay burgos padlan, san carlos city, pangasinan': 'burgos padlan',
        'san pedro st': 'san pedro-taloy',
        'bugallon st': 'bugallon-posadas street',
        'caoayan kiling': 'caoayan-kiling',
        'pnr site': 'pnr station site',
        'nelintap': 'nilentap',
        'padilla st': 'padilla-gomez',
        'paitan': 'paitan-panoypoy',
        'tarec': 'tarece',
        'mabalabalino': 'mabalbalino'
    };
    const normKey = value => String(value || '')
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\(.*?\)/g, ' ')
        .replace(/\bblvd\.?/g, 'boulevard')
        .replace(/\s+/g, ' ').trim();
    let shapeIndex = null;
    const shapeFor = area => {
        if (!shapeIndex) return null;
        const full = normKey(area.label);
        return shapeIndex[full]
            || (SHAPE_ALIASES[full] ? shapeIndex[SHAPE_ALIASES[full]] : null)
            || shapeIndex[full.replace(/\s+(st|street|ave|avenue)$/, '')]
            || null;
    };

    const buildPopup = (area, count, showStatus) => {
        const popup = document.createElement('div');
        const heading = document.createElement('strong'); heading.textContent = area.label;
        const detail = document.createElement('p');
        detail.textContent = `${area.rhu_assignment || 'RHU not assigned'} — ${showStatus ? `${area.status_label}. ${count} open pregnancy record(s).` : `${select.selectedOptions[0].textContent}: ${count}`}`;
        popup.append(heading, detail);
        if (showStatus) {
            const risks = area.risk_counts || {};
            const breakdown = document.createElement('p');
            breakdown.textContent = `Low: ${risks.Low || 0}; Medium: ${risks.Medium || 0}; High: ${risks.High || 0}; Critical: ${risks.Critical || 0}; Unassessed: ${risks.Unassessed || 0}; Emergency flags: ${area.emergencies || 0}.`;
            popup.append(breakdown);
        }
        const location = document.createElement('p');
        location.textContent = `${area.location_basis || 'Approximate area location'}. Not a patient address.`;
        popup.append(location);
        if (area.location_source) {
            const source = document.createElement('a');
            source.href = area.location_source; source.textContent = 'Location source: PhilAtlas';
            source.target = '_blank'; source.rel = 'noopener noreferrer'; popup.append(source);
        }
        return popup;
    };

    const addPin = (latLng, area, count, pinColor) => {
        const indicator = L.marker(latLng, {
            interactive: false,
            icon: L.divIcon({ className: 'an-map-count-pin an-pin-' + pinColor, html: String(count), iconSize: [30, 30], iconAnchor: [15, 15] })
        });
        layers.addLayer(indicator);
    };

    let boundaryLayer = null;
    const render = () => {
        layers.clearLayers();
        boundaryLayer = null;
        const metric = select.value;
        const showStatus = metric === 'risk_status';
        const token = {high_risk: 'danger', deaths: 'danger', complications: 'warning', registrations: 'purple'}[metric];
        let total = 0;
        const shapeFeatures = [];
        data.areas.forEach(area => {
            const count = Number(area[showStatus ? 'open' : metric]) || 0;
            total += count;
            const areaToken = showStatus ? area.status_color : (count ? token : 'text-muted');
            const markerColor = color(areaToken);
            const geom = shapeFor(area);
            if (geom) {
                shapeFeatures.push({ type: 'Feature', properties: { area, count, areaToken }, geometry: geom });
                return;
            }
            const marker = L.circle([Number(area.lat), Number(area.lng)], {
                radius: 220 + Math.min(24, Math.sqrt(count) * 4) * 55,
                fillColor: markerColor, fillOpacity: .78,
                color: color('surface'), weight: 2
            });
            marker.bindPopup(buildPopup(area, count, showStatus)).bindTooltip(`${area.label} · ${area.rhu_assignment || 'RHU not assigned'}`, { direction: 'top' });
            layers.addLayer(marker);
            addPin([Number(area.lat), Number(area.lng)], area, count, areaToken);
        });
        if (shapeFeatures.length) {
            boundaryLayer = L.geoJSON({ type: 'FeatureCollection', features: shapeFeatures }, {
                style: feature => {
                    const fill = color(feature.properties.areaToken);
                    return { fillColor: fill, fillOpacity: .42, color: '#ffffff', weight: 1.5, opacity: 1 };
                },
                onEachFeature: (feature, layer) => {
                    const { area, count, areaToken } = feature.properties;
                    layer.bindPopup(buildPopup(area, count, showStatus)).bindTooltip(`${area.label} · ${count} ${showStatus ? 'open' : 'recorded'}`, { direction: 'top', sticky: true });
                    layer.on('mouseover', () => layer.setStyle({ weight: 3, fillOpacity: .55 }));
                    layer.on('mouseout', () => boundaryLayer.resetStyle(layer));
                    addPin(layer.getBounds().getCenter(), area, count, areaToken);
                }
            });
            layers.addLayer(boundaryLayer);
        }
        if (status) status.textContent = data.areas.length
            ? `${total} ${showStatus ? 'open pregnancy records' : 'recorded ' + select.selectedOptions[0].textContent.toLowerCase()} across ${data.areas.length} mapped area(s). Each colored shape and numbered dot is an area indicator; hover or click a shape for details.`
            : 'City base map shown. No matching areas have saved coordinates; no risk markers are displayed.';
    };
    const fitInitialView = () => {
        if (!data.areas.length) return;
        try {
            map.fitBounds(layers.getBounds(), { padding: [28, 28], maxZoom: 13 });
        } catch (e) {
            map.fitBounds(data.areas.map(area => [Number(area.lat), Number(area.lng)]), { padding: [28, 28], maxZoom: 13 });
        }
    };
    render();
    fitInitialView();
    if (element.dataset.boundariesUrl) {
        fetch(element.dataset.boundariesUrl, { headers: { 'Accept': 'application/json' } })
            .then(response => (response.ok ? response.json() : null))
            .then(geojson => {
                if (!geojson || !Array.isArray(geojson.features)) return;
                shapeIndex = {};
                geojson.features.forEach(feature => {
                    const name = feature && feature.properties ? feature.properties.name : null;
                    if (name && feature.geometry) shapeIndex[normKey(name)] = feature.geometry;
                });
                render();
                fitInitialView();
            })
            .catch(() => {});
    }
    select.addEventListener('change', render);
    document.addEventListener('rc:theme-changed', render);
    if (window.ResizeObserver) new ResizeObserver(() => map.invalidateSize({ pan: false })).observe(element);
    window.addEventListener('resize', () => map.invalidateSize({ pan: false }));
})();
