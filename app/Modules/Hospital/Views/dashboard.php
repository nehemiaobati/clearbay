<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Zone 1: ED Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text">Module: Hospital ED</span>
    </div>
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
      <h1 class="s-title admin-heading m-0"><?= esc($hospital->name) ?></h1>
      <div class="d-flex gap-2">
        <a href="<?= url_to('hospital.analytics') ?>" class="btn btn-sm btn-outline-secondary">View Analytics</a>
        <a href="<?= url_to('auth.logout') ?>" class="btn btn-sm btn-outline-danger">Sign Out</a>
      </div>
    </div>
  </div>

  <!-- Zone 2: ED Status Banner -->
  <div class="mb-4 reveal">
    <div id="statusBanner" class="p-4 card blueprint-card text-center text-uppercase fw-bold fs-4 pointer-event" data-bs-toggle="modal" data-bs-target="#statusModal">
      Loading ED status...
    </div>
  </div>

  <!-- Zone 3: Metric Cards -->
  <div class="row g-4 mb-5 reveal">
    <div class="col-6 col-md-3">
      <div class="card blueprint-card p-4 text-center">
        <span class="d-block admin-stat-val text-primary" id="metricQueueCount">0</span>
        <span class="mono-label text-muted mt-2 d-block">Ambulances in Queue</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card blueprint-card p-4 text-center">
        <span class="d-block admin-stat-val" id="metricAvgWait">0</span>
        <span class="mono-label text-muted mt-2 d-block">Avg Wait Today (min)</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card blueprint-card p-4 text-center">
        <span class="d-block admin-stat-val text-success" id="metricHandoversCount">0</span>
        <span class="mono-label text-muted mt-2 d-block">Completed Today</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card blueprint-card p-4 text-center">
        <span class="d-block admin-stat-val" id="metricBaseline">0</span>
        <span class="mono-label text-muted mt-2 d-block">vs. Baseline Average</span>
      </div>
    </div>
  </div>

  <!-- Zone 4: Ambulance Queue Table -->
  <div class="card blueprint-card p-4 reveal">
    <h3 class="admin-card-heading mb-4">Active Ambulance Queue</h3>
    <div class="table-responsive">
      <table class="table queue-table align-middle">
        <thead>
          <tr class="mono-label text-muted">
            <th>Unit ID</th>
            <th>Provider</th>
            <th>Patient</th>
            <th>Complaint</th>
            <th>Acuity</th>
            <th>ETA / Status</th>
            <th>Wait Time</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody id="queueTableBody">
          <tr>
            <td colspan="8" class="text-center text-muted py-4">Loading active ambulance queue...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal SC-04: ED Status Control -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card blueprint-card">
      <div class="modal-header border-secondary border-opacity-10">
        <h5 class="modal-title" id="statusModalLabel">Update ED Capacity Status</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="statusForm" class="form-dark" novalidate>
        <div class="modal-body">
          <div class="d-flex justify-content-between gap-2 mb-4">
            <button type="button" class="btn btn-outline-success flex-fill py-3 status-select-btn" data-status="GREEN">GREEN<br><small class="mono-label">Accepting</small></button>
            <button type="button" class="btn btn-outline-warning flex-fill py-3 status-select-btn" data-status="AMBER">AMBER<br><small class="mono-label">Busy</small></button>
            <button type="button" class="btn btn-outline-danger flex-fill py-3 status-select-btn" data-status="RED">RED<br><small class="mono-label">Full</small></button>
          </div>
          <input type="hidden" name="status" id="selectedStatus" value="">

          <div class="mb-3">
            <label for="baysAvailableInput" class="form-label">Available Ambulance Bays</label>
            <input type="number" class="form-control" name="bays_available" id="baysAvailableInput" placeholder="Bays" min="0" required>
          </div>
        </div>
        <div class="modal-footer border-secondary border-opacity-10">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal SC-05: Handover Completion Screen -->
