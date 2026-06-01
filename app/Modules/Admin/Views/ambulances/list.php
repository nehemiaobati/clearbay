<?php
/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var array $ambulances
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
        <span class="s-label-text">Fleet Registry</span>
      </div>
      <h1 class="s-title mb-2 admin-heading">
        Manage <span class="ital dim">Ambulances</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.ambulances.new') ?>" class="btn btn-primary admin-btn-action">
        Register Vehicle +
      </a>
    </div>
  </div>

  <!-- Responsive Table -->
  <div class="card blueprint-card p-4">
    <?php if (empty($ambulances)) : ?>
      <p class="text-center my-4 text-muted">No ambulance fleet units registered in the system.</p>
    <?php else : ?>
      <div class="table-responsive">
        <table class="table queue-table align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Unit ID</th>
              <th>Service Provider</th>
              <th>Registered At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ambulances as $ambulance) : ?>
              <tr>
                <td class="admin-td-id"><?= esc($ambulance->id) ?></td>
                <td class="admin-td-code"><?= esc($ambulance->unit_id) ?></td>
                <td class="admin-td-name"><?= esc($ambulance->provider) ?></td>
                <td class="admin-td-mono-sm">
                  <?= esc($ambulance->created_at ? $ambulance->created_at->format('Y-m-d H:i') : '—') ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= url_to('admin.ambulances.edit', $ambulance->id) ?>" class="btn btn-outline-secondary btn-sm px-3 py-1 admin-btn-edit">
                      Edit
                    </a>
                    <a href="<?= url_to('admin.ambulances.delete', $ambulance->id) ?>" 
                       class="btn btn-danger btn-sm px-3 py-1 admin-btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this ambulance unit? This might affect handovers associated with this vehicle.');">
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
          <?= $pager->links('ambulances', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
