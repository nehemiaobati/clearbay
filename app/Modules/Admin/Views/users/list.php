<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $users
 * @var \CodeIgniter\Pager\Pager|null $pager
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container py-5 mt-5">
  <!-- Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 reveal">
    <div>
      <div class="s-label">
        <div class="s-label-line"></div>
        <span class="s-label-text">Admin Panel</span>
      </div>
      <h1 class="s-title m-0 mb-2">User Accounts</h1>
    </div>
    <div class="d-flex gap-2 mt-3 mt-md-0">
      <a href="<?= url_to('admin.dashboard') ?>" class="btn btn-outline-secondary touch-target">Dashboard</a>
      <a href="<?= url_to('admin.users.new') ?>" class="btn btn-primary touch-target">Add User Account +</a>
    </div>
  </div>

  <!-- User Accounts with Mobile Card Fallback -->
  <div class="card border-secondary border-opacity-10 p-4 reveal" style="background: var(--color-bg-card);">

    <!-- Mobile Card List (<768px) -->
    <div class="d-md-none">
      <?php if (empty($users)) : ?>
        <p class="text-center my-4 text-secondary">No user accounts registered.</p>
      <?php else : ?>
        <?php foreach ($users as $u) : ?>
          <div class="list-card-item flex-column align-items-start gap-2 py-3">
            <div class="d-flex justify-content-between align-items-center w-100">
              <span class="fw-bold" style="color: var(--color-text-main);"><?= esc($u->name) ?></span>
              <span class="badge <?= $u->active === 1 ? 'bg-success' : 'bg-danger' ?> font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                <?= $u->active === 1 ? 'Active' : 'Suspended' ?>
              </span>
            </div>
            <div class="w-100">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-secondary small"><?= esc($u->email) ?></span>
                <span class="badge bg-secondary font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($u->role) ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <?php if ($u->role === 'nurse' || $u->role === 'hospital_admin') : ?>
                  <span class="mono-label small">Hospital: <?= esc($u->hospital_name ?? 'Unassigned') ?></span>
                <?php elseif ($u->role === 'paramedic') : ?>
                  <span class="mono-label small">EMS: <?= esc($u->ems_name ?? 'Unassigned') ?></span>
                <?php else : ?>
                  <span class="mono-label small">—</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="d-flex gap-2 w-100">
              <a href="<?= url_to('admin.users.edit', $u->id) ?>" class="btn btn-outline-secondary btn-sm flex-fill touch-target">Edit</a>
              <a href="<?= url_to('admin.users.delete', $u->id) ?>" class="btn btn-danger btn-sm flex-fill touch-target" onclick="return confirm('Are you sure you want to deactivate/delete this user?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Desktop Table (≥768px) -->
    <div class="d-none d-md-block">
      <div class="table-responsive">
        <table class="table align-middle" style="color: var(--color-text-main);">
          <thead>
            <tr class="mono-label text-secondary">
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Organization</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)) : ?>
              <tr>
                <td colspan="6" class="text-center text-secondary py-4">No user accounts registered.</td>
              </tr>
            <?php else : ?>
              <?php foreach ($users as $u) : ?>
                <tr>
                  <td class="fw-bold" style="color: var(--color-text-main);"><?= esc($u->name) ?></td>
                  <td><?= esc($u->email) ?></td>
                  <td><span class="badge bg-secondary font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($u->role) ?></span></td>
                  <td class="small text-secondary">
                    <?php if ($u->role === 'nurse' || $u->role === 'hospital_admin') : ?>
                      Hospital: <?= esc($u->hospital_name ?? 'Unassigned') ?>
                    <?php elseif ($u->role === 'paramedic') : ?>
                      EMS: <?= esc($u->ems_name ?? 'Unassigned') ?>
                    <?php else : ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= $u->active === 1 ? 'bg-success' : 'bg-danger' ?> font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                      <?= $u->active === 1 ? 'Active' : 'Suspended' ?>
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="d-flex gap-2 justify-content-end">
                      <a href="<?= url_to('admin.users.edit', $u->id) ?>" class="btn btn-sm btn-outline-secondary touch-target">Edit</a>
                      <a href="<?= url_to('admin.users.delete', $u->id) ?>" class="btn btn-sm btn-danger touch-target" onclick="return confirm('Are you sure you want to deactivate/delete this user?');">Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <?php if ($pager) : ?>
      <div class="mt-4 d-flex justify-content-center">
        <?= $pager->links('users', 'default_full') ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>