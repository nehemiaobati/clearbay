<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var \App\Modules\Queue\Entities\Hospital|null $hospital
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

          <div class="row">
            <div class="col-md-4 mb-4">
              <div>
                <label for="code" class="form-label">Facility Code *</label>
                <input type="text" id="code" name="code" class="form-control <?= session('errors.code') ? 'is-invalid' : '' ?>" placeholder="e.g. KNH · Level 6" required
                  value="<?= esc(old('code', $hospital->code ?? '')) ?>">
                <div class="form-note mt-1 text-muted admin-form-note">e.g. KNH · Level 6</div>
                <?php if (session('errors.code')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.code')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-8 mb-4">
              <div>
                <label for="name" class="form-label">Hospital / Facility Name *</label>
                <input type="text" id="name" name="name" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" placeholder="Hospital Name" required
                  value="<?= esc(old('name', $hospital->name ?? '')) ?>">
                <?php if (session('errors.name')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.name')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="category" class="form-label">Category / Classification *</label>
                <input type="text" id="category" name="category" class="form-control <?= session('errors.category') ? 'is-invalid' : '' ?>" placeholder="Category" required
                  value="<?= esc(old('category', $hospital->category ?? '')) ?>">
                <div class="form-note mt-1 text-muted admin-form-note">e.g. National Referral · Public</div>
                <?php if (session('errors.category')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.category')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="status" class="form-label">Off-Load Capacity Status *</label>
                <select id="status" name="status" class="form-select admin-form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status Level</option>
                  <?php
                  $statuses = ['Green', 'Amber', 'Red', 'Recruiting'];
                  $currentStatus = old('status', $hospital->status ?? '');
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
              <?= $isEdit ? 'Save Changes' : 'Register Facility' ?>
            </button>
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary admin-btn-submit">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>