<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var int $pre_id
 * @var array $status
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page max-width-600">
  <div class="card blueprint-card p-4 p-md-5 text-center reveal" id="runCard">
    <!-- Live Status Icon -->
    <div id="statusIconContainer" class="mb-4">
      <!-- Pulsing radar ring for en-route state -->
      <div id="radarRing" class="spinner-grow text-primary active-run-radar" role="status"></div>
      <div id="successMark" class="d-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#7AB890" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
      </div>
    </div>

    <!-- Main Message -->
    <h2 class="h3 fw-bold text-cream mb-2" id="mainStatusText">En Route to Facility</h2>
    <p class="text-muted fs-5 mb-4" id="hospitalName"><?= esc($status['hospital_name']) ?></p>

    <!-- ETA Countdown -->
    <div id="countdownBox" class="p-4 bg-secondary bg-opacity-10 rounded mb-4">
      <span class="mono-label text-muted d-block mb-1">Estimated Arrival</span>
      <span class="d-block fs-1 fw-bold text-primary" id="etaDisplay"><?= esc($status['eta_minutes']) ?> min</span>
    </div>

    <!-- Status Details / Instructions -->
    <div id="statusDetails" class="p-3 border border-secondary border-opacity-10 rounded mb-4 text-start">
      <span class="mono-label text-muted d-block mb-2">Hospital Feed</span>
      <span class="d-block" id="feedMessage">ED notified. Patient details transmitted en route.</span>
    </div>

    <!-- Action Group -->
    <div id="actionBox" class="d-none">
      <a href="<?= url_to('ambulance.home') ?>" class="btn btn-primary w-100 py-3 fw-bold fs-6 touch-target-btn">
        Start New Run
      </a>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const preId = <?= $pre_id ?>;
    
    // Telemetry Polling (every 5 seconds)
    const pollStatus = async () => {
      try {
        const response = await fetch('/ambulance/run/' + preId);
        if (!response.ok) return;

        const apiResponse = await fetch('/ambulance/run/' + preId + '?ajax=1');
        if (!apiResponse.ok) return;

        const data = await apiResponse.json();
        
        if (data.status === 'success') {
          updateUI(data.result);
        }
      } catch (err) {
        console.error('Active run polling failed:', err);
      }
    };

    const updateUI = (run) => {
      const radar = document.getElementById('radarRing');
      const success = document.getElementById('successMark');
      const mainText = document.getElementById('mainStatusText');
      const etaDisplay = document.getElementById('etaDisplay');
      const feed = document.getElementById('feedMessage');
      const actionBox = document.getElementById('actionBox');
      const countdownBox = document.getElementById('countdownBox');

      // Update ETA
      if (run.eta_minutes > 0) {
        etaDisplay.textContent = `${run.eta_minutes} min`;
      } else {
        etaDisplay.textContent = 'Arrived';
      }

      if (run.status === 'En route') {
        mainText.textContent = 'En Route to Facility';
        feed.textContent = 'ED notified. Patient details transmitted en route.';
      } else if (run.status === 'Preparing' || run.status === 'Acknowledged' || run.status === 'Arrived') {
        mainText.textContent = 'Bay Preparation Underway';
        mainText.className = 'h3 fw-bold text-success mb-2';
        feed.innerHTML = '<strong class="text-success">✔ Bay is being prepared for your arrival.</strong> ED clinicians are standing by.';
        radar.className = 'spinner-grow text-success active-run-radar';
      } else if (run.status === 'Cleared') {
        // Confirmed clear!
        mainText.textContent = 'Handover Confirmed';
        mainText.className = 'h3 fw-bold text-success mb-2';
        feed.textContent = 'Handover completed. Crew is cleared and free to return to service.';
        
        // Toggle indicators
        radar.classList.add('d-none');
        success.classList.remove('d-none');
        countdownBox.classList.add('d-none');
        actionBox.classList.remove('d-none');
      }
    };

    // Polling setup
    setInterval(pollStatus, 5000);
  });
</script>

<?= $this->endSection() ?>
