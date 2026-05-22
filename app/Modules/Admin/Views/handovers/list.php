<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $handovers
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
        <span class="s-label-text">Handovers Queue Registry</span>
      </div>
      <h1 class="s-title mb-2 admin-heading">
        Manage <span class="ital dim">Handovers</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.handovers.new') ?>" class="btn btn-primary admin-btn-action">
        Dispatch Handover +
      </a>
    </div>
  </div>

  <!-- Responsive Table -->
  <div class="card blueprint-card p-4">
    <?php if (empty($handovers)) : ?>
      <p class="text-center my-4 text-muted">No handover queue records registered in the system.</p>
    <?php else : ?>
      <div class="table-responsive">
        <table class="queue-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ambulance</th>
              <th>Hospital</th>
              <th>Patient</th>
              <th>Acuity</th>
              <th>ETA</th>
              <th>Wait Time</th>
              <th>Status</th>
              <th>Created At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($handovers as $handover) : ?>
              <?php
              // Acuity Badge color mapping
              $acuityClass = 'wait-green';
              if ($handover->acuity === 'Critical') {
                  $acuityClass = 'wait-red';
              } elseif ($handover->acuity === 'Serious') {
                  $acuityClass = 'wait-amber';
              }

              // Status Badge color mapping
              $statusClass = 'badge bg-secondary';
              if ($handover->status === 'En route') {
                  $statusClass = 'badge bg-success text-dark';
              } elseif ($handover->status === 'Arrived') {
                  $statusClass = 'badge bg-warning text-dark';
              } elseif ($handover->status === 'Acknowledged') {
                  $statusClass = 'badge bg-info text-dark';
              } elseif ($handover->status === 'Preparing') {
                  $statusClass = 'badge bg-primary text-dark';
              }
              ?>
              <tr>
                <td class="admin-td-id"><?= esc($handover->id) ?></td>
                <td class="admin-td-code">
                  <?= esc($handover->ambulance_unit ?? '—') ?>
                </td>
                <td class="admin-td-name"><?= esc($handover->hospital_name ?? '—') ?></td>
                <td>
                  <?= esc($handover->patient_age) ?> y/o (<?= esc($handover->patient_gender) ?>)
                </td>
                <td>
                  <span class="wait-pill <?= $acuityClass ?>">
                    <?= esc($handover->acuity) ?>
                  </span>
                </td>
                <td class="admin-td-mono"><?= esc($handover->eta_minutes) ?> min</td>
                <td class="admin-td-mono"><?= esc($handover->wait_time_minutes) ?> min</td>
                <td>
                  <span class="<?= $statusClass ?> admin-status-pill">
                    <?= esc($handover->status) ?>
                  </span>
                </td>
                <td class="admin-td-mono-sm">
                  <?= esc($handover->created_at ? $handover->created_at->format('Y-m-d H:i') : '—') ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="<?= url_to('admin.handovers.edit', $handover->id) ?>" class="btn btn-outline-secondary btn-sm px-3 py-1 admin-btn-edit">
                      Edit
                    </a>
                    <a href="<?= url_to('admin.handovers.delete', $handover->id) ?>" 
                       class="btn btn-danger btn-sm px-3 py-1 admin-btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this handover record?');">
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
          <?= $pager->links('handovers', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
