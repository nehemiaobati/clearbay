<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $hospitals
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
        <span class="s-label-text">Facilities Registry</span>
      </div>
      <h1 class="s-title mb-2 admin-heading">
        Manage <span class="ital dim">Hospitals</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.hospitals.new') ?>" class="btn btn-primary admin-btn-action">
        Register Hospital +
      </a>
    </div>
  </div>

  <!-- Responsive Table -->
  <div class="card blueprint-card p-4">
    <?php if (empty($hospitals)) : ?>
      <p class="text-center my-4 text-muted">No hospital facilities registered in the system.</p>
    <?php else : ?>
      <div class="table-responsive">
        <table class="table queue-table align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Name</th>
              <th>Category</th>
              <th>Status</th>
              <th>Registered At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hospitals as $hospital) : ?>
              <?php
              $statusClass = 'text-info';
              if ($hospital->status === 'Green') {
                  $statusClass = 'text-success';
              } elseif ($hospital->status === 'Amber') {
                  $statusClass = 'text-warning';
              } elseif ($hospital->status === 'Red') {
                  $statusClass = 'text-danger';
              }
              ?>
              <tr>
                <td class="admin-td-id"><?= esc($hospital->id) ?></td>
                <td class="admin-td-code"><?= esc($hospital->code) ?></td>
                <td class="admin-td-name"><?= esc($hospital->name) ?></td>
                <td><?= esc($hospital->category) ?></td>
                <td>
                  <span class="<?= $statusClass ?> admin-td-sm admin-td-mono">
                    ● <?= esc($hospital->status) ?>
                  </span>
                </td>
                <td class="admin-td-mono-sm">
                  <?= esc($hospital->created_at ? $hospital->created_at->format('Y-m-d H:i') : '—') ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= url_to('admin.hospitals.edit', $hospital->id) ?>" class="btn btn-outline-secondary btn-sm px-3 py-2 d-inline-block admin-btn-edit">
                      Edit
                    </a>
                    <a href="<?= url_to('admin.hospitals.delete', $hospital->id) ?>" 
                       class="btn btn-danger btn-sm px-3 py-2 d-inline-block admin-btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this hospital facility? This might affect handovers associated with this facility.');">
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
          <?= $pager->links('hospitals', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
