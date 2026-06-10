<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $pilots
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.dashboard') ?>" class="mono-label text-decoration-none admin-back">
      ← Back to Dashboard
    </a>
  </div>

  <!-- Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center blueprint-header reveal mb-4">
    <div>
      <div class="s-label mb-1">
        <div class="s-label-line"></div>
        <span class="s-label-text">Pilot Signups Registry</span>
      </div>
      <h1 class="s-title mb-2 admin-heading">
        Manage <span class="ital dim">Applications</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.pilots.new') ?>" class="btn btn-primary admin-btn-submit" style="min-height: 48px;">
        Add Manual Entry +
      </a>
    </div>
  </div>

  <!-- Responsive Table -->
  <div class="card blueprint-card p-4">
    <?php if (empty($pilots)) : ?>
      <p class="text-center my-4 text-muted">No pilot signup records registered in the system.</p>
    <?php else : ?>
      <div class="table-responsive">
        <table class="table queue-table align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Organisation</th>
              <th>Role</th>
              <th>Phone</th>
              <th>Date</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pilots as $pilot) : ?>
              <tr>
                <td class="td-code"><?= esc($pilot->id) ?></td>
                <td class="td-name"><?= esc($pilot->full_name) ?></td>
                <td><?= esc($pilot->email_address) ?></td>
                <td><?= esc($pilot->organisation) ?></td>
                <td class="td-mono-sm"><?= esc($pilot->user_role) ?></td>
                <td class="td-mono"><?= esc($pilot->phone_number ?? '—') ?></td>
                <td class="td-mono-sm">
                  <?= esc($pilot->created_at ? $pilot->created_at->format('Y-m-d H:i') : '—') ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= url_to('admin.pilots.edit', $pilot->id) ?>" class="btn btn-outline-secondary btn-sm px-3 py-2 d-inline-block admin-btn-edit" style="min-height: 48px; min-width: 48px;">
                      Edit
                    </a>
                    <a href="<?= url_to('admin.pilots.delete', $pilot->id) ?>"
                      class="btn btn-danger btn-sm px-3 py-2 d-inline-block admin-btn-delete"
                      style="min-height: 48px; min-width: 48px;"
                      onclick="return confirm('Are you sure you want to delete this pilot application?');">
                      Delete
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($pager) : ?>
        <div class="mt-4 d-flex justify-content-center admin-pager">
          <?= $pager->links('pilots', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>