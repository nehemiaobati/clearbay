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

<div class="container" style="margin-top: 120px; margin-bottom: 80px;">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.handovers.list') ?>" class="mono-label text-decoration-none" style="color: var(--sage-ll) !important;">
      ← Back to Handovers List
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Handover' : 'New Handover' ?></span>
    </div>
    <h1 class="s-title mb-2" style="font-family: var(--serif); font-weight: 700; color: var(--cream); font-size: 2.2rem; line-height: 1.2;">
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
            <h5 class="alert-heading font-family-sans fw-bold mb-2 text-danger" style="font-size: 0.95rem;">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
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
              <div class="form-floating">
                <select id="ambulanceId" name="ambulanceId" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
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
              </div>
            </div>

            <!-- Hospital destination lookup select -->
            <div class="col-md-6 mb-4">
              <div class="form-floating">
                <select id="hospitalId" name="hospitalId" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
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
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Patient Age -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <input type="number" id="patientAge" name="patientAge" class="form-control" placeholder="Age" min="0" required 
                       value="<?= esc(old('patientAge', $handover->patient_age ?? '')) ?>">
                <label for="patientAge">Patient Age *</label>
              </div>
            </div>

            <!-- Patient Gender -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <select id="patientGender" name="patientGender" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
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
              </div>
            </div>

            <!-- Acuity Level -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <select id="acuity" name="acuity" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Acuity</option>
                  <?php 
                  $acuities = ['Critical', 'Serious', 'Stable'];
                  $currentAcuity = old('acuity', $handover->acuity ?? '');
                  foreach ($acuities as $acuity) : 
                  ?>
                    <option value="<?= esc($acuity) ?>" <?= $currentAcuity === $acuity ? 'selected' : '' ?>><?= esc($acuity) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="acuity">Acuity Level *</label>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- ETA Minutes -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <input type="number" id="etaMinutes" name="etaMinutes" class="form-control" placeholder="ETA Minutes" min="0" required 
                       value="<?= esc(old('etaMinutes', $handover->eta_minutes ?? '')) ?>">
                <label for="etaMinutes">ETA (Minutes) *</label>
                <div class="form-note mt-1 text-muted" style="font-size: 0.72rem;">Set to 0 if already arrived</div>
              </div>
            </div>

            <!-- Wait Time Minutes -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <input type="number" id="waitTimeMinutes" name="waitTimeMinutes" class="form-control" placeholder="Wait Time" min="0" required 
                       value="<?= esc(old('waitTimeMinutes', $handover->wait_time_minutes ?? '0')) ?>">
                <label for="waitTimeMinutes">Off-Load Wait Time (Mins) *</label>
              </div>
            </div>

            <!-- Status Level -->
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <select id="status" name="status" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status</option>
                  <?php 
                  $statuses = ['En route', 'Arrived', 'Acknowledged', 'Preparing', 'Cleared'];
                  $currentStatus = old('status', $handover->status ?? '');
                  foreach ($statuses as $status) : 
                  ?>
                    <option value="<?= esc($status) ?>" <?= $currentStatus === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="status">Dispatch / Queue Status *</label>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary" style="font-size: 0.78rem !important; padding: 0.75rem 2rem !important;">
              <?= $isEdit ? 'Save Changes' : 'Dispatch Handover' ?>
            </button>
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary" style="font-size: 0.78rem !important; padding: 0.75rem 2rem !important;">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
