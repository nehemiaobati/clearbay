<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Ambulance\Entities\Ambulance $ambulance
 * @var array $hospitals
 * @var string $mapbox_token
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<!-- Include Mapbox GL JS via CDN -->
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>

<div class="container-fluid p-0 d-flex flex-column paramedic-layout">
  <!-- Section 1: Map (upper two-thirds) -->
  <div id="map" class="flex-grow-1 w-100 paramedic-map">
    <!-- Floating Quick Stats -->
    <div class="position-absolute top-0 start-0 m-3 p-3 card blueprint-card shadow paramedic-quick-stats">
      <span class="mono-label text-muted d-block mb-1">Ambulance Unit</span>
      <span class="fw-bold fs-5 text-primary"><?= esc($ambulance->unit_id) ?></span>
      <span class="mono-label text-muted d-block mt-2 mb-1">Current Status</span>
      <span class="badge bg-secondary"><?= esc($ambulance->status) ?></span>
    </div>
  </div>

  <!-- Section 2: Hospital List (lower third) -->
  <div class="border-top border-secondary border-opacity-20 p-3 paramedic-hospital-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mono-label text-muted m-0">Hospitals Sorted by Distance</h3>
    </div>

    <div class="row g-2">
      <?php foreach ($hospitals as $item) : ?>
        <?php
        $h = $item['hospital'];
        $status_class = 'bg-success';
        if ($h->status === 'RED') $status_class = 'bg-danger';
        elseif ($h->status === 'AMBER') $status_class = 'bg-warning text-dark';
        ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card blueprint-card p-3 d-flex flex-row justify-content-between align-items-center hover-glow touch-target-card"
            onclick="window.location.href='<?= url_to('ambulance.hospital.detail', $h->id) ?>'">
            <div class="d-flex align-items-center gap-3">
              <span class="badge <?= $status_class ?> p-2 rounded-circle" style="width: 12px; height: 12px; display: inline-block;"></span>
              <div>
                <h4 class="h6 m-0 fw-bold text-cream"><?= esc($h->name) ?></h4>
                <small class="text-muted"><?= esc($item['distance']) ?> km away &nbsp;·&nbsp; ETA: <?= esc($item['eta']) ?> min</small>
              </div>
            </div>
            <div class="text-end">
              <span class="badge bg-secondary py-2 px-3"><?= esc($h->bays_available) ?> bays</span>
            </div>
          </div>
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

    // 1. Mapbox Initialization
    // Mapped hospital coordinates
    const hospitals = [
      <?php foreach ($hospitals as $item) : ?> {
          id: <?= $item['hospital']->id ?>,
          name: "<?= esc($item['hospital']->name) ?>",
          status: "<?= esc($item['hospital']->status) ?>",
          lat: <?= (float) $item['hospital']->lat ?>,
          lng: <?= (float) $item['hospital']->lng ?>,
        },
      <?php endforeach; ?>
    ];

    const myLat = <?= $ambulance->current_lat ?? -1.2921 ?>;
    const myLng = <?= $ambulance->current_lng ?? 36.8219 ?>;

    mapboxgl.accessToken = '<?= esc($mapbox_token) ?>';

    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/dark-v11', // Premium dark theme matching ClearBay aesthetics
      center: [myLng, myLat],
      zoom: 12
    });

    // 2. Add Paramedic Location Marker
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

    // 3. Add Hospital Markers
    hospitals.forEach(h => {
      let color = colorSage; // Success green
      if (h.status === 'RED') color = colorRed; // Danger red
      else if (h.status === 'AMBER') color = colorAmber; // Warning amber

      const hMarker = document.createElement('div');
      hMarker.style.backgroundColor = color;
      hMarker.style.width = '14px';
      hMarker.style.height = '14px';
      hMarker.style.borderRadius = '4px'; // Square block
      hMarker.style.cursor = 'pointer';

      hMarker.addEventListener('click', () => {
        window.location.href = '/ambulance/hospital/' + h.id;
      });

      new mapboxgl.Marker(hMarker)
        .setLngLat([h.lng, h.lat])
        .setPopup(new mapboxgl.Popup({
          offset: 25
        }).setHTML('<h6>' + h.name + '</h6><p>Status: ' + h.status + '</p>'))
        .addTo(map);
    });

    // 4. GPS Telemetry Coordinate Streamer (Simulated update every 30s)
    const streamLocation = async () => {
      // Simulate slight movement en-route
      const offsetLat = (Math.random() - 0.5) * 0.002;
      const offsetLng = (Math.random() - 0.5) * 0.002;
      const simLat = myLat + offsetLat;
      const simLng = myLng + offsetLng;

      try {
        const formData = new FormData();
        formData.append('lat', simLat.toFixed(6));
        formData.append('lng', simLng.toFixed(6));

        await fetch('<?= url_to('ambulance.location.update') ?>', {
          method: 'POST',
          body: formData
        });
      } catch (err) {
        console.error('Telemetry update failed:', err);
      }
    };

    setInterval(streamLocation, 30000);
  });
</script>

<?= $this->endSection() ?>