<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $ambulances
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container" style="margin-top: 120px; margin-bottom: 80px;">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.dashboard') ?>" class="mono-label text-decoration-none" style="color: var(--sage-ll) !important;">
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
      <h1 class="s-title mb-2" style="font-family: var(--serif); font-weight: 700; color: var(--cream); font-size: 2.2rem; line-height: 1.2;">
        Manage <span class="ital dim">Ambulances</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.ambulances.new') ?>" class="btn btn-primary" style="font-size: 0.78rem !important; padding: 0.75rem 1.5rem !important;">
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
        <table class="queue-table">
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
                <td style="font-family: var(--mono); color: rgba(255,255,255,0.3);"><?= esc($ambulance->id) ?></td>
                <td style="font-family: var(--mono); font-weight: 600; color: var(--sage-ll);"><?= esc($ambulance->unit_id) ?></td>
                <td style="font-weight: 500; color: var(--cream);"><?= esc($ambulance->provider) ?></td>
                <td style="font-family: var(--mono); font-size: 0.75rem;">
                  <?= esc($ambulance->created_at ? $ambulance->created_at->format('Y-m-d H:i') : '—') ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= url_to('admin.ambulances.edit', $ambulance->id) ?>" class="btn btn-outline-secondary btn-sm px-3 py-1" style="font-size: 0.72rem !important; border-color: rgba(255,255,255,0.1) !important;">
                      Edit
                    </a>
                    <a href="<?= url_to('admin.ambulances.delete', $ambulance->id) ?>" 
                       class="btn btn-danger btn-sm px-3 py-1" 
                       style="font-size: 0.72rem !important;" 
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

<style>
/* Style CodeIgniter 4 full pager to fit theme perfectly */
.admin-pager ul {
  display: flex;
  list-style: none;
  padding: 0;
  gap: 0.5rem;
  font-family: var(--mono);
  font-size: 0.78rem;
}
.admin-pager li a, .admin-pager li span {
  display: block;
  padding: 0.5rem 0.8rem;
  border: 1px solid rgba(247,244,238,0.1);
  color: var(--cream);
  text-decoration: none;
  transition: all 0.2s;
}
.admin-pager li.active a, .admin-pager li.active span {
  background-color: var(--sage-l);
  color: var(--ink);
  border-color: var(--sage-l);
}
.admin-pager li a:hover {
  background-color: rgba(255,255,255,0.05);
  border-color: var(--sage-l);
}
</style>

<?= $this->endSection() ?>
