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

<div class="container admin-page">
  <!-- Blueprint Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center blueprint-header reveal mb-4">
    <div>
      <div class="s-label">
        <div class="s-label-line"></div>
        <span class="s-label-text">Admin Panel</span>
      </div>
      <h1 class="s-title m-0 mb-2">User Accounts</h1>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url_to('admin.dashboard') ?>" class="btn btn-outline-secondary">Dashboard</a>
      <a href="<?= url_to('admin.users.new') ?>" class="btn btn-primary">Add User Account +</a>
    </div>
  </div>

  <!-- User Accounts Table -->
  <div class="card blueprint-card p-4 reveal">
    <div class="table-responsive">
      <table class="table queue-table align-middle">
        <thead>
          <tr class="mono-label text-muted">
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
              <td colspan="6" class="text-center text-muted py-4">No user accounts registered.</td>
            </tr>
          <?php else : ?>
            <?php foreach ($users as $u) : ?>
              <tr>
                <td class="fw-bold text-cream"><?= esc($u->name) ?></td>
                <td><?= esc($u->email) ?></td>
                <td><span class="badge bg-secondary"><?= esc($u->role) ?></span></td>
                <td>
                  <?php if ($u->role === 'nurse' || $u->role === 'hospital_admin') : ?>
                    <span class="text-muted small">Hospital:</span> <?= esc($u->hospital_name ?? 'Unassigned') ?>
                  <?php elseif ($u->role === 'paramedic') : ?>
                    <span class="text-muted small">EMS:</span> <?= esc($u->ems_name ?? 'Unassigned') ?>
                  <?php else : ?>
                    <span class="text-muted small">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $u->active === 1 ? 'bg-success' : 'bg-danger' ?>">
                    <?= $u->active === 1 ? 'Active' : 'Suspended' ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= url_to('admin.users.edit', $u->id) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <a href="<?= url_to('admin.users.delete', $u->id) ?>"
                      class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Are you sure you want to deactivate/delete this user?');">Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pager) : ?>
      <div class="mt-4 d-flex justify-content-center admin-pager">
        <?= $pager->links('users', 'default_full') ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>