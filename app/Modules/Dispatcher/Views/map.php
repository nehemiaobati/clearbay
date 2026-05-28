<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<!-- Include Mapbox GL JS via CDN -->
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>

<style>
  /* Local custom overrides for full screen layout */
  .dispatcher-layout {
    height: calc(100vh - 80px);
    margin-top: 80px;
    display: flex;
    overflow: hidden;
  }
  
  .map-container {
    flex-grow: 1;
    height: 100%;
    position: relative;
  }

  .sidebar-container {
    width: 380px;
    height: 100%;
    border-left: 1px solid rgba(255, 255, 255, 0.08);
    background: var(--ink);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    padding: 1.5rem;
    gap: 1.5rem;
  }

  /* Blinking dot for queued alerts */
  .blink-dot {
    animation: blinker 1.2s cubic-bezier(.5, 0, 1, 1) infinite;
  }
  @keyframes blinker {
    50% { opacity: 0; }
  }
</style>

<div class="container-fluid p-0 dispatcher-layout">
  <!-- Left Side: Map Area -->
  <div class="map-container">
    <div id="map" class="w-100 h-100"></div>

    <!-- Floating Map Control Search Bar -->
    <div class="position-absolute top-0 end-0 m-3" style="z-index: 10; width: 280px;">
      <div class="input-group">
        <input type="text" id="unitSearchInput" class="form-control bg-dark border-secondary border-opacity-25 text-cream" placeholder="Search Unit ID (e.g. AAR-04)">
        <button class="btn btn-outline-secondary" type="button" id="searchBtn">Find</button>
      </div>
    </div>
  </div>

  <!-- Right Side: Sidebar Panels -->
  <div class="sidebar-container">
    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-10">
      <span class="mono-label text-muted">EMS Dispatcher Console</span>
      <a href="<?= url_to('auth.logout') ?>" class="btn btn-xs btn-outline-danger">Sign Out</a>
    </div>

    <!-- Panel SC-14: Active Alerts Panel -->
    <div class="card blueprint-card p-3">
      <h3 class="mono-label text-danger mb-3 d-flex align-items-center gap-2">
        <span class="spinner-grow spinner-grow-sm text-danger blink-dot" role="status"></span>
        Active Off-Load Alerts (&gt;30m)
      </h3>
      <div id="alertsPanelList" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
        <span class="text-muted small text-center py-3">No active timeout alerts.</span>
      </div>
    </div>

    <!-- Panel SC-13: Fleet Status Panel -->
    <div class="card blueprint-card p-3 flex-grow-1" style="min-height: 250px;">
      <h3 class="mono-label text-primary mb-3">Ambulance Fleet Status</h3>
      <div id="fleetPanelList" class="d-flex flex-column gap-2" style="overflow-y: auto;">
        <span class="text-muted small text-center py-3">Loading active fleet...</span>
      </div>
    </div>

    <!-- Panel SC-15: Hospital Capacity Panel -->
    <div class="card blueprint-card p-3">
      <h3 class="mono-label text-success mb-3">Facility Capacities</h3>
      <div id="capacityPanelList" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
        <span class="text-muted small text-center py-3">Loading capacities...</span>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Theme Colors
    const style = getComputedStyle(document.documentElement);
    const colorSage = style.getPropertyValue('--sage').trim() || '#3D6B4F';
    const colorRed = style.getPropertyValue('--red').trim() || '#C23B22';
    const colorAmber = style.getPropertyValue('--amber').trim() || '#D4711A';
    
    // 1. Mapbox Setup
    mapboxgl.accessToken = 'pk.eyJ1IjoibmVoZW1pYWgiLCJhIjoiY2x2YnlwdnJ5MGdtNDJpcG5iNWhzNHBxNiJ9.y7k4s5f8d9q1r2t3y4u5i6';
    
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/dark-v11', // Premium dark theme matching platform aesthetics
      center: [36.8219, -1.2921], // Nairobi County Center
      zoom: 12
    });

    const activeMarkers = {}; // Cache active vehicle markers to move them smoothly

    // 2. Telemetry Render Handler
    const updateDashboard = (telemetry) => {
      renderFleetPanel(telemetry);
      renderAlertsPanel(telemetry);
      renderCapacityPanel(telemetry);
      updateMapMarkers(telemetry);
    };

    // A. Render Fleet Panel List (SC-13)
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
          <div class="p-2 border border-secondary border-opacity-10 rounded d-flex justify-content-between align-items-center pointer-event hover-glow" 
               style="cursor: pointer;"
               onclick="focusAmbulance(${a.current_lat}, ${a.current_lng}, '${a.unit_id}')">
            <div class="d-flex align-items-center gap-2">
              <span class="badge ${statusDot} rounded-circle p-1" style="width: 8px; height: 8px; display: inline-block;"></span>
              <strong class="mono-label text-cream">${a.unit_id}</strong>
              <small class="text-muted">(${a.provider})</small>
            </div>
            <div class="text-end">
              <span class="small text-muted">${a.status}</span>
              ${waitText}
            </div>
          </div>
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
            <button class="btn btn-xs btn-outline-danger ack-alert-btn" data-id="${al.id}">Ack</button>
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
      // Create/Update ambulance pins
      telemetry.ambulances.forEach(a => {
        if (!a.current_lat || !a.current_lng) return;

        let color = colorSage; // green
        if (a.status === 'Transporting') color = '#0DCAF0'; // teal
        else if (a.status === 'Queued') color = colorRed; // red
        else if (a.status === 'On Scene') color = colorAmber; // orange
        else if (a.status === 'Off Duty') color = '#6C757D'; // grey

        if (activeMarkers[a.unit_id]) {
          // Marker already exists, update position dynamically
          activeMarkers[a.unit_id].setLngLat([a.current_lng, a.current_lat]);
          activeMarkers[a.unit_id].getElement().style.backgroundColor = color;
        } else {
          // Create marker element
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
            .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(`<h6>${a.unit_id}</h6><p>${a.provider}<br>Status: ${a.status}</p>`))
            .addTo(map);

          activeMarkers[a.unit_id] = marker;
        }
      });

      // Add Hospital Pins
      telemetry.hospitals.forEach(h => {
        let color = colorSage;
        if (h.status === 'RED') color = colorRed;
        else if (h.status === 'AMBER') color = colorAmber;

        const hospEl = document.createElement('div');
        hospEl.style.backgroundColor = color;
        hospEl.style.width = '16px';
        hospEl.style.height = '16px';
        hospEl.style.borderRadius = '3px'; // Square
        hospEl.style.border = '2px solid #EDE9E0';
        hospEl.style.cursor = 'pointer';

        new mapboxgl.Marker(hospEl)
          .setLngLat([h.lng, h.lat])
          .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(`<h6>${h.name}</h6><p>Bays: ${h.bays_available}<br>Status: ${h.status}</p>`))
          .addTo(map);
      });
    };

    // Helper functions
    window.focusAmbulance = (lat, lng, unitId) => {
      if (lat && lng) {
        map.flyTo({ center: [lng, lat], zoom: 14 });
        if (activeMarkers[unitId]) {
          activeMarkers[unitId].togglePopup();
        }
      }
    };

    // 3. Telemetry Stream EventSource (Server-Sent Events Listener)
    const eventSource = new EventSource('<?= url_to('dispatcher.sse') ?>');
    
    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);
      if (data.status === 'update') {
        updateDashboard(data.result);
      }
    };

    eventSource.onerror = (err) => {
      console.warn('SSE disconnected. Reconnecting dynamically...');
    };

    // 4. Alert Acknowledgment Click Actions (Event Delegation)
    document.getElementById('alertsPanelList').addEventListener('click', async (e) => {
      const btn = e.target.closest('.ack-alert-btn');
      if (!btn) return;

      const alertId = btn.dataset.id;
      btn.disabled = true;

      try {
        const response = await fetch('/dispatcher/alerts/' + alertId + '/acknowledge', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
          }
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

      // Poll current local telemetry
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
