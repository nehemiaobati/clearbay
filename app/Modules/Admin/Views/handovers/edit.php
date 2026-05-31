<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Queue\Entities\Handover|null $handover
 * @var array $hospitals
 * @var array $ambulances
 */
$isEdit = isset($handover) && $handover->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.handovers.list') ?>" class="mono-label text-decoration-none admin-back">
      ← Back to Handovers List
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Handover' : 'New Handover' ?></span>
    </div>
    <h1 class="s-title mb-2 admin-heading">
      <?= $isEdit ? 'Edit Queue <span class="ital dim">Handover</span>' : 'Dispatch New <span class="ital dim">Handover</span>' ?>
    </h1>
  </div>

  <!-- Form Card -->
  <div class="row justify-content-start">
    <div class="col-lg-8">
      <div class="card blueprint-card p-4 p-md-5">

        <!-- Flash validation errors -->
        <?php if (session()->has('errors')) : ?>
          <div class="alert alert-danger card blueprint-card border-danger mb-4 p-3" role="alert">
            <h5 class="alert-heading font-family-sans fw-bold mb-2 text-danger admin-error-heading">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3 admin-error-list">
              <?php foreach (session()->get('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? url_to('admin.handovers.update', $handover->id) : url_to('admin.handovers.create') ?>" method="POST" class="form-dark">
          <?= csrf_field() ?>

          <div class="row">
            <!-- Ambulance unit lookup select -->
            <div class="col-md-6 mb-4">
              <div>
                <label for="ambulanceId" class="form-label">Ambulance Unit *</label>
                <select id="ambulanceId" name="ambulanceId" class="form-select admin-form-select <?= session('errors.ambulanceId') ? 'is-invalid' : '' ?>" required>
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
                <?php if (session('errors.ambulanceId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.ambulanceId')) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Hospital destination lookup select -->
            <div class="col-md-6 mb-4">
              <div>
                <label for="hospitalId" class="form-label">Destination Hospital *</label>
                <select id="hospitalId" name="hospitalId" class="form-select admin-form-select <?= session('errors.hospitalId') ? 'is-invalid' : '' ?>" required>
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
                <?php if (session('errors.hospitalId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.hospitalId')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Patient Age -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="patientAge" class="form-label">Patient Age *</label>
                <input type="number" id="patientAge" name="patientAge" class="form-control <?= session('errors.patientAge') ? 'is-invalid' : '' ?>" placeholder="Age" min="0" required
                  value="<?= esc(old('patientAge', $handover->patient_age ?? '')) ?>">
                <?php if (session('errors.patientAge')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.patientAge')) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Patient Gender -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="patientGender" class="form-label">Patient Gender *</label>
                <select id="patientGender" name="patientGender" class="form-select admin-form-select <?= session('errors.patientGender') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Gender</option>
                  <?php
                  $genders = ['M' => 'Male', 'F' => 'Female'];
                  $currentGender = old('patientGender', $handover->patient_gender ?? '');
                  foreach ($genders as $val => $lbl) :
                  ?>
                    <option value="<?= esc($val) ?>" <?= $currentGender === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (session('errors.patientGender')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.patientGender')) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Acuity Level -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="acuity" class="form-label">Acuity Level *</label>
                <select id="acuity" name="acuity" class="form-select admin-form-select <?= session('errors.acuity') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Acuity</option>
                  <?php
                  $acuities = ['Critical', 'Serious', 'Stable'];
                  $currentAcuity = old('acuity', $handover->acuity ?? '');
                  foreach ($acuities as $acuity) :
                  ?>
                    <option value="<?= esc($acuity) ?>" <?= $currentAcuity === $acuity ? 'selected' : '' ?>><?= esc($acuity) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (session('errors.acuity')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.acuity')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- ETA Minutes -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="etaMinutes" class="form-label">ETA (Minutes) *</label>
                <input type="number" id="etaMinutes" name="etaMinutes" class="form-control <?= session('errors.etaMinutes') ? 'is-invalid' : '' ?>" placeholder="ETA Minutes" min="0" required
                  value="<?= esc(old('etaMinutes', $handover->eta_minutes ?? '')) ?>">
                <div class="form-note mt-1 text-muted admin-form-note">Set to 0 if already arrived</div>
                <?php if (session('errors.etaMinutes')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.etaMinutes')) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Wait Time Minutes -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="waitTimeMinutes" class="form-label">Off-Load Wait Time (Mins) *</label>
                <input type="number" id="waitTimeMinutes" name="waitTimeMinutes" class="form-control <?= session('errors.waitTimeMinutes') ? 'is-invalid' : '' ?>" placeholder="Wait Time" min="0" required
                  value="<?= esc(old('waitTimeMinutes', $handover->wait_time_minutes ?? '0')) ?>">
                <?php if (session('errors.waitTimeMinutes')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.waitTimeMinutes')) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Status Level -->
            <div class="col-md-4 mb-4">
              <div>
                <label for="status" class="form-label">Dispatch / Queue Status *</label>
                <select id="status" name="status" class="form-select admin-form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status</option>
                  <?php
                  $statuses = ['En route', 'Arrived', 'Acknowledged', 'Preparing', 'Cleared'];
                  $currentStatus = old('status', $handover->status ?? '');
                  foreach ($statuses as $status) :
                  ?>
                    <option value="<?= esc($status) ?>" <?= $currentStatus === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (session('errors.status')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary admin-btn-submit">
              <?= $isEdit ? 'Save Changes' : 'Dispatch Handover' ?>
            </button>
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary admin-btn-submit">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>