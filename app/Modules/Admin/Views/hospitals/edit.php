<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Queue\Entities\Hospital|null $hospital
 */
$isEdit = isset($hospital) && $hospital->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container" style="margin-top: 120px; margin-bottom: 80px;">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.hospitals.list') ?>" class="mono-label text-decoration-none" style="color: var(--sage-ll) !important;">
      ← Back to Hospitals List
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Facility' : 'New Facility' ?></span>
    </div>
    <h1 class="s-title mb-2" style="font-family: var(--serif); font-weight: 700; color: var(--cream); font-size: 2.2rem; line-height: 1.2;">
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
            <h5 class="alert-heading font-family-sans fw-bold mb-2 text-danger" style="font-size: 0.95rem;">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
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
              <div class="form-floating">
                <input type="text" id="code" name="code" class="form-control" placeholder="e.g. KNH · Level 6" required 
                       value="<?= esc(old('code', $hospital->code ?? '')) ?>">
                <label for="code">Facility Code *</label>
                <div class="form-note mt-1 text-muted" style="font-size: 0.72rem;">e.g. KNH · Level 6</div>
              </div>
            </div>
            <div class="col-md-8 mb-4">
              <div class="form-floating">
                <input type="text" id="name" name="name" class="form-control" placeholder="Hospital Name" required
                       value="<?= esc(old('name', $hospital->name ?? '')) ?>">
                <label for="name">Hospital / Facility Name *</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div class="form-floating">
                <input type="text" id="category" name="category" class="form-control" placeholder="Category" required
                       value="<?= esc(old('category', $hospital->category ?? '')) ?>">
                <label for="category">Category / Classification *</label>
                <div class="form-note mt-1 text-muted" style="font-size: 0.72rem;">e.g. National Referral · Public</div>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="form-floating">
                <select id="status" name="status" class="form-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Status Level</option>
                  <?php 
                  $statuses = ['Green', 'Amber', 'Red', 'Recruiting'];
                  $currentStatus = old('status', $hospital->status ?? '');
                  foreach ($statuses as $status) : 
                  ?>
                    <option value="<?= esc($status) ?>" <?= $currentStatus === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="status">Off-Load Capacity Status *</label>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary" style="font-size: 0.78rem !important; padding: 0.75rem 2rem !important;">
              <?= $isEdit ? 'Save Changes' : 'Register Facility' ?>
            </button>
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary" style="font-size: 0.78rem !important; padding: 0.75rem 2rem !important;">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
