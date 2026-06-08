<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var \App\Modules\Ambulance\Entities\Ambulance $ambulance
 * @var array $hospitals
 * @var string $mapbox_token
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid p-0 d-flex flex-column paramedic-layout">
  <!-- Section 1: Map (upper two-thirds) -->
  <div id="map" class="flex-grow-1 w-100 paramedic-map">
    <noscript>
      <div class="alert alert-warning m-3" role="alert">
        Map view requires JavaScript. Please enable it to see hospital locations and your current position.
      </div>
    </noscript>

    <!-- Floating Quick Stats -->
    <div class="position-absolute top-0 start-0 m-3 p-3 card blueprint-card shadow paramedic-quick-stats">
      <span class="mono-label text-muted d-block mb-1">Ambulance Unit</span>
      <span class="fw-bold fs-5 text-primary"><?= esc($ambulance->unit_id) ?></span>
      <span class="mono-label text-muted d-block mt-2 mb-1">Current Status</span>
      <span class="badge bg-secondary"><?= esc($ambulance->status) ?></span>
    </div>

    <!-- GPS Status Indicator -->
    <div id="gps-status" class="position-absolute top-0 end-0 m-3 p-2 card blueprint-card shadow-sm"
      style="font-size: 0.75rem; display: none;">
      <span id="gps-text" class="text-muted">Acquiring GPS...</span>
    </div>
  </div>

  <!-- Section 2: Hospital List (lower third) -->
  <div class="border-top border-secondary border-opacity-20 p-3 paramedic-hospital-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mono-label text-muted m-0">Hospitals Sorted by Distance</h3>
    </div>

    <div id="hospital-list-container" class="row g-2">
      <?php foreach ($hospitals as $item) : ?>
        <?php
        $h = $item['hospital'];
        $status_class = 'bg-success';
        if ($h->status === 'RED') $status_class = 'bg-danger';
        elseif ($h->status === 'AMBER') $status_class = 'bg-warning text-dark';
        ?>
        <div class="col-12 col-md-6 col-lg-4 hospital-card-wrapper"
          data-hospital-id="<?= (int) $h->id ?>"
          data-lat="<?= (float) $h->lat ?>"
          data-lng="<?= (float) $h->lng ?>"
          data-name="<?= esc($h->name) ?>"
          data-status="<?= esc($h->status) ?>"
          data-bays="<?= (int) $h->bays_available ?>">
          <a href="<?= url_to('ambulance.hospital.detail', $h->id) ?>"
            class="card blueprint-card p-3 d-flex flex-row justify-content-between align-items-center hover-glow text-decoration-none text-reset focus-ring"
            style="min-height: 48px; gap: 0.75rem;">
            <div class="d-flex align-items-center gap-3">
              <span class="badge <?= $status_class ?> rounded-circle p-1" aria-hidden="true" style="width: 12px; height: 12px;"></span>
              <div>
                <h4 class="h6 m-0 fw-bold text-cream"><?= esc($h->name) ?></h4>
                <small class="text-muted hospital-meta"
                  data-distance="<?= (float) $item['distance'] ?>"
                  data-eta="<?= (int) $item['eta'] ?>">
                  <span class="hospital-distance"><?= esc($item['distance']) ?></span> km away
                  &nbsp;·&nbsp;
                  ETA: <span class="hospital-eta"><?= (int) $item['eta'] ?></span> min
                </small>
              </div>
            </div>
            <span class="badge bg-secondary py-2 px-3" aria-label="<?= (int) $h->bays_available ?> bays available">
              <?= (int) $h->bays_available ?>
            </span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Theme Colors
    const style = getComputedStyle(document.documentElement);
    const colorSage = style.getPropertyValue('--sage').trim() || '#3D6B4F';
    const colorSageL = style.getPropertyValue('--sage-l').trim() || '#4E8A63';
    const colorRed = style.getPropertyValue('--red').trim() || '#C23B22';
    const colorAmber = style.getPropertyValue('--amber').trim() || '#D4711A';

    const mapboxToken = '<?= esc($mapbox_token) ?>';

    // --- Gather hospital data from data attributes ---
    const hospitalCards = document.querySelectorAll('.hospital-card-wrapper');
    const hospitals = [];
    hospitalCards.forEach(card => {
      hospitals.push({
        id: parseInt(card.dataset.hospitalId),
        name: card.dataset.name,
        status: card.dataset.status,
        lat: parseFloat(card.dataset.lat),
        lng: parseFloat(card.dataset.lng),
        bays: parseInt(card.dataset.bays),
      });
    });

    // --- Initial fallback coordinates ---
    let myLat = <?= $ambulance->current_lat ?? \App\Modules\Ambulance\Libraries\AmbulanceService::NAIROBI_LAT ?>;
    let myLng = <?= $ambulance->current_lng ?? \App\Modules\Ambulance\Libraries\AmbulanceService::NAIROBI_LNG ?>;
    let gpsResolved = false;

    // --- GPS Status Indicator ---
    const gpsStatus = document.getElementById('gps-status');
    const gpsText = document.getElementById('gps-text');

    // --- 1. EARLY GPS ACQUISITION ---
    // Request geolocation immediately on page load to get live coordinates.
    if ('geolocation' in navigator) {
      gpsStatus.style.display = 'block';
      gpsText.textContent = 'Acquiring GPS...';

      navigator.geolocation.getCurrentPosition(
        // Success: live coordinates obtained
        (position) => {
          myLat = position.coords.latitude;
          myLng = position.coords.longitude;
          gpsResolved = true;

          gpsText.textContent = 'GPS acquired';
          gpsStatus.style.opacity = '0.6';
          setTimeout(() => {
            gpsStatus.style.display = 'none';
          }, 3000);

          // 2. MAPBOX MATRIX API CALL
          fetchMapboxMatrix(myLat, myLng, hospitals);
        },
        // Error: permission denied or unavailable — fall back to server Haversine
        (err) => {
          console.warn('GPS unavailable (' + err.code + '): ' + err.message);
          gpsText.textContent = 'Using estimated location';
          gpsStatus.style.opacity = '0.5';
          setTimeout(() => {
            gpsStatus.style.display = 'none';
          }, 3000);
        }, {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 60000,
        }
      );
    }

    // --- 2. MAPBOX MATRIX API FETCH ---
    async function fetchMapboxMatrix(sourceLat, sourceLng, destinations) {
      if (!mapboxToken) {
        console.warn('Mapbox token missing — falling back to Haversine.');
        return;
      }

      // Build coordinate string: source (index 0) + all destination hospitals
      // Mapbox Matrix API format: {lng},{lat};{lng},{lat};...
      let coords = `${sourceLng},${sourceLat}`;
      destinations.forEach(h => {
        coords += `;${h.lng},${h.lat}`;
      });

      const url = `https://api.mapbox.com/directions-matrix/v1/mapbox/driving/${coords}` +
        `?sources=0` +
        `&annotations=distance,duration` +
        `&access_token=${mapboxToken}`;

      try {
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(`Mapbox API responded with ${response.status}`);
        }
        const data = await response.json();

        if (!data || data.code !== 'Ok') {
          throw new Error('Invalid Mapbox Matrix response');
        }

        // Map results back to hospital cards by index
        // The Matrix API returns a matrix in row-major order:
        //   data.distances[i][j] = distance from source i to destination j (meters)
        //   data.durations[i][j] = duration from source i to destination j (seconds)
        // With one source (index 0), we read data.distances[0][j] and data.durations[0][j]
        const container = document.getElementById('hospital-list-container');
        const cards = container.querySelectorAll('.hospital-card-wrapper');
        const distanceRow = data.distances ? data.distances[0] : null;
        const durationRow = data.durations ? data.durations[0] : null;

        // Build an array of {element, drivingDistance, drivingDuration}
        const sorted = [];
        cards.forEach((card, index) => {
          const rawDistance = distanceRow ? distanceRow[index] : null;
          const rawDuration = durationRow ? durationRow[index] : null;

          if (rawDistance !== null && rawDistance !== undefined) {
            const distanceKm = (rawDistance / 1000).toFixed(1); // meters → km
            const durationMin = Math.round(rawDuration / 60); // seconds → min

            // Update the card's distance and ETA text
            const meta = card.querySelector('.hospital-meta');
            if (meta) {
              const distSpan = meta.querySelector('.hospital-distance');
              const etaSpan = meta.querySelector('.hospital-eta');
              if (distSpan) distSpan.textContent = distanceKm;
              if (etaSpan) etaSpan.textContent = durationMin;
            }

            sorted.push({
              element: card,
              drivingDistance: rawDistance, // raw meters for sorting
              drivingDuration: rawDuration,
            });
          } else {
            sorted.push({
              element: card,
              drivingDistance: Infinity,
              drivingDuration: 0,
            });
          }
        });

        // Sort by driving distance (ascending)
        sorted.sort((a, b) => a.drivingDistance - b.drivingDistance);

        // Re-append cards in sorted order to the DOM
        sorted.forEach(item => {
          container.appendChild(item.element);
        });

      } catch (err) {
        console.warn('Mapbox Matrix API failed:', err.message);
        // Fallback: keep server-side Haversine sort — no DOM changes needed
      }
    }

    // --- 3. MAPBOX MAP INITIALIZATION (only if Mapbox GL is available) ---
    if (typeof mapboxgl !== 'undefined') {
      mapboxgl.accessToken = mapboxToken;

      const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/dark-v11',
        center: [myLng, myLat],
        zoom: 12
      });

      // Paramedic Location Marker
      const el = document.createElement('div');
      el.className = 'marker';
      el.style.backgroundColor = colorSageL;
      el.style.width = '20px';
      el.style.height = '20px';
      el.style.borderRadius = '50%';
      el.style.border = '3px solid #EDE9E0';
      el.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';

      new mapboxgl.Marker(el)
        .setLngLat([myLng, myLat])
        .setPopup(new mapboxgl.Popup({
          offset: 25
        }).setHTML('<h6>Active Ambulance: ' + "<?= esc($ambulance->unit_id) ?>" + '</h6>'))
        .addTo(map);

      // Hospital Markers
      hospitals.forEach(h => {
        let color = colorSage;
        if (h.status === 'RED') color = colorRed;
        else if (h.status === 'AMBER') color = colorAmber;

        const hMarker = document.createElement('div');
        hMarker.style.backgroundColor = color;
        hMarker.style.width = '14px';
        hMarker.style.height = '14px';
        hMarker.style.borderRadius = '4px';
        hMarker.style.cursor = 'pointer';

        hMarker.addEventListener('click', () => {
          window.location.href = '<?= url_to('ambulance.hospital.detail', '__ID__') ?>'.replace('__ID__', h.id);
        });

        new mapboxgl.Marker(hMarker)
          .setLngLat([h.lng, h.lat])
          .setPopup(new mapboxgl.Popup({
            offset: 25
          }).setHTML('<h6>' + h.name + '</h6><p>Status: ' + h.status + '</p>'))
          .addTo(map);
      });

      // GPS callback to update map marker when coordinates resolve
      // (This runs after Mapbox Matrix fetch already triggered the GPS call)
    }
  });
</script>

<!-- Load Mapbox GL JS via CDN (deferred, after page render) -->
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js" defer></script>

<?= $this->endSection() ?>