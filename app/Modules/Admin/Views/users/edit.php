<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $hospitals
 * @var array $ems_providers
 * @var \App\Modules\Auth\Entities\User|null $user
 */
$isEdit = isset($user);
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Inner Back navigation -->
  <div class="mb-4 reveal">
    <a href="<?= url_to('admin.users.list') ?>" class="mono-label text-decoration-none admin-back">← Back to Users</a>
  </div>

  <!-- Blueprint Header -->
  <div class="blueprint-header reveal mb-4">
    <div class="s-label">
      <div class="s-label-line"></div>
      <span class="s-label-text">User Registry</span>
    </div>
    <h1 class="s-title"><?= $isEdit ? 'Edit Account' : 'Register Account' ?></h1>
  </div>

  <div class="row justify-content-start">
    <div class="col-lg-8">
      <div class="card blueprint-card p-4 p-md-5 reveal">

        <form action="<?= $isEdit ? url_to('admin.users.update', $user->id) : url_to('admin.users.create') ?>" method="POST" class="form-dark" novalidate>
          <?= csrf_field() ?>

          <!-- Name Input -->
          <div class="mb-3">
            <label for="nameInput" class="form-label">Full Name *</label>
            <input type="text"
              name="name"
              id="nameInput"
              class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
              placeholder="Name"
              value="<?= (string) esc($isEdit ? $user->name : old('name')) ?>"
              required>
            <?php if (session('errors.name')) : ?>
              <div class="invalid-feedback"><?= (string) esc(session('errors.name')) ?></div>
            <?php endif; ?>
          </div>

          <!-- Email Input -->
          <div class="mb-3">
            <label for="emailInput" class="form-label">Email Address *</label>
            <input type="email"
              name="email"
              id="emailInput"
              class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
              placeholder="Email"
              value="<?= (string) esc($isEdit ? $user->email : old('email')) ?>"
              required>
            <?php if (session('errors.email')) : ?>
              <div class="invalid-feedback"><?= (string) esc(session('errors.email')) ?></div>
            <?php endif; ?>
          </div>

          <!-- Role Select -->
          <div class="mb-3">
            <label for="roleSelect" class="form-label">Account Authorization Role *</label>
            <select name="role" id="roleSelect" class="form-select <?= session('errors.role') ? 'is-invalid' : '' ?>" required>
              <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Account Role</option>
              <option value="nurse" <?= ($isEdit && $user->role === 'nurse') || old('role') === 'nurse' ? 'selected' : '' ?>>ED Nurse</option>
              <option value="hospital_admin" <?= ($isEdit && $user->role === 'hospital_admin') || old('role') === 'hospital_admin' ? 'selected' : '' ?>>Hospital Administrator</option>
              <option value="paramedic" <?= ($isEdit && $user->role === 'paramedic') || old('role') === 'paramedic' ? 'selected' : '' ?>>Paramedic / EMT</option>
              <option value="dispatcher" <?= ($isEdit && $user->role === 'dispatcher') || old('role') === 'dispatcher' ? 'selected' : '' ?>>EMS Dispatcher</option>
              <option value="admin" <?= ($isEdit && $user->role === 'admin') || old('role') === 'admin' ? 'selected' : '' ?>>System Admin</option>
            </select>
            <?php if (session('errors.role')) : ?>
              <div class="invalid-feedback"><?= (string) esc(session('errors.role')) ?></div>
            <?php endif; ?>
          </div>

          <!-- Hospital mapping select (visible only for nurse / hospital_admin) -->
          <div class="mb-3 d-none" id="hospitalGroup">
            <label for="hospitalSelect" class="form-label">Map to Hospital Facility</label>
            <select name="hospital_id" id="hospitalSelect" class="form-select">
              <option value="" selected>Unassigned / None</option>
              <?php foreach ($hospitals as $h) : ?>
                <option value="<?= $h->id ?>" <?= ($isEdit && $user->hospital_id === $h->id) ? 'selected' : '' ?>><?= esc($h->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- EMS Provider mapping select (visible only for paramedic) -->
          <div class="mb-3 d-none" id="emsGroup">
            <label for="emsSelect" class="form-label">Map to EMS Service Provider</label>
            <select name="ems_provider_id" id="emsSelect" class="form-select">
              <option value="" selected>Unassigned / None</option>
              <?php foreach ($ems_providers as $prov) : ?>
                <option value="<?= $prov['id'] ?>" <?= ($isEdit && $user->ems_provider_id === (int)$prov['id']) ? 'selected' : '' ?>><?= esc($prov['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Active Status Select -->
          <div class="mb-4">
            <label for="activeSelect" class="form-label">Account Status</label>
            <select name="active" id="activeSelect" class="form-select" required>
              <option value="1" <?= ($isEdit && $user->active === 1) || !isset($user) ? 'selected' : '' ?>>Active / Enabled</option>
              <option value="0" <?= ($isEdit && $user->active === 0) ? 'selected' : '' ?>>Suspended / Disabled</option>
            </select>
          </div>

          <!-- Optional Password Reset (Edit only) -->
          <?php if ($isEdit) : ?>
            <div class="form-check mb-4 text-start">
              <input class="form-check-input" type="checkbox" name="reset_password" value="1" id="resetPassCheck">
              <label class="form-check-label text-muted" for="resetPassCheck">
                Reset password to default temporary "12345678"
              </label>
            </div>
          <?php endif; ?>

          <!-- Actions -->
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-3 fw-bold fs-6">
              <?= $isEdit ? 'Save Changes' : 'Register Account' ?>
            </button>
            <a href="<?= url_to('admin.users.list') ?>" class="btn btn-outline-secondary py-3 fw-bold fs-6">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('roleSelect');
    const hospitalGroup = document.getElementById('hospitalGroup');
    const emsGroup = document.getElementById('emsGroup');

    const toggleOrgFields = () => {
      const val = roleSelect.value;
      if (val === 'nurse' || val === 'hospital_admin') {
        hospitalGroup.classList.remove('d-none');
        emsGroup.classList.add('d-none');
      } else if (val === 'paramedic') {
        emsGroup.classList.remove('d-none');
        hospitalGroup.classList.add('d-none');
      } else {
        hospitalGroup.classList.add('d-none');
        emsGroup.classList.add('d-none');
      }
    };

    roleSelect.addEventListener('change', toggleOrgFields);
    toggleOrgFields(); // Initial run on load
  });
</script>

<?= $this->endSection() ?>