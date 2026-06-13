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
        <span class="s-label-text">Pilot Signups Registry</span>
      </div>
      <h1 class="s-title mb-0">
        Manage <span class="fst-italic text-secondary">Applications</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.pilots.new') ?>" class="btn btn-primary touch-target">
        Add Manual Entry +
      </a>
    </div>
  </div>

  <!-- Responsive Table with Mobile Card Fallback -->
  <div class="card border-secondary border-opacity-10 p-4" style="background: var(--color-bg-card);">
    <?php if (empty($pilots)) : ?>
      <p class="text-center my-4 text-secondary">No pilot signup records registered in the system.</p>
    <?php else : ?>

      <!-- Mobile Card List (<768px) -->
      <div class="d-md-none">
        <?php foreach ($pilots as $pilot) : ?>
          <div class="list-card-item flex-column align-items-start gap-2 py-3">
            <div class="d-flex justify-content-between align-items-center w-100">
              <span class="fw-semibold" style="color: var(--color-text-main);"><?= esc($pilot->full_name) ?></span>
              <span class="badge bg-secondary font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($pilot->user_role) ?></span>
            </div>
            <div class="w-100">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-secondary small"><?= esc($pilot->email_address) ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="mono-label small"><?= esc($pilot->organisation) ?></span>
                <span class="mono-label small"><?= esc($pilot->phone_number ?? '—') ?></span>
              </div>
              <div class="d-flex justify-content-end mb-2">
                <span class="mono-label small"><?= esc($pilot->created_at ? $pilot->created_at->format('Y-m-d H:i') : '—') ?></span>
              </div>
            </div>
            <div class="d-flex gap-2 w-100">
              <a href="<?= url_to('admin.pilots.edit', $pilot->id) ?>" class="btn btn-outline-secondary btn-sm flex-fill touch-target">Edit</a>
              <a href="<?= url_to('admin.pilots.delete', $pilot->id) ?>" class="btn btn-danger btn-sm flex-fill touch-target" onclick="return confirm('Are you sure you want to delete this pilot application?');">Delete</a>
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
                  <td class="font-monospace small text-secondary"><?= esc($pilot->id) ?></td>
                  <td class="fw-semibold"><?= esc($pilot->full_name) ?></td>
                  <td><?= esc($pilot->email_address) ?></td>
                  <td><?= esc($pilot->organisation) ?></td>
                  <td class="small font-monospace"><?= esc($pilot->user_role) ?></td>
                  <td class="font-monospace small"><?= esc($pilot->phone_number ?? '—') ?></td>
                  <td class="small font-monospace text-secondary"><?= esc($pilot->created_at ? $pilot->created_at->format('Y-m-d H:i') : '—') ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-2">
                      <a href="<?= url_to('admin.pilots.edit', $pilot->id) ?>" class="btn btn-outline-secondary btn-sm px-3 touch-target">Edit</a>
                      <a href="<?= url_to('admin.pilots.delete', $pilot->id) ?>" class="btn btn-danger btn-sm px-3 touch-target" onclick="return confirm('Are you sure you want to delete this pilot application?');">Delete</a>
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
          <?= $pager->links('pilots', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>