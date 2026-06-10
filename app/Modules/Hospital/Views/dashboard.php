<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 * @var string $user_role
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
        <?php if ($user_role === 'hospital_admin') : ?>
          <a href="<?= url_to('hospital.users.list') ?>" class="btn btn-sm btn-outline-secondary" style="min-height: 36px;">Manage Users</a>
        <?php endif; ?>
        <?php if ($user_role !== 'nurse') : ?>
          <a href="<?= url_to('hospital.analytics') ?>" class="btn btn-sm btn-outline-secondary" style="min-height: 36px;">View Analytics</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Zone 2: ED Status Banner — semantic button, not clickable div -->
  <div class="mb-4 reveal">
    <button type="button" id="statusBanner" class="p-4 card blueprint-card text-center text-uppercase fw-bold fs-4 w-100 bg-transparent text-reset focus-ring" data-bs-toggle="modal" data-bs-target="#statusModal" style="min-height: 48px;">
      Loading ED status...
    </button>
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

  <!-- Zone 4: Ambulance Queue - Mobile Cards / Desktop Table -->
  <div class="card blueprint-card p-4 reveal">
    <h3 class="admin-card-heading mb-4">Active Ambulance Queue</h3>

    <!-- Mobile Queue Cards (visible <768px) -->
    <div id="queueMobileContainer" class="d-md-none">
      <div class="text-center text-muted py-4">Loading active ambulance queue...</div>
    </div>

    <!-- Desktop Table (visible md+) -->
    <div class="d-none d-md-block">
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
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="d-flex justify-content-between gap-2 mb-4" role="radiogroup" aria-labelledby="statusGroupLabel">
            <span class="visually-hidden" id="statusGroupLabel">Select ED capacity status</span>
            <button type="button" role="radio" aria-checked="false" class="btn btn-outline-success flex-fill py-3 status-select-btn" style="min-height: 48px;" data-status="GREEN">GREEN<br><small class="mono-label">Accepting</small></button>
            <button type="button" role="radio" aria-checked="false" class="btn btn-outline-warning flex-fill py-3 status-select-btn" style="min-height: 48px;" data-status="AMBER">AMBER<br><small class="mono-label">Busy</small></button>
            <button type="button" role="radio" aria-checked="false" class="btn btn-outline-danger flex-fill py-3 status-select-btn" style="min-height: 48px;" data-status="RED">RED<br><small class="mono-label">Full</small></button>
          </div>
          <input type="hidden" name="status" id="selectedStatus" value="">

          <div class="mb-3">
            <label for="baysAvailableInput" class="form-label">Available Ambulance Bays *</label>
            <input type="number" class="form-control" name="bays_available" id="baysAvailableInput" placeholder="Bays" min="0" required>
          </div>
        </div>
        <div class="modal-footer border-secondary border-opacity-10">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="min-height: 48px;">Cancel</button>
          <button type="submit" class="btn btn-primary" style="min-height: 48px;">Update Status</button>
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
        <?= csrf_field() ?>
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
            <textarea class="form-control" name="notes" id="notesInput" placeholder="Handover Notes" style="height: 100px;" maxlength="200"></textarea>
          </div>
        </div>
        <div class="modal-footer border-secondary border-opacity-10">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="min-height: 48px;">Cancel</button>
          <button type="submit" class="btn btn-primary" style="min-height: 48px;">Confirm Handover Complete</button>
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
      banner.className = 'p-4 card blueprint-card text-center text-uppercase fw-bold fs-4 w-100 bg-transparent text-reset focus-ring';

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

      document.getElementById('selectedStatus').value = status;
      document.getElementById('baysAvailableInput').value = bays;
      document.querySelectorAll('.status-select-btn').forEach(btn => {
        if (btn.dataset.status === status) {
          btn.classList.add('active');
          btn.setAttribute('aria-checked', 'true');
        } else {
          btn.classList.remove('active');
          btn.setAttribute('aria-checked', 'false');
        }
      });
    };

    updateBannerUI(currentStatus, currentBays);

    // Status button click handler inside modal
    document.querySelectorAll('.status-select-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.status-select-btn').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-checked', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-checked', 'true');
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
          const csrfInputs = document.querySelectorAll('input[name="csrf_test_name"]');
          csrfInputs.forEach(i => i.value = data.csrf_token);
        }
      } catch (err) {
        console.error('Queue fetching failed:', err);
      }
    };

    const renderQueue = (result) => {
      const updateElText = (id, val) => {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(val)) {
          el.textContent = val;
        }
      };

      updateElText('metricQueueCount', result.metrics.ambulances_in_queue);
      updateElText('metricAvgWait', result.metrics.avg_wait_today);
      updateElText('metricHandoversCount', result.metrics.completed_today);

      const baseline = result.metrics.baseline_difference;
      const baselineEl = document.getElementById('metricBaseline');
      if (baselineEl) {
        const formattedBaseline = baseline > 0 ? `+${baseline}` : String(baseline);
        if (baselineEl.textContent !== formattedBaseline) {
          baselineEl.textContent = formattedBaseline;
        }
        const expectedClass = baseline < 0 ?
          'd-block admin-stat-val text-success' :
          (baseline > 0 ? 'd-block admin-stat-val text-danger' : 'd-block admin-stat-val text-muted');
        if (baselineEl.className !== expectedClass) {
          baselineEl.className = expectedClass;
        }
      }

      const tbody = document.getElementById('queueTableBody');
      const mobileContainer = document.getElementById('queueMobileContainer');

      if (result.queue.length === 0) {
        const emptyTableHtml = `<tr><td colspan="8" class="text-center text-muted py-4">No active ambulances in queue. All clear.</td></tr>`;
        if (tbody && tbody.innerHTML !== emptyTableHtml) {
          tbody.innerHTML = emptyTableHtml;
        }
        if (mobileContainer) {
          mobileContainer.innerHTML = `<div class="text-center text-muted py-4">No active ambulances in queue. All clear.</div>`;
        }
        return;
      }

      // --- Desktop Table Rows ---
      const tableHtml = result.queue.map(h => {
        const wait = parseInt(h.wait_time_minutes, 10);
        let waitClass = 'bg-success text-white';
        if (wait >= 30) waitClass = 'bg-danger text-white';
        else if (wait >= 15) waitClass = 'bg-warning text-dark';

        const rowHighlight = wait >= 30 ? 'row-urgent' : '';
        const patientStr = `${h.patient_gender}, ${h.patient_age}`;
        const etaStr = h.status === 'En route' ? `${h.eta_minutes} min` : 'Arrived';
        const complaintStr = h.chief_complaint || 'Walk-in / Direct';

        return `
          <tr class="${rowHighlight}">
            <td class="mono-label">${h.unit_id}</td>
            <td>${h.provider}</td>
            <td>${patientStr}</td>
            <td>${complaintStr}</td>
            <td><span class="badge ${h.acuity === 'Critical' ? 'bg-danger' : (h.acuity === 'Serious' ? 'bg-warning text-dark' : 'bg-success')}">${h.acuity}</span></td>
            <td class="fw-bold">${etaStr}</td>
            <td><span class="badge ${waitClass}">${wait} min</span></td>
            <td class="text-end">
              ${h.status === 'En route' ?
                `<button class="btn btn-sm btn-success mark-arrived-btn" style="min-height: 36px;"
                         data-id="${h.id}">Mark Arrived</button>` :
                `<button class="btn btn-sm btn-primary clear-bay-btn" style="min-height: 36px;"
                         data-id="${h.id}"
                         data-unit="${h.unit_id}"
                         data-details="${patientStr} (${complaintStr})"
                         data-bs-toggle="modal"
                         data-bs-target="#handoverModal">Clear Bay</button>`
              }
            </td>
          </tr>
        `;
      }).join('');

      if (tbody && tbody.innerHTML !== tableHtml) {
        tbody.innerHTML = tableHtml;
      }

      // --- Mobile Card Rows ---
      if (mobileContainer) {
        const mobileHtml = result.queue.map(h => {
          const wait = parseInt(h.wait_time_minutes, 10);
          let waitClass = 'bg-success text-white';
          if (wait >= 30) waitClass = 'bg-danger text-white';
          else if (wait >= 15) waitClass = 'bg-warning text-dark';

          const patientStr = `${h.patient_gender}, ${h.patient_age}`;
          const etaStr = h.status === 'En route' ? `${h.eta_minutes} min` : 'Arrived';
          const complaintStr = h.chief_complaint || 'Walk-in / Direct';

          const actionBtn = h.status === 'En route' ?
            `<button class="btn btn-sm btn-success mark-arrived-btn flex-fill" style="min-height: 48px;" data-id="${h.id}">Mark Arrived</button>` :
            `<button class="btn btn-sm btn-primary clear-bay-btn flex-fill" style="min-height: 48px;" data-id="${h.id}" data-unit="${h.unit_id}" data-details="${patientStr} (${complaintStr})" data-bs-toggle="modal" data-bs-target="#handoverModal">Clear Bay</button>`;

          const urgentCard = wait >= 30 ? 'row-urgent' : '';

          return `
            <div class="list-card-item flex-column align-items-start gap-2 py-3 ${urgentCard}">
              <div class="d-flex justify-content-between align-items-center w-100">
                <span class="td-name fw-bold">${h.unit_id}</span>
                <span class="badge ${h.acuity === 'Critical' ? 'bg-danger' : (h.acuity === 'Serious' ? 'bg-warning text-dark' : 'bg-success')}">${h.acuity}</span>
              </div>
              <div class="d-flex justify-content-between w-100" style="font-size: 0.85rem; color: var(--color-text-muted);">
                <span>${h.provider}</span>
                <span>${patientStr}</span>
              </div>
              <div class="d-flex justify-content-between w-100" style="font-size: 0.85rem; color: var(--color-text-muted);">
                <span>${complaintStr}</span>
                <span class="fw-bold">${etaStr}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center w-100 gap-3" style="font-size: 0.85rem;">
                <span class="badge ${waitClass} flex-shrink-0" style="padding: 10px 14px; font-size: 0.85rem;">${wait} min</span>
                ${actionBtn}
              </div>
            </div>
          `;
        }).join('');

        if (mobileContainer.innerHTML !== mobileHtml) {
          mobileContainer.innerHTML = mobileHtml;
        }
      }
    };

    fetchQueue();
    setInterval(fetchQueue, 10000);

    const handleQueueAction = async (e) => {
      const arrivedBtn = e.target.closest('.mark-arrived-btn');
      if (arrivedBtn) {
        const handoverId = arrivedBtn.dataset.id;
        const formData = new FormData();
        const csrfInput = document.querySelector('input[name="csrf_test_name"]');
        if (csrfInput) formData.append(csrfInput.name, csrfInput.value);
        formData.append('handover_id', handoverId);

        try {
          const response = await fetch('<?= url_to('hospital.handover.arrived') ?>', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

          if (data.status === 'success') {
            // Rotate CSRF token
            const csrfInputs = document.querySelectorAll('input[name="csrf_test_name"]');
            csrfInputs.forEach(i => i.value = data.csrf_token);
            fetchQueue();
          } else {
            alert(data.message);
          }
        } catch (err) {
          alert('Failed to mark as arrived.');
        }
        return;
      }

      if (e.target.classList.contains('clear-bay-btn')) {
        const btn = e.target;
        document.getElementById('handoverIdInput').value = btn.dataset.id;
        document.getElementById('handoverUnitId').textContent = btn.dataset.unit;
        document.getElementById('handoverPatientDetails').textContent = btn.dataset.details;
      }
    };

    document.getElementById('queueTableBody').addEventListener('click', handleQueueAction);
    document.getElementById('queueMobileContainer').addEventListener('click', handleQueueAction);

    // 3. Form Submissions (AJAX)
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