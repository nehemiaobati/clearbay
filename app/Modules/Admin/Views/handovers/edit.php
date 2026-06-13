<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Handover|null $handover
 * @var array $hospitals
 * @var array $ambulances
 */
$isEdit = isset($handover) && $handover->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container py-5 mt-5">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.handovers.list') ?>" class="mono-label text-decoration-none" style="color: var(--color-brand-primary);">
      ← Back to Handovers List
    </a>
  </div>

  <!-- Header -->
  <div class="mb-4 reveal">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Handover' : 'New Handover' ?></span>
    </div>
    <h1 class="s-title mb-0">
      <?= $isEdit ? 'Edit Queue <span class="fst-italic text-secondary">Handover</span>' : 'Dispatch New <span class="fst-italic text-secondary">Handover</span>' ?>
    </h1>
  </div>

  <!-- Form Card -->
  <div class="row justify-content-start">
    <div class="col-lg-8">
      <div class="card border-secondary border-opacity-10 p-4 p-md-5" style="background: var(--color-bg-card);">

        <!-- Flash validation errors -->
        <?php if (session()->has('errors')) : ?>
          <div class="alert alert-danger mb-4 p-3" role="alert">
            <h5 class="alert-heading fw-bold mb-2 text-danger">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3">
              <?php foreach (session()->get('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? url_to('admin.handovers.update', $handover->id) : url_to('admin.handovers.create') ?>" method="POST">
          <?= csrf_field() ?>

          <!-- Required: Ambulance + Hospital -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <select id="ambulanceId" name="ambulanceId" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.ambulanceId') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Ambulance</option>
                  <?php
                  $currentAmbulance = old('ambulanceId', $handover->ambulance_id ?? '');
                  foreach ($ambulances as $ambulance) :
                  ?>
                    <option value="<?= esc($ambulance->id) ?>" <?= (int) $currentAmbulance === (int) $ambulance->id ? 'selected' : '' ?>>
                      <?= esc($ambulance->unit_id) ?> (<?= esc($ambulance->provider) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <label for="ambulanceId">Ambulance Unit *</label>
                <?php if (session('errors.ambulanceId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.ambulanceId')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <select id="hospitalId" name="hospitalId" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.hospitalId') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Destination Hospital</option>
                  <?php
                  $currentHospital = old('hospitalId', $handover->hospital_id ?? '');
                  foreach ($hospitals as $hospital) :
                  ?>
                    <option value="<?= esc($hospital->id) ?>" <?= (int) $currentHospital === (int) $hospital->id ? 'selected' : '' ?>>
                      <?= esc($hospital->name) ?> (<?= esc($hospital->code) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <label for="hospitalId">Destination Hospital *</label>
                <?php if (session('errors.hospitalId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.hospitalId')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Required: Patient Info + Acuity -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <input type="number" id="patientAge" name="patientAge" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.patientAge') ? 'is-invalid' : '' ?>" placeholder="Age" min="0" required
                  value="<?= esc(old('patientAge', $handover->patient_age ?? '')) ?>">
                <label for="patientAge">Patient Age *</label>
                <?php if (session('errors.patientAge')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.patientAge')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <select id="patientGender" name="patientGender" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.patientGender') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Gender</option>
                  <?php
                  $genders = ['M' => 'Male', 'F' => 'Female'];
                  $currentGender = old('patientGender', $handover->patient_gender ?? '');
                  foreach ($genders as $val => $lbl) :
                  ?>
                    <option value="<?= esc($val) ?>" <?= $currentGender === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="patientGender">Patient Gender *</label>
                <?php if (session('errors.patientGender')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.patientGender')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <select id="acuity" name="acuity" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.acuity') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Acuity</option>
                  <?php
                  $acuities = ['Critical', 'Serious', 'Stable'];
                  $currentAcuity = old('acuity', $handover->acuity ?? '');
                  foreach ($acuities as $acuityOption) :
                  ?>
                    <option value="<?= esc($acuityOption) ?>" <?= $currentAcuity === $acuityOption ? 'selected' : '' ?>><?= esc($acuityOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="acuity">Acuity Level *</label>
                <?php if (session('errors.acuity')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.acuity')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Required: ETA, Wait Time, Status -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <input type="number" id="etaMinutes" name="etaMinutes" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.etaMinutes') ? 'is-invalid' : '' ?>" placeholder="ETA Minutes" min="0" required
                  value="<?= esc(old('etaMinutes', $handover->eta_minutes ?? '')) ?>">
                <label for="etaMinutes">ETA (Minutes) *</label>
                <div class="small font-monospace text-secondary mt-1">Set to 0 if already arrived</div>
                <?php if (session('errors.etaMinutes')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.etaMinutes')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <input type="number" id="waitTimeMinutes" name="waitTimeMinutes" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.waitTimeMinutes') ? 'is-invalid' : '' ?>" placeholder="Wait Time" min="0" required
                  value="<?= esc(old('waitTimeMinutes', $handover->wait_time_minutes ?? '0')) ?>">
                <label for="waitTimeMinutes">Off-Load Wait Time (Mins) *</label>
                <?php if (session('errors.waitTimeMinutes')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.waitTimeMinutes')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <select id="status" name="status" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status</option>
                  <?php
                  $statuses = ['En route', 'Arrived', 'Acknowledged', 'Preparing', 'Cleared'];
                  $currentStatus = old('status', $handover->status ?? '');
                  foreach ($statuses as $statusOption) :
                  ?>
                    <option value="<?= esc($statusOption) ?>" <?= $currentStatus === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="status">Dispatch / Queue Status *</label>
                <?php if (session('errors.status')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Optional: Completion Details (collapsible) -->
          <details class="mb-3">
            <summary class="mono-label text-secondary border-bottom pb-2 mb-3" style="cursor: pointer;">Completion Details</summary>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" id="bayNumber" name="bayNumber" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.bayNumber') ? 'is-invalid' : '' ?>" placeholder="e.g. Bay 3"
                    value="<?= esc(old('bayNumber', $handover->bay_number ?? '')) ?>">
                  <label for="bayNumber">Bay Number</label>
                  <?php if (session('errors.bayNumber')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.bayNumber')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <div class="form-floating">
                <textarea id="notes" name="notes" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.notes') ? 'is-invalid' : '' ?>" placeholder="Notes" style="height: 100px;" maxlength="200"><?= esc(old('notes', $handover->notes ?? '')) ?></textarea>
                <label for="notes">Handover Notes</label>
                <?php if (session('errors.notes')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.notes')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </details>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary touch-target">
              <?= $isEdit ? 'Save Changes' : 'Dispatch Handover' ?>
            </button>
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary touch-target">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>