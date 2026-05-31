<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Pilot\Entities\PilotSignup|null $pilot
 */
$isEdit = isset($pilot) && $pilot->id;
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.pilots.list') ?>" class="mono-label text-decoration-none admin-back">
      ← Back to Pilot Registry
    </a>
  </div>

  <!-- Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label mb-1">
      <div class="s-label-line"></div>
      <span class="s-label-text"><?= $isEdit ? 'Update Entry' : 'Manual Entry' ?></span>
    </div>
    <h1 class="s-title mb-2 admin-heading">
      <?= $isEdit ? 'Edit Pilot <span class="ital dim">Signup</span>' : 'Add New <span class="ital dim">Signup</span>' ?>
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

        <form action="<?= $isEdit ? url_to('admin.pilots.update', $pilot->id) : url_to('admin.pilots.create') ?>" method="POST" class="form-dark">
          <?= csrf_field() ?>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="fullName" class="form-label">Full Name *</label>
                <input type="text" id="fullName" name="fullName" class="form-control <?= session('errors.fullName') ? 'is-invalid' : '' ?>" placeholder="Full Name" required
                  value="<?= esc(old('fullName', $pilot->full_name ?? '')) ?>">
                <?php if (session('errors.fullName')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.fullName')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="emailAddress" class="form-label">Email Address *</label>
                <input type="email" id="emailAddress" name="emailAddress" class="form-control <?= session('errors.emailAddress') ? 'is-invalid' : '' ?>" placeholder="Email Address" required
                  value="<?= esc(old('emailAddress', $pilot->email_address ?? '')) ?>">
                <?php if (session('errors.emailAddress')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.emailAddress')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <div>
              <label for="organisation" class="form-label">Organisation / Hospital / EMS *</label>
              <input type="text" id="organisation" name="organisation" class="form-control <?= session('errors.organisation') ? 'is-invalid' : '' ?>" placeholder="Organisation" required
                value="<?= esc(old('organisation', $pilot->organisation ?? '')) ?>">
              <?php if (session('errors.organisation')) : ?>
                <div class="invalid-feedback"><?= esc(session('errors.organisation')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <div>
                <label for="userRole" class="form-label">Your Role *</label>
                <select id="userRole" name="userRole" class="form-select admin-form-select <?= session('errors.userRole') ? 'is-invalid' : '' ?>" required>
                  <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Role</option>
                  <?php
                  $roles = [
                    'Hospital Administrator',
                    'ED Manager / Charge Nurse',
                    'Emergency Physician',
                    'Paramedic / EMT',
                    'EMS Dispatcher / Operations Manager',
                    'Investor / Funder',
                    'Researcher / Academic',
                    'Other'
                  ];
                  $currentRole = old('userRole', $pilot->user_role ?? '');
                  foreach ($roles as $role) :
                  ?>
                    <option value="<?= esc($role) ?>" <?= $currentRole === $role ? 'selected' : '' ?>><?= esc($role) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (session('errors.userRole')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.userRole')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div>
                <label for="phoneNumber" class="form-label">Phone Number (optional)</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control <?= session('errors.phoneNumber') ? 'is-invalid' : '' ?>" placeholder="Phone Number"
                  value="<?= esc(old('phoneNumber', $pilot->phone_number ?? '')) ?>">
                <?php if (session('errors.phoneNumber')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.phoneNumber')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <div>
              <label for="message" class="form-label">Message / Note (optional)</label>
              <textarea id="message" name="message" class="form-control <?= session('errors.message') ? 'is-invalid' : '' ?>" placeholder="Message" style="height: 120px;"><?= esc(old('message', $pilot->message ?? '')) ?></textarea>
              <?php if (session('errors.message')) : ?>
                <div class="invalid-feedback"><?= esc(session('errors.message')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary admin-btn-submit">
              <?= $isEdit ? 'Save Changes' : 'Create Application' ?>
            </button>
            <a href="<?= url_to('admin.pilots.list') ?>" class="btn btn-outline-secondary admin-btn-submit">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>