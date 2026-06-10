<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Ambulance\Entities\Ambulance|null $ambulance
 * @var array $ems_providers
 */
$isEdit = isset($ambulance) && $ambulance->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.ambulances.list') ?>" class="mono-label text-decoration-none admin-back">
      ← Back to Fleet List
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Vehicle' : 'Register Vehicle' ?></span>
    </div>
    <h1 class="s-title mb-2 admin-heading">
      <?= $isEdit ? 'Edit Ambulance <span class="ital dim">Registry</span>' : 'Register New <span class="ital dim">Ambulance</span>' ?>
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

        <form action="<?= $isEdit ? url_to('admin.ambulances.update', $ambulance->id) : url_to('admin.ambulances.create') ?>" method="POST" class="form-dark">
          <?= csrf_field() ?>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="unitId" class="form-label">Ambulance Unit ID *</label>
                <input type="text" id="unitId" name="unitId" class="form-control <?= session('errors.unitId') ? 'is-invalid' : '' ?>" placeholder="Unit ID" required
                  value="<?= esc(old('unitId', $ambulance->unit_id ?? '')) ?>" style="text-transform: uppercase;">
                <div class="form-note mt-1 text-muted admin-form-note">e.g. KRC-401, E-Plus 22, St John 05</div>
                <?php if (session('errors.unitId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.unitId')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="provider" class="form-label">Service Provider *</label>
                <input type="text" id="provider" name="provider" class="form-control <?= session('errors.provider') ? 'is-invalid' : '' ?>" placeholder="Provider" required
                  value="<?= esc(old('provider', $ambulance->provider ?? '')) ?>">
                <div class="form-note mt-1 text-muted admin-form-note">e.g. Kenya Red Cross, E-Plus, St John Ambulance</div>
                <?php if (session('errors.provider')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.provider')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Section: Assignment & Registration -->
          <h5 class="mono-label text-muted mt-4 mb-3 border-bottom border-secondary border-opacity-10 pb-2">Assignment & Registration</h5>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="ems_provider_id" class="form-label">EMS Provider</label>
                <select id="ems_provider_id" name="ems_provider_id" class="form-select admin-form-select <?= session('errors.ems_provider_id') ? 'is-invalid' : '' ?>">
                  <option value="">— None —</option>
                  <?php if (!empty($ems_providers)) : ?>
                    <?php foreach ($ems_providers as $provider) : ?>
                      <option value="<?= (int) $provider['id'] ?>" <?= ((old('ems_provider_id', $ambulance->ems_provider_id ?? '')) == $provider['id']) ? 'selected' : '' ?>>
                        <?= esc($provider['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <?php if (session('errors.ems_provider_id')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.ems_provider_id')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="registration" class="form-label">Registration Plate</label>
                <input type="text" id="registration" name="registration" class="form-control <?= session('errors.registration') ? 'is-invalid' : '' ?>" placeholder="e.g. KBY 104A"
                  value="<?= esc(old('registration', $ambulance->registration ?? '')) ?>">
                <?php if (session('errors.registration')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.registration')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="status" class="form-label">Current Status</label>
                <select id="status" name="status" class="form-select admin-form-select <?= session('errors.status') ? 'is-invalid' : '' ?>">
                  <?php
                  $statuses = ['Available', 'Transporting', 'On Scene', 'Queued', 'Off Duty'];
                  $currentStatus = old('status', $ambulance->status ?? 'Available');
                  foreach ($statuses as $statusOption) :
                  ?>
                    <option value="<?= esc($statusOption) ?>" <?= $currentStatus === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (session('errors.status')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Section: GPS Coordinates (Optional) -->
          <h5 class="mono-label text-muted mt-4 mb-3 border-bottom border-secondary border-opacity-10 pb-2">GPS Coordinates <span class="text-muted">(Optional — auto-updated by device)</span></h5>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="current_lat" class="form-label">Current Latitude</label>
                <input type="text" id="current_lat" name="current_lat" class="form-control <?= session('errors.current_lat') ? 'is-invalid' : '' ?>" placeholder="-1.2921"
                  value="<?= esc(old('current_lat', $ambulance->current_lat ?? '')) ?>">
                <?php if (session('errors.current_lat')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.current_lat')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="current_lng" class="form-label">Current Longitude</label>
                <input type="text" id="current_lng" name="current_lng" class="form-control <?= session('errors.current_lng') ? 'is-invalid' : '' ?>" placeholder="36.8219"
                  value="<?= esc(old('current_lng', $ambulance->current_lng ?? '')) ?>">
                <?php if (session('errors.current_lng')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.current_lng')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary admin-btn-submit" style="min-height: 48px;">
              <?= $isEdit ? 'Save Changes' : 'Register Vehicle' ?>
            </button>
            <a href="<?= url_to('admin.ambulances.list') ?>" class="btn btn-outline-secondary admin-btn-submit" style="min-height: 48px;">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>