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

<div class="container py-5 mt-5">
  <!-- Breadcrumb / Back Link -->
  <div class="mb-4">
    <a href="<?= url_to('admin.dashboard') ?>" class="mono-label text-decoration-none" style="color: var(--color-brand-primary);">
      ← Back to Dashboard
    </a>
  </div>

  <!-- Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 reveal">
    <div>
      <div class="s-label mb-1">
        <div class="s-label-line"></div>
        <span class="s-label-text">Handovers Queue Registry</span>
      </div>
      <h1 class="s-title mb-0">
        Manage <span class="fst-italic text-secondary">Handovers</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.handovers.new') ?>" class="btn btn-primary touch-target">
        Dispatch Handover +
      </a>
    </div>
  </div>

  <!-- Responsive Table with Mobile Card Fallback -->
  <div class="card border-secondary border-opacity-10 p-4" style="background: var(--color-bg-card);">
    <?php if (empty($handovers)) : ?>
      <p class="text-center my-4 text-secondary">No handover queue records registered in the system.</p>
    <?php else : ?>

      <!-- Mobile Card List (<768px) -->
      <div class="d-md-none">
        <?php foreach ($handovers as $handover) : ?>
          <?php
          $acuityClass = 'wait-green';
          if ($handover->acuity === 'Critical') $acuityClass = 'wait-red';
          elseif ($handover->acuity === 'Serious') $acuityClass = 'wait-amber';

          $statusClass = 'badge bg-secondary';
          if ($handover->status === 'En route') $statusClass = 'badge bg-success text-dark';
          elseif ($handover->status === 'Arrived') $statusClass = 'badge bg-warning text-dark';
          elseif ($handover->status === 'Acknowledged') $statusClass = 'badge bg-info text-dark';
          elseif ($handover->status === 'Preparing') $statusClass = 'badge bg-primary text-dark';
          ?>
          <div class="list-card-item flex-column align-items-start gap-2 py-3">
            <div class="d-flex justify-content-between align-items-center w-100">
              <span class="fw-semibold" style="color: var(--color-text-main);"><?= esc($handover->ambulance_unit ?? '—') ?></span>
              <span class="<?= $statusClass ?> font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($handover->status) ?></span>
            </div>
            <div class="w-100">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-secondary small"><?= esc($handover->hospital_name ?? '—') ?></span>
                <span class="wait-pill <?= $acuityClass ?>"><?= esc($handover->acuity) ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="mono-label small"><?= esc($handover->patient_age) ?> y/o (<?= esc($handover->patient_gender) ?>)</span>
                <span class="mono-label small">ETA: <?= esc($handover->eta_minutes) ?> min</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="mono-label small">Wait: <?= esc($handover->wait_time_minutes) ?> min</span>
                <span class="mono-label small"><?= esc($handover->created_at ? $handover->created_at->format('Y-m-d H:i') : '—') ?></span>
              </div>
            </div>
            <div class="d-flex gap-2 w-100">
              <a href="<?= url_to('admin.handovers.edit', $handover->id) ?>" class="btn btn-outline-secondary btn-sm flex-fill touch-target">Edit</a>
              <a href="<?= url_to('admin.handovers.delete', $handover->id) ?>" class="btn btn-danger btn-sm flex-fill touch-target" onclick="return confirm('Are you sure you want to delete this handover record?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Desktop Table (≥768px) -->
      <div class="d-none d-md-block">
        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--color-text-main);">
            <thead>
              <tr class="mono-label text-secondary">
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
                $acuityClass = 'wait-green';
                if ($handover->acuity === 'Critical') $acuityClass = 'wait-red';
                elseif ($handover->acuity === 'Serious') $acuityClass = 'wait-amber';

                $statusClass = 'badge bg-secondary';
                if ($handover->status === 'En route') $statusClass = 'badge bg-success text-dark';
                elseif ($handover->status === 'Arrived') $statusClass = 'badge bg-warning text-dark';
                elseif ($handover->status === 'Acknowledged') $statusClass = 'badge bg-info text-dark';
                elseif ($handover->status === 'Preparing') $statusClass = 'badge bg-primary text-dark';
                ?>
                <tr>
                  <td class="font-monospace small text-secondary"><?= esc($handover->id) ?></td>
                  <td class="font-monospace"><?= esc($handover->ambulance_unit ?? '—') ?></td>
                  <td class="fw-semibold"><?= esc($handover->hospital_name ?? '—') ?></td>
                  <td><?= esc($handover->patient_age) ?> y/o (<?= esc($handover->patient_gender) ?>)</td>
                  <td><span class="wait-pill <?= $acuityClass ?>"><?= esc($handover->acuity) ?></span></td>
                  <td class="font-monospace small"><?= esc($handover->eta_minutes) ?> min</td>
                  <td class="font-monospace small"><?= esc($handover->wait_time_minutes) ?> min</td>
                  <td><span class="<?= $statusClass ?> font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($handover->status) ?></span></td>
                  <td class="small font-monospace text-secondary"><?= esc($handover->created_at ? $handover->created_at->format('Y-m-d H:i') : '—') ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-2">
                      <a href="<?= url_to('admin.handovers.edit', $handover->id) ?>" class="btn btn-outline-secondary btn-sm px-3 touch-target">Edit</a>
                      <a href="<?= url_to('admin.handovers.delete', $handover->id) ?>" class="btn btn-danger btn-sm px-3 touch-target" onclick="return confirm('Are you sure you want to delete this handover record?');">Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <?php if ($pager) : ?>
        <div class="mt-4 d-flex justify-content-center">
          <?= $pager->links('handovers', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>