<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 * @var int $eta
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page max-width-600">
  <!-- Inner Back navigation -->
  <div class="mb-4 reveal">
    <a href="<?= url_to('ambulance.hospital.detail', $hospital->id) ?>" class="mono-label text-decoration-none admin-back">← Back to Detail</a>
  </div>

  <div class="card blueprint-card p-4 p-md-5 reveal">
    <div class="mb-4 border-bottom border-secondary border-opacity-10 pb-3">
      <span class="mono-label text-muted d-block mb-1">Destination</span>
      <h2 class="h4 fw-bold text-cream"><?= esc($hospital->name) ?></h2>
      <span class="badge bg-secondary mt-1">Est. Wait: ~8 min</span>
    </div>

    <form id="preNotifyForm" class="form-dark" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="hospital_id" value="<?= (int) $hospital->id ?>">

      <div id="formFeedback" class="alert alert-danger d-none mb-3" role="alert"></div>

      <!-- Patient Age -->
      <div class="form-floating mb-3">
        <input type="number" name="patient_age" id="patientAgeInput" class="form-control" placeholder="Age" min="0" max="120" required>
        <label for="patientAgeInput">Patient Age (Years) *</label>
        <div class="invalid-feedback" id="error_patient_age">Please enter a valid age.</div>
      </div>

      <!-- Patient Sex -->
      <div class="form-floating mb-3">
        <select name="patient_sex" id="patientSexInput" class="form-select" required>
          <option value="" disabled selected>Select patient sex</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Not Specified">Not Specified</option>
        </select>
        <label for="patientSexInput">Patient Sex *</label>
        <div class="invalid-feedback" id="error_patient_sex">Please select patient sex.</div>
      </div>

      <!-- Chief Complaint -->
      <div class="form-floating mb-3">
        <select name="chief_complaint" id="chiefComplaintInput" class="form-select" required>
          <option value="" disabled selected>Select chief complaint</option>
          <option value="Cardiac Arrest">Cardiac Arrest</option>
          <option value="Acute Coronary Syndrome">Acute Coronary Syndrome</option>
          <option value="Stroke / CVA">Stroke / CVA</option>
          <option value="Road Traffic Accident">Road Traffic Accident</option>
          <option value="Obstetric Emergency">Obstetric Emergency</option>
          <option value="Respiratory Distress">Respiratory Distress</option>
          <option value="Trauma">Trauma</option>
          <option value="Sepsis">Sepsis</option>
          <option value="Other">Other</option>
        </select>
        <label for="chiefComplaintInput">Chief Complaint *</label>
        <div class="invalid-feedback" id="error_chief_complaint">Please select a chief complaint.</div>
      </div>

      <!-- Acuity Level Buttons (Fitts's Law touch compliance) -->
      <div class="mb-4">
        <label class="mono-label text-muted d-block mb-2">Acuity Level *</label>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-danger flex-fill py-3 acuity-btn fw-bold touch-target-btn-lg" data-acuity="Critical">Critical</button>
          <button type="button" class="btn btn-outline-warning flex-fill py-3 acuity-btn fw-bold touch-target-btn-lg" data-acuity="Serious">Serious</button>
          <button type="button" class="btn btn-outline-success flex-fill py-3 acuity-btn fw-bold touch-target-btn-lg" data-acuity="Stable">Stable</button>
        </div>
        <input type="hidden" name="acuity" id="acuityInput" value="" required>
        <div class="text-danger small d-none mt-1" id="error_acuity">Please select acuity level.</div>
      </div>

      <!-- Notes -->
      <div class="form-floating mb-3">
        <textarea name="notes" id="notesInput" class="form-control" placeholder="Notes" style="height: 80px;" maxlength="150"></textarea>
        <label for="notesInput">En-route Notes (Optional, max 150 chars)</label>
      </div>

      <!-- ETA (Read Only) -->
      <div class="form-floating mb-4">
        <input type="number" name="eta_minutes" id="etaInput" class="form-control" value="<?= $eta ?>" readonly>
        <label for="etaInput">Calculated ETA (Minutes)</label>
      </div>

      <!-- Submit Button -->
      <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 fw-bold fs-5 d-flex align-items-center justify-content-center touch-target-btn-lg">
        <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
        <span id="submitText">Send Pre-Notification</span>
      </button>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Acuity Selection Handler
    const acuityButtons = document.querySelectorAll('.acuity-btn');
    const acuityInput = document.getElementById('acuityInput');
    const errorAcuity = document.getElementById('error_acuity');

    acuityButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        acuityButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        acuityInput.value = btn.dataset.acuity;
        errorAcuity.classList.add('d-none');
      });
    });

    // 2. Submit Handler via AJAX
    const form = document.getElementById('preNotifyForm');
    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    const submitText = document.getElementById('submitText');
    const feedback = document.getElementById('formFeedback');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      // Validation Check
      if (!acuityInput.value) {
        errorAcuity.classList.remove('d-none');
        return;
      }

      // Reset styles
      feedback.classList.add('d-none');
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

      // Loading state
      submitBtn.disabled = true;
      spinner.classList.remove('d-none');
      submitText.textContent = 'Sending...';

      try {
        const formData = new FormData(form);
        const response = await fetch('<?= url_to('ambulance.pre_notify.submit') ?>', {
          method: 'POST',
          body: formData
        });

        if (!response.ok) throw new Error('Network error');

        const data = await response.json();
        
        // Rotate CSRF
        const csrfInput = document.querySelector('input[name="csrf_test_name"]');
        if (csrfInput && data.csrf_token) {
          csrfInput.value = data.csrf_token;
        }

        if (data.status === 'success') {
          window.location.href = data.redirect_to;
        } else {
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
          submitText.textContent = 'Send Pre-Notification';
          
          if (data.errors) {
            Object.keys(data.errors).forEach(key => {
              const input = form.querySelector(`[name="${key}"]`);
              if (input) {
                input.classList.add('is-invalid');
                const errDiv = document.getElementById(`error_${key}`);
                if (errDiv) errDiv.textContent = data.errors[key];
              }
            });
          }
          feedback.textContent = data.message;
          feedback.classList.remove('d-none');
        }
      } catch (err) {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        submitText.textContent = 'Send Pre-Notification';
        feedback.textContent = 'Failed to submit form. Please check your internet connection.';
        feedback.classList.remove('d-none');
      }
    });
  });
</script>

<?= $this->endSection() ?>
