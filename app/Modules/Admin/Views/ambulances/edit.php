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

<div class="container py-5 mt-5">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.ambulances.list') ?>" class="mono-label text-decoration-none" style="color: var(--color-brand-primary);">
      ← Back to Fleet List
    </a>
  </div>

  <!-- Header -->
  <div class="mb-4 reveal">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Vehicle' : 'Register Vehicle' ?></span>
    </div>
    <h1 class="s-title mb-0">
      <?= $isEdit ? 'Edit Ambulance <span class="fst-italic text-secondary">Registry</span>' : 'Register New <span class="fst-italic text-secondary">Ambulance</span>' ?>
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

        <form action="<?= $isEdit ? url_to('admin.ambulances.update', $ambulance->id) : url_to('admin.ambulances.create') ?>" method="POST">
          <?= csrf_field() ?>

          <!-- Required Fields -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <input type="text" id="unitId" name="unitId" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.unitId') ? 'is-invalid' : '' ?>" placeholder="Unit ID" required
                  value="<?= esc(old('unitId', $ambulance->unit_id ?? '')) ?>" style="text-transform: uppercase;">
                <label for="unitId">Ambulance Unit ID *</label>
                <div class="small font-monospace text-secondary mt-1">e.g. KRC-401, E-Plus 22, St John 05</div>
                <?php if (session('errors.unitId')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.unitId')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <input type="text" id="provider" name="provider" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.provider') ? 'is-invalid' : '' ?>" placeholder="Provider" required
                  value="<?= esc(old('provider', $ambulance->provider ?? '')) ?>">
                <label for="provider">Service Provider *</label>
                <div class="small font-monospace text-secondary mt-1">e.g. Kenya Red Cross, E-Plus, St John Ambulance</div>
                <?php if (session('errors.provider')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.provider')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Section: Assignment & Registration (collapsible optional) -->
          <details class="mb-3">
            <summary class="mono-label text-secondary border-bottom pb-2 mb-3" style="cursor: pointer;">Assignment & Registration</summary>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <select id="ems_provider_id" name="ems_provider_id" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.ems_provider_id') ? 'is-invalid' : '' ?>">
                    <option value="">— None —</option>
                    <?php if (!empty($ems_providers)) : ?>
                      <?php foreach ($ems_providers as $provider) : ?>
                        <option value="<?= (int) $provider['id'] ?>" <?= ((old('ems_provider_id', $ambulance->ems_provider_id ?? '')) == $provider['id']) ? 'selected' : '' ?>>
                          <?= esc($provider['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <label for="ems_provider_id">EMS Provider</label>
                  <?php if (session('errors.ems_provider_id')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.ems_provider_id')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" id="registration" name="registration" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.registration') ? 'is-invalid' : '' ?>" placeholder="Registration Plate"
                    value="<?= esc(old('registration', $ambulance->registration ?? '')) ?>">
                  <label for="registration">Registration Plate</label>
                  <?php if (session('errors.registration')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.registration')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <select id="status" name="status" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.status') ? 'is-invalid' : '' ?>">
                    <?php
                    $statuses = ['Available', 'Transporting', 'On Scene', 'Queued', 'Off Duty'];
                    $currentStatus = old('status', $ambulance->status ?? 'Available');
                    foreach ($statuses as $statusOption) :
                    ?>
                      <option value="<?= esc($statusOption) ?>" <?= $currentStatus === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label for="status">Current Status</label>
                  <?php if (session('errors.status')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </details>

          <!-- Section: GPS Coordinates (Optional, collapsible) -->
          <details class="mb-3">
            <summary class="mono-label text-secondary border-bottom pb-2 mb-3" style="cursor: pointer;">GPS Coordinates (Optional — auto-updated by device)</summary>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" id="current_lat" name="current_lat" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.current_lat') ? 'is-invalid' : '' ?>" placeholder="Latitude"
                    value="<?= esc(old('current_lat', $ambulance->current_lat ?? '')) ?>">
                  <label for="current_lat">Current Latitude</label>
                  <?php if (session('errors.current_lat')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.current_lat')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" id="current_lng" name="current_lng" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.current_lng') ? 'is-invalid' : '' ?>" placeholder="Longitude"
                    value="<?= esc(old('current_lng', $ambulance->current_lng ?? '')) ?>">
                  <label for="current_lng">Current Longitude</label>
                  <?php if (session('errors.current_lng')) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors.current_lng')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </details>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary touch-target">
              <?= $isEdit ? 'Save Changes' : 'Register Vehicle' ?>
            </button>
            <a href="<?= url_to('admin.ambulances.list') ?>" class="btn btn-outline-secondary touch-target">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>