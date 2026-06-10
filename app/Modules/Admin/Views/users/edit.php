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

        <!-- Flash validation errors -->
        <?php if (session()->has('errors') && is_array(session('errors'))) : ?>
          <div class="alert alert-danger mb-4 p-3" role="alert">
            <h5 class="alert-heading fw-bold mb-2 text-danger">Please correct the following errors:</h5>
            <ul class="mb-0 ps-3">
              <?php foreach (session('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? url_to('admin.users.update', $user->id) : url_to('admin.users.create') ?>" method="POST" class="form-dark" novalidate>
          <?= csrf_field() ?>

          <!-- Name -->
          <div class="mb-3">
            <div class="form-floating">
              <input type="text" name="name" id="nameInput" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" placeholder="Full Name"
                value="<?= (string) esc($isEdit ? $user->name : old('name')) ?>" required>
              <label for="nameInput">Full Name *</label>
              <?php if (session('errors.name')) : ?>
                <div class="invalid-feedback"><?= (string) esc(session('errors.name')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <div class="form-floating">
              <input type="email" name="email" id="emailInput" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" placeholder="Email Address"
                value="<?= (string) esc($isEdit ? $user->email : old('email')) ?>" required>
              <label for="emailInput">Email Address *</label>
              <?php if (session('errors.email')) : ?>
                <div class="invalid-feedback"><?= (string) esc(session('errors.email')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Role -->
          <div class="mb-3">
            <div class="form-floating">
              <select name="role" id="roleSelect" class="form-select <?= session('errors.role') ? 'is-invalid' : '' ?>" required>
                <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Account Role</option>
                <option value="nurse" <?= ($isEdit && $user->role === 'nurse') || old('role') === 'nurse' ? 'selected' : '' ?>>ED Nurse</option>
                <option value="hospital_admin" <?= ($isEdit && $user->role === 'hospital_admin') || old('role') === 'hospital_admin' ? 'selected' : '' ?>>Hospital Administrator</option>
                <option value="paramedic" <?= ($isEdit && $user->role === 'paramedic') || old('role') === 'paramedic' ? 'selected' : '' ?>>Paramedic / EMT</option>
                <option value="dispatcher" <?= ($isEdit && $user->role === 'dispatcher') || old('role') === 'dispatcher' ? 'selected' : '' ?>>EMS Dispatcher</option>
                <option value="admin" <?= ($isEdit && $user->role === 'admin') || old('role') === 'admin' ? 'selected' : '' ?>>System Admin</option>
              </select>
              <label for="roleSelect">Account Authorization Role *</label>
              <?php if (session('errors.role')) : ?>
                <div class="invalid-feedback"><?= (string) esc(session('errors.role')) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Hospital mapping (visible only for nurse / hospital_admin) -->
          <div class="mb-3 d-none" id="hospitalGroup">
            <div class="form-floating">
              <select name="hospital_id" id="hospitalSelect" class="form-select">
                <option value="" selected>Unassigned / None</option>
                <?php foreach ($hospitals as $h) : ?>
                  <option value="<?= $h->id ?>" <?= ($isEdit && $user->hospital_id === $h->id) ? 'selected' : '' ?>><?= esc($h->name) ?></option>
                <?php endforeach; ?>
              </select>
              <label for="hospitalSelect">Map to Hospital Facility</label>
            </div>
          </div>

          <!-- EMS Provider mapping (visible only for paramedic) -->
          <div class="mb-3 d-none" id="emsGroup">
            <div class="form-floating">
              <select name="ems_provider_id" id="emsSelect" class="form-select">
                <option value="" selected>Unassigned / None</option>
                <?php foreach ($ems_providers as $prov) : ?>
                  <option value="<?= $prov['id'] ?>" <?= ($isEdit && $user->ems_provider_id === (int)$prov['id']) ? 'selected' : '' ?>><?= esc($prov['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label for="emsSelect">Map to EMS Service Provider</label>
            </div>
          </div>

          <!-- Ambulance assignment (visible only for paramedic) -->
          <div class="mb-3 d-none" id="ambulanceGroup">
            <div class="form-floating">
              <select name="ambulance_id" id="ambulanceSelect" class="form-select">
                <option value="" selected>Unassigned / None</option>
                <?php foreach ($ambulances as $amb) : ?>
                  <?php
                    $is_selected = $isEdit && $user->ambulance_id !== null && (int)$user->ambulance_id === (int)$amb['id'];
                    $is_taken = !empty($amb['assigned_to_id']) && (!$isEdit || (int)$amb['assigned_to_id'] !== (int)$user->id);
                    $label = esc($amb['unit_id']) . ' — ' . esc($amb['provider']);
                    if (!empty($amb['registration'])) {
                        $label .= ' (' . esc($amb['registration']) . ')';
                    }
                    if ($is_taken) {
                        $label .= ' ⚠ Assigned to ' . esc($amb['assigned_to_name']);
                    }
                  ?>
                  <option value="<?= $amb['id'] ?>" <?= $is_selected ? 'selected' : '' ?> <?= $is_taken ? 'disabled' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
              <label for="ambulanceSelect">Assign to Ambulance Unit</label>
              <?php if (session('errors.ambulance_id')) : ?>
                <div class="text-danger small mt-1"><?= (string) esc(session('errors.ambulance_id')) ?></div>
              <?php endif; ?>
            </div>
            <div class="form-text text-muted">Each ambulance can only be assigned to one active paramedic at a time.</div>
          </div>

          <!-- Active Status -->
          <div class="mb-3">
            <div class="form-floating">
              <select name="active" id="activeSelect" class="form-select" required>
                <option value="1" <?= ($isEdit && $user->active === 1) || !isset($user) ? 'selected' : '' ?>>Active / Enabled</option>
                <option value="0" <?= ($isEdit && $user->active === 0) ? 'selected' : '' ?>>Suspended / Disabled</option>
              </select>
              <label for="activeSelect">Account Status</label>
            </div>
          </div>

          <!-- Optional Custom Password (Edit only) — collapsible -->
          <?php if ($isEdit) : ?>
            <details class="mb-3" open>
              <summary class="mono-label text-muted border-bottom border-secondary border-opacity-10 pb-2 mb-3" style="cursor: pointer;">Set New Password (Optional)</summary>
              <div class="mb-3">
                <div class="input-group">
                  <div class="form-floating flex-grow-1">
                    <input type="password" name="new_password" id="newPasswordInput" class="form-control <?= session('errors.new_password') ? 'is-invalid' : '' ?>" placeholder="Password" autocomplete="new-password">
                    <label for="newPasswordInput">New Password</label>
                  </div>
                  <button type="button" class="btn btn-outline-secondary" id="generatePassBtn" title="Generate secure password" style="min-height: 58px; min-width: 48px;">🔑</button>
                  <button type="button" class="btn btn-outline-secondary" id="togglePassBtn" title="Show/Hide password" style="min-height: 58px; min-width: 48px;">👁️</button>
                </div>
                <?php if (session('errors.new_password')) : ?>
                  <div class="text-danger small mt-1"><?= (string) esc(session('errors.new_password')) ?></div>
                <?php endif; ?>
                <div class="form-text text-muted">Min 6 characters. Leave blank to keep the current password unchanged.</div>
              </div>
            </details>

            <script>
              document.addEventListener('DOMContentLoaded', () => {
                const passInput = document.getElementById('newPasswordInput');
                const genBtn = document.getElementById('generatePassBtn');
                const toggleBtn = document.getElementById('togglePassBtn');

                genBtn.addEventListener('click', () => {
                  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
                  let password = '';
                  for (let i = 0; i < 12; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                  }
                  passInput.value = password;
                });

                toggleBtn.addEventListener('click', () => {
                  const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                  passInput.setAttribute('type', type);
                });
              });
            </script>
          <?php endif; ?>

          <!-- Actions -->
          <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary flex-fill" style="min-height: 48px;">
              <?= $isEdit ? 'Save Changes' : 'Register Account' ?>
            </button>
            <a href="<?= url_to('admin.users.list') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">
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
    const ambulanceGroup = document.getElementById('ambulanceGroup');

    const toggleOrgFields = () => {
      const val = roleSelect.value;
      if (val === 'nurse' || val === 'hospital_admin') {
        hospitalGroup.classList.remove('d-none');
        emsGroup.classList.add('d-none');
        ambulanceGroup.classList.add('d-none');
      } else if (val === 'paramedic') {
        emsGroup.classList.remove('d-none');
        ambulanceGroup.classList.remove('d-none');
        hospitalGroup.classList.add('d-none');
      } else {
        hospitalGroup.classList.add('d-none');
        emsGroup.classList.add('d-none');
        ambulanceGroup.classList.add('d-none');
      }
    };

    roleSelect.addEventListener('change', toggleOrgFields);
    toggleOrgFields();
  });
</script>

<?= $this->endSection() ?>