<div class="modal fade" id="handoverModal" tabindex="-1" aria-labelledby="handoverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card blueprint-card">
      <div class="modal-header border-secondary border-opacity-10">
        <h5 class="modal-title" id="handoverModalLabel">Complete Ambulance Handover</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="handoverForm" class="form-dark" novalidate>
        <div class="modal-body">
          <div class="mb-4 p-3 bg-secondary bg-opacity-10 rounded">
            <span class="mono-label text-muted d-block mb-1">Ambulance Unit</span>
            <span class="fs-5 fw-bold" id="handoverUnitId">...</span>
            <span class="mono-label text-muted d-block mt-3 mb-1">Patient Details</span>
            <span class="d-block" id="handoverPatientDetails">...</span>
          </div>

          <input type="hidden" name="handover_id" id="handoverIdInput" value="">

          <div class="mb-3">
            <label for="bayNumberInput" class="form-label">Bay Number (Optional)</label>
            <input type="text" class="form-control" name="bay_number" id="bayNumberInput" placeholder="Bay 1">
          </div>

          <div class="mb-3">
            <label for="notesInput" class="form-label">Handover Notes (Optional, max 200 chars)</label>
            <textarea class="form-control" name="notes" id="notesInput" placeholder="Handover Notes" style="height: 100px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-secondary border-opacity-10">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm Handover Complete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    let currentStatus = "<?= esc($hospital->status) ?>";
    let currentBays = <?= (int) $hospital->bays_available ?>;

    // 1. Initial State UI Load
    const updateBannerUI = (status, bays) => {
      const banner = document.getElementById('statusBanner');
      banner.className = 'p-4 card blueprint-card text-center text-uppercase fw-bold fs-4 pointer-event';

      if (status === 'GREEN') {
        banner.classList.add('bg-success', 'text-white');
        banner.innerHTML = `GREEN · Accepting — ${bays} bays available`;
      } else if (status === 'AMBER') {
        banner.classList.add('bg-warning', 'text-dark');
        banner.innerHTML = `AMBER · Busy — ${bays} bays available`;
      } else {
        banner.classList.add('bg-danger', 'text-white');
        banner.innerHTML = `RED · Full — no bays available`;
      }

      // Pre-fill status modal elements
      document.getElementById('selectedStatus').value = status;
      document.getElementById('baysAvailableInput').value = bays;
      document.querySelectorAll('.status-select-btn').forEach(btn => {
        if (btn.dataset.status === status) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
    };

    updateBannerUI(currentStatus, currentBays);

    // Status button click handler inside modal
    document.querySelectorAll('.status-select-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.status-select-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('selectedStatus').value = btn.dataset.status;
      });
    });

    // 2. Fetch Queue API and Render
    const fetchQueue = async () => {
      try {
        const response = await fetch('<?= url_to('hospital.queue') ?>');
        if (!response.ok) throw new Error('Network error');

        const data = await response.json();
        if (data.status === 'success') {
          renderQueue(data.result);
          // Rotate CSRF tokens
          const csrfInputs = document.querySelectorAll('input[name="csrf_test_name"]');
          csrfInputs.forEach(i => i.value = data.csrf_token);
        }
      } catch (err) {
        console.error('Queue fetching failed:', err);
      }
    };

    const renderQueue = (result) => {
      // Update Metrics
      document.getElementById('metricQueueCount').textContent = result.metrics.ambulances_in_queue;
      document.getElementById('metricAvgWait').textContent = result.metrics.avg_wait_today;
      document.getElementById('metricHandoversCount').textContent = result.metrics.completed_today;

      const baseline = result.metrics.baseline_difference;
      const baselineEl = document.getElementById('metricBaseline');
      baselineEl.textContent = baseline > 0 ? `+${baseline}` : baseline;
      if (baseline < 0) {
        baselineEl.className = 'd-block admin-stat-val text-success';
      } else if (baseline > 0) {
        baselineEl.className = 'd-block admin-stat-val text-danger';
      } else {
        baselineEl.className = 'd-block admin-stat-val text-muted';
      }

      // Render Queue Table Body
      const tbody = document.getElementById('queueTableBody');
      if (result.queue.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No active ambulances in queue. All clear.</td></tr>`;
        return;
      }

      tbody.innerHTML = result.queue.map(h => {
        const wait = parseInt(h.wait_time_minutes, 10);
        let waitClass = 'bg-success text-white';
        if (wait >= 30) waitClass = 'bg-danger text-white';
        else if (wait >= 15) waitClass = 'bg-warning text-dark';

        const rowHighlight = wait >= 30 ? 'table-danger border-danger border-opacity-10' : '';

        // Formats
        const patientStr = `${h.patient_gender}, ${h.patient_age}`;
        const etaStr = h.status === 'En route' ? `${h.eta_minutes} min` : 'Arrived';

        return `
          <tr class="${rowHighlight}">
            <td class="mono-label">${h.unit_id}</td>
            <td>${h.provider}</td>
            <td>${patientStr}</td>
            <td>${h.chief_complaint}</td>
            <td><span class="badge ${h.acuity === 'Critical' ? 'bg-danger' : (h.acuity === 'Serious' ? 'bg-warning text-dark' : 'bg-success')}">${h.acuity}</span></td>
            <td class="fw-bold">${etaStr}</td>
            <td><span class="badge ${waitClass}">${wait} min</span></td>
            <td class="text-end">
              ${h.status === 'En route' ? 
                `<span class="text-muted small">En Route</span>` : 
                `<button class="btn btn-sm btn-primary clear-bay-btn" 
                         data-id="${h.id}" 
                         data-unit="${h.unit_id}" 
                         data-details="${patientStr} (${h.chief_complaint})"
                         data-bs-toggle="modal" 
                         data-bs-target="#handoverModal">Clear Bay</button>`
              }
            </td>
          </tr>
        `;
      }).join('');
    };

    // Poll every 10 seconds (optimized for responsive performance)
    fetchQueue();
    setInterval(fetchQueue, 10000);

    // Event delegation to capture which handover was clicked
    document.getElementById('queueTableBody').addEventListener('click', (e) => {
      if (e.target.classList.contains('clear-bay-btn')) {
        const btn = e.target;
        document.getElementById('handoverIdInput').value = btn.dataset.id;
        document.getElementById('handoverUnitId').textContent = btn.dataset.unit;
        document.getElementById('handoverPatientDetails').textContent = btn.dataset.details;
      }
    });

    // 3. Form Submissions (AJAX)
    // A. Status Update Form
    const statusForm = document.getElementById('statusForm');
    statusForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(statusForm);

      try {
        const response = await fetch('<?= url_to('hospital.status.update') ?>', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
          // Close modal
          const modalEl = document.getElementById('statusModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          modalInstance.hide();

          currentStatus = formData.get('status');
          currentBays = parseInt(formData.get('bays_available'), 10);
          updateBannerUI(currentStatus, currentBays);
          fetchQueue();
        } else {
          alert(data.message);
        }
      } catch (err) {
        alert('Failed to update status.');
      }
    });

    // B. Handover Form
    const handoverForm = document.getElementById('handoverForm');
    handoverForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(handoverForm);

      try {
        const response = await fetch('<?= url_to('hospital.handover.complete') ?>', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
          const modalEl = document.getElementById('handoverModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          modalInstance.hide();

          // Reset inputs
          document.getElementById('bayNumberInput').value = '';
          document.getElementById('notesInput').value = '';

          fetchQueue();
        } else {
          alert(data.message);
        }
      } catch (err) {
        alert('Failed to complete handover.');
      }
    });
  });
</script>

<?= $this->endSection() ?>