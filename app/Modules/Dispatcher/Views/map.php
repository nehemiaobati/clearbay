<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var string $mapbox_token
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid p-0 d-flex flex-column flex-lg-row gap-3 mt-5 pt-4 dispatcher-layout" style="overflow-x: hidden; overflow-y: auto;">
  <!-- Left Side: Map Area -->
  <div class="map-container flex-grow-1 position-relative" style="min-height: 400px; height: calc(100vh - 80px);">
    <noscript>
      <div class="alert alert-warning m-3" role="alert">
        Dispatcher map requires JavaScript. Please enable it to view live fleet positions.
      </div>
    </noscript>
    <div id="map" class="w-100 h-100"></div>

    <!-- Floating Map Control Search Bar -->
    <div class="position-absolute top-0 end-0 m-3 dispatcher-search-overlay" style="z-index: 10;">
      <div class="input-group">
        <input type="text" id="unitSearchInput" class="form-control bg-dark border-secondary border-opacity-25 text-cream" placeholder="Search Unit ID (e.g. AAR-04)" aria-label="Search ambulance unit by ID">
        <button class="btn btn-outline-secondary" type="button" id="searchBtn" style="min-height: 48px;">Find</button>
      </div>
    </div>
  </div>

  <!-- Right Side: Sidebar Panels -->
  <aside class="p-4 d-flex flex-column gap-4 border-start border-secondary border-opacity-10 dispatcher-sidebar" style="width: 380px; flex-shrink: 0;">
    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-10">
      <span class="mono-label text-muted">EMS Dispatcher Console</span>
      <div>
        <?= csrf_field() ?>
      </div>
    </div>

    <!-- Panel SC-14: Active Alerts Panel -->
    <section class="card blueprint-card p-3" aria-labelledby="alertsHeading">
      <h3 id="alertsHeading" class="mono-label text-danger mb-3 d-flex align-items-center gap-2">
        <span class="spinner-grow spinner-grow-sm text-danger blink-dot" role="status" aria-hidden="true"></span>
        Active Off-Load Alerts (>30m)
      </h3>
      <div id="alertsPanelList" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
        <span class="text-muted small text-center py-3">No active timeout alerts.</span>
      </div>
    </section>

    <!-- Panel SC-13: Fleet Status Panel -->
    <section class="card blueprint-card p-3 flex-grow-1" style="min-height: 250px;" aria-labelledby="fleetHeading">
      <h3 id="fleetHeading" class="mono-label text-primary mb-3">Ambulance Fleet Status</h3>
      <div id="fleetPanelList" class="d-flex flex-column gap-2" style="overflow-y: auto;">
        <span class="text-muted small text-center py-3">Loading active fleet...</span>
      </div>
    </section>

    <!-- Panel SC-15: Hospital Capacity Panel -->
    <section class="card blueprint-card p-3" aria-labelledby="capacityHeading">
      <h3 id="capacityHeading" class="mono-label text-success mb-3">Facility Capacities</h3>
      <div id="capacityPanelList" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
        <span class="text-muted small text-center py-3">Loading capacities...</span>
      </div>
    </section>
  </aside>
</div>

<!-- Load Mapbox GL JS via CDN (deferred, after page render) -->
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js" defer></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Guard: only initialize map if Mapbox loaded
    if (typeof mapboxgl === 'undefined') return;

    // Theme Colors
    const style = getComputedStyle(document.documentElement);
    const colorSage = style.getPropertyValue('--sage').trim() || '#3D6B4F';
    const colorRed = style.getPropertyValue('--red').trim() || '#C23B22';
    const colorAmber = style.getPropertyValue('--amber').trim() || '#D4711A';

    // 1. Mapbox Setup
    mapboxgl.accessToken = '<?= esc($mapbox_token) ?>';

    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/dark-v11',
      center: [36.8219, -1.2921],
      zoom: 12
    });

    const activeMarkers = {};

    // 2. Telemetry Render Handler
    const updateDashboard = (telemetry) => {
      renderFleetPanel(telemetry);
      renderAlertsPanel(telemetry);
      renderCapacityPanel(telemetry);
      updateMapMarkers(telemetry);
    };

    // A. Render Fleet Panel List (SC-13) — semantic buttons with keyboard support
    const renderFleetPanel = (telemetry) => {
      const list = document.getElementById('fleetPanelList');
      if (telemetry.ambulances.length === 0) {
        list.innerHTML = `<span class="text-muted small text-center py-3">No active fleet vehicles.</span>`;
        return;
      }

      list.innerHTML = telemetry.ambulances.map(a => {
        let statusDot = 'bg-success';
        if (a.status === 'Transporting') statusDot = 'bg-info';
        else if (a.status === 'Queued') statusDot = 'bg-danger blink-dot';
        else if (a.status === 'On Scene') statusDot = 'bg-warning';
        else if (a.status === 'Off Duty') statusDot = 'bg-secondary';

        const waitText = telemetry.waits[a.id] ?
          `<span class="badge bg-danger ms-2">${telemetry.waits[a.id].wait_time_minutes}m waiting at ${telemetry.waits[a.id].hospital_name}</span>` :
          '';

        return `
          <button type="button"
                  class="p-2 border border-secondary border-opacity-10 rounded d-flex justify-content-between align-items-center hover-glow text-start bg-transparent text-reset focus-ring"
                  style="min-height: 48px;"
                  aria-label="Focus on ${a.unit_id} (${a.status})"
                  onclick="focusAmbulance(${a.current_lat}, ${a.current_lng}, '${a.unit_id}')">
            <div class="d-flex align-items-center gap-2">
              <span class="badge ${statusDot} rounded-circle p-1" aria-hidden="true" style="width: 8px; height: 8px;"></span>
              <strong class="mono-label text-cream">${a.unit_id}</strong>
              <small class="text-muted">(${a.provider})</small>
            </div>
            <div class="text-end">
              <span class="small text-muted">${a.status}</span>
              ${waitText}
            </div>
          </button>
        `;
      }).join('');
    };

    // B. Render Active Alerts Panel (SC-14)
    const renderAlertsPanel = (telemetry) => {
      const list = document.getElementById('alertsPanelList');
      if (telemetry.alerts.length === 0) {
        list.innerHTML = `<span class="text-muted small text-center py-3">No active off-load delay alerts.</span>`;
        return;
      }

      list.innerHTML = telemetry.alerts.map(al => {
        return `
          <div class="p-2 border border-danger border-opacity-20 rounded bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
            <div>
              <strong class="text-danger mono-label d-block">${al.ambulance_unit}</strong>
              <small class="text-muted d-block">Detained at ${al.hospital_name}</small>
            </div>
            <button class="btn btn-sm btn-outline-danger ack-alert-btn" data-id="${al.id}" style="min-height: 36px;">Ack</button>
          </div>
        `;
      }).join('');
    };

    // C. Render Hospital Capacities Panel (SC-15)
    const renderCapacityPanel = (telemetry) => {
      const list = document.getElementById('capacityPanelList');
      if (telemetry.hospitals.length === 0) {
        list.innerHTML = `<span class="text-muted small text-center py-3">No registered hospitals.</span>`;
        return;
      }

      list.innerHTML = telemetry.hospitals.map(h => {
        let statusColor = 'text-success';
        if (h.status === 'RED') statusColor = 'text-danger';
        else if (h.status === 'AMBER') statusColor = 'text-warning';

        return `
          <div class="p-2 border border-secondary border-opacity-10 rounded d-flex justify-content-between align-items-center">
            <div>
              <span class="small text-cream fw-bold d-block">${h.name}</span>
              <small class="text-muted mono-label">${h.code}</small>
            </div>
            <div class="text-end">
              <span class="small fw-bold ${statusColor} d-block">${h.status}</span>
              <small class="text-muted mono-label">${h.bays_available} bays</small>
            </div>
          </div>
        `;
      }).join('');
    };

    // D. Update Mapbox Coordinates Telemetry Markers
    const updateMapMarkers = (telemetry) => {
      telemetry.ambulances.forEach(a => {
        if (!a.current_lat || !a.current_lng) return;

        let color = colorSage;
        if (a.status === 'Transporting') color = '#0DCAF0';
        else if (a.status === 'Queued') color = colorRed;
        else if (a.status === 'On Scene') color = colorAmber;
        else if (a.status === 'Off Duty') color = '#6C757D';

        if (activeMarkers[a.unit_id]) {
          activeMarkers[a.unit_id].setLngLat([a.current_lng, a.current_lat]);
          activeMarkers[a.unit_id].getElement().style.backgroundColor = color;
        } else {
          const markerEl = document.createElement('div');
          markerEl.className = 'ambulance-map-pin';
          markerEl.style.backgroundColor = color;
          markerEl.style.width = '16px';
          markerEl.style.height = '16px';
          markerEl.style.borderRadius = '50%';
          markerEl.style.border = '2px solid #EDE9E0';
          markerEl.style.cursor = 'pointer';

          if (a.status === 'Queued') {
            markerEl.classList.add('blink-dot');
          }

          const marker = new mapboxgl.Marker(markerEl)
            .setLngLat([a.current_lng, a.current_lat])
            .setPopup(new mapboxgl.Popup({
              offset: 25
            }).setHTML(`<h6>${a.unit_id}</h6><p>${a.provider}<br>Status: ${a.status}</p>`))
            .addTo(map);

          activeMarkers[a.unit_id] = marker;
        }
      });

      telemetry.hospitals.forEach(h => {
        let color = colorSage;
        if (h.status === 'RED') color = colorRed;
        else if (h.status === 'AMBER') color = colorAmber;

        const hospEl = document.createElement('div');
        hospEl.style.backgroundColor = color;
        hospEl.style.width = '16px';
        hospEl.style.height = '16px';
        hospEl.style.borderRadius = '3px';
        hospEl.style.border = '2px solid #EDE9E0';
        hospEl.style.cursor = 'pointer';

        new mapboxgl.Marker(hospEl)
          .setLngLat([h.lng, h.lat])
          .setPopup(new mapboxgl.Popup({
            offset: 25
          }).setHTML(`<h6>${h.name}</h6><p>Bays: ${h.bays_available}<br>Status: ${h.status}</p>`))
          .addTo(map);
      });
    };

    window.focusAmbulance = (lat, lng, unitId) => {
      if (lat && lng) {
        map.flyTo({
          center: [lng, lat],
          zoom: 14
        });
        if (activeMarkers[unitId]) {
          activeMarkers[unitId].togglePopup();
        }
      }
    };

    // 3. Telemetry Stream EventSource
    const eventSource = new EventSource('<?= url_to('dispatcher.sse') ?>');

    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);
      if (data.status === 'update') {
        updateDashboard(data.result);
      }
    };

    eventSource.onerror = () => {
      console.warn('SSE disconnected. Reconnecting dynamically...');
    };

    // 4. Alert Acknowledgment Click Actions
    document.getElementById('alertsPanelList').addEventListener('click', async (e) => {
      const btn = e.target.closest('.ack-alert-btn');
      if (!btn) return;

      const alertId = btn.dataset.id;
      btn.disabled = true;

      try {
        const formData = new FormData();
        formData.append('csrf_test_name', document.querySelector('input[name="csrf_test_name"]')?.value ?? '');

        const ackBaseUrl = '<?= url_to('dispatcher.alert.acknowledge', 999999) ?>';
        const response = await fetch(ackBaseUrl.replace('999999', alertId), {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });
        const data = await response.json();
        if (data.status === 'success') {
          updateDashboard(data.result);
        } else {
          btn.disabled = false;
        }
      } catch (err) {
        btn.disabled = false;
        console.error('Acknowledgment failed:', err);
      }
    });

    // 5. Unit ID Search Action
    const unitSearchInput = document.getElementById('unitSearchInput');
    const searchBtn = document.getElementById('searchBtn');

    const triggerSearch = async () => {
      const query = unitSearchInput.value.toUpperCase().trim();
      if (!query) return;

      try {
        const response = await fetch('<?= url_to('dispatcher.fleet') ?>');
        const data = await response.json();
        if (data.status === 'success') {
          const amb = data.result.ambulances.find(a => a.unit_id === query);
          if (amb) {
            focusAmbulance(amb.current_lat, amb.current_lng, amb.unit_id);
          } else {
            alert(`Unit ID ${query} not found in active fleet.`);
          }
        }
      } catch (err) {
        console.error('Search query failed:', err);
      }
    };

    searchBtn.addEventListener('click', triggerSearch);
    unitSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') triggerSearch();
    });
  });
</script>

<?= $this->endSection() ?>