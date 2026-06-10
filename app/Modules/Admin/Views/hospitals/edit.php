<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Hospital|null $hospital
 */
$isEdit = isset($hospital) && $hospital->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.hospitals.list') ?>" class="mono-label text-decoration-none admin-back">
      ← Back to Hospitals List
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Facility' : 'New Facility' ?></span>
    </div>
    <h1 class="s-title mb-2 admin-heading">
      <?= $isEdit ? 'Edit Hospital <span class="ital dim">Profile</span>' : 'Register New <span class="ital dim">Hospital</span>' ?>
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

        <form action="<?= $isEdit ? url_to('admin.hospitals.update', $hospital->id) : url_to('admin.hospitals.create') ?>" method="POST" class="form-dark">
          <?= csrf_field() ?>

          <!-- Required fields using floating labels -->
          <div class="row">
            <div class="col-md-4 mb-4">
              <div class="form-floating">
                <input type="text" id="code" name="code" class="form-control <?= session('errors.code') ? 'is-invalid' : '' ?>" placeholder="Facility Code" required
                  value="<?= esc(old('code', $hospital->code ?? '')) ?>">
                <label for="code">Facility Code *</label>
                <?php if (session('errors.code')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.code')) ?></div>
                <?php endif; ?>
              </div>
              <div class="form-note mt-1 text-muted admin-form-note">e.g. KNH · Level 6</div>
            </div>
            <div class="col-md-8 mb-4">
              <div class="form-floating">
                <input type="text" id="name" name="name" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" placeholder="Hospital Name" required
                  value="<?= esc(old('name', $hospital->name ?? '')) ?>">
                <label for="name">Hospital / Facility Name *</label>
                <?php if (session('errors.name')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.name')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div class="form-floating">
                <input type="text" id="category" name="category" class="form-control <?= session('errors.category') ? 'is-invalid' : '' ?>" placeholder="Category" required
                  value="<?= esc(old('category', $hospital->category ?? '')) ?>">
                <label for="category">Category / Classification *</label>
                <?php if (session('errors.category')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.category')) ?></div>
                <?php endif; ?>
              </div>
              <div class="form-note mt-1 text-muted admin-form-note">e.g. National Referral · Public</div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="form-floating">
                <select id="status" name="status" class="form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status</option>
                  <?php
                  $statuses = ['Green', 'Amber', 'Red', 'Recruiting'];
                  $currentStatus = old('status', $hospital->status ?? '');
                  foreach ($statuses as $statusOption) :
                  ?>
                    <option value="<?= esc($statusOption) ?>" <?= $currentStatus === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="status">Off-Load Capacity Status *</label>
                <?php if (session('errors.status')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Section: Capacity & Location -->
          <details class="mb-4">
            <summary class="mono-label text-muted py-2" style="cursor: pointer;">Capacity & Location <span class="text-dim">(click to expand)</span></summary>
            <div class="border-top border-secondary border-opacity-10 pt-3 mt-2">
              <div class="row">
                <div class="col-md-4 mb-4">
                  <div class="form-floating">
                    <input type="number" id="bays_available" name="bays_available" class="form-control <?= session('errors.bays_available') ? 'is-invalid' : '' ?>" min="0" placeholder="0"
                      value="<?= esc(old('bays_available', $hospital->bays_available ?? 0)) ?>">
                    <label for="bays_available">Available Bays</label>
                    <?php if (session('errors.bays_available')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.bays_available')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-4 mb-4">
                  <div class="form-floating">
                    <input type="number" id="baseline_avg" name="baseline_avg" class="form-control <?= session('errors.baseline_avg') ? 'is-invalid' : '' ?>" min="0" placeholder="60"
                      value="<?= esc(old('baseline_avg', $hospital->baseline_avg ?? 60)) ?>">
                    <label for="baseline_avg">Baseline Avg Wait (min)</label>
                    <?php if (session('errors.baseline_avg')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.baseline_avg')) ?></div>
                    <?php endif; ?>
                  </div>
                  <div class="form-note mt-1 text-muted admin-form-note">Target off-load time · default 60</div>
                </div>
                <div class="col-md-4 mb-4">
                  <div class="form-floating">
                    <input type="text" id="lat" name="lat" class="form-control <?= session('errors.lat') ? 'is-invalid' : '' ?>" placeholder="-1.2921"
                      value="<?= esc(old('lat', $hospital->lat ?? '')) ?>">
                    <label for="lat">Latitude</label>
                    <?php if (session('errors.lat')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.lat')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-4 mb-4">
                  <div class="form-floating">
                    <input type="text" id="lng" name="lng" class="form-control <?= session('errors.lng') ? 'is-invalid' : '' ?>" placeholder="36.8219"
                      value="<?= esc(old('lng', $hospital->lng ?? '')) ?>">
                    <label for="lng">Longitude</label>
                    <?php if (session('errors.lng')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.lng')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8 mb-4">
                  <div class="form-floating">
                    <input type="text" id="address" name="address" class="form-control <?= session('errors.address') ? 'is-invalid' : '' ?>" placeholder="Full address"
                      value="<?= esc(old('address', $hospital->address ?? '')) ?>">
                    <label for="address">Address</label>
                    <?php if (session('errors.address')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.address')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-4 mb-4">
                  <div class="form-floating">
                    <input type="text" id="contact_phone" name="contact_phone" class="form-control <?= session('errors.contact_phone') ? 'is-invalid' : '' ?>" placeholder="+254..."
                      value="<?= esc(old('contact_phone', $hospital->contact_phone ?? '')) ?>">
                    <label for="contact_phone">Contact Phone</label>
                    <?php if (session('errors.contact_phone')) : ?>
                      <div class="invalid-feedback"><?= esc(session('errors.contact_phone')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="active" name="active" value="1" <?= (old('active', $hospital->active ?? 1) == 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="active">Active Facility</label>
                </div>
              </div>
            </div>
          </details>

          <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary" style="min-height: 48px;">
              <?= $isEdit ? 'Save Changes' : 'Register Facility' ?>
            </button>
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>