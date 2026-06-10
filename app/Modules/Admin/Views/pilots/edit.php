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
          <div class="alert alert-danger mb-4 p-3" role="alert">
            <h5 class="alert-heading fw-bold mb-2 text-danger">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3">
              <?php foreach (session()->get('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? url_to('admin.pilots.update', $pilot->id) : url_to('admin.pilots.create') ?>" method="POST" class="form-dark">
          <?= csrf_field() ?>

          <!-- Required Fields -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <input type="text" id="fullName" name="fullName" class="form-control <?= session('errors.fullName') ? 'is-invalid' : '' ?>" placeholder="Full Name" required
                  value="<?= esc(old('fullName', $pilot->full_name ?? '')) ?>">
                <label for="fullName">Full Name *</label>
                <?php if (session('errors.fullName')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.fullName')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <input type="email" id="emailAddress" name="emailAddress" class="form-control <?= session('errors.emailAddress') ? 'is-invalid' : '' ?>" placeholder="Email Address" required
                  value="<?= esc(old('emailAddress', $pilot->email_address ?? '')) ?>">
                <label for="emailAddress">Email Address *</label>
                <?php if (session('errors.emailAddress')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.emailAddress')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-floating">
              <input type="text" id="organisation" name="organisation" class="form-control <?= session('errors.organisation') ? 'is-invalid' : '' ?>" placeholder="Organisation" required
                value="<?= esc(old('organisation', $pilot->organisation ?? '')) ?>">
              <label for="organisation">Organisation / Hospital / EMS *</label>
              <?php if (session('errors.organisation')) : ?>
                <div class="invalid-feedback"><?= esc(session('errors.organisation')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Role + Phone -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <select id="userRole" name="userRole" class="form-select <?= session('errors.userRole') ? 'is-invalid' : '' ?>" required>
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
                  foreach ($roles as $roleOption) :
                  ?>
                    <option value="<?= esc($roleOption) ?>" <?= $currentRole === $roleOption ? 'selected' : '' ?>><?= esc($roleOption) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="userRole">Your Role *</label>
                <?php if (session('errors.userRole')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.userRole')) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-floating">
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control <?= session('errors.phoneNumber') ? 'is-invalid' : '' ?>" placeholder="Phone Number"
                  value="<?= esc(old('phoneNumber', $pilot->phone_number ?? '')) ?>">
                <label for="phoneNumber">Phone Number (optional)</label>
                <?php if (session('errors.phoneNumber')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.phoneNumber')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Optional: Message (collapsible) -->
          <details class="mb-3">
            <summary class="mono-label text-muted border-bottom border-secondary border-opacity-10 pb-2 mb-3" style="cursor: pointer;">Message / Note (Optional)</summary>
            <div class="mb-3">
              <div class="form-floating">
                <textarea id="message" name="message" class="form-control <?= session('errors.message') ? 'is-invalid' : '' ?>" placeholder="Message" style="height: 120px;"><?= esc(old('message', $pilot->message ?? '')) ?></textarea>
                <label for="message">Message</label>
                <?php if (session('errors.message')) : ?>
                  <div class="invalid-feedback"><?= esc(session('errors.message')) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </details>

          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary" style="min-height: 48px;">
              <?= $isEdit ? 'Save Changes' : 'Create Application' ?>
            </button>
            <a href="<?= url_to('admin.pilots.list') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>