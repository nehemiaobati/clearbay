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

<div class="container py-5 mt-5">
  <!-- Breadcrumb -->
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
        <span class="s-label-text">Facilities Registry</span>
      </div>
      <h1 class="s-title mb-0">
        Manage <span class="fst-italic text-secondary">Hospitals</span>
      </h1>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= url_to('admin.hospitals.new') ?>" class="btn btn-primary py-2 touch-target">
        Register Hospital +
      </a>
    </div>
  </div>

  <div class="card border-secondary border-opacity-10 p-4" style="background: var(--color-bg-card);">
    <?php if (empty($hospitals)) : ?>
      <p class="text-center my-4 text-secondary">No hospital facilities registered in the system.</p>
    <?php else : ?>
      <!-- Mobile Card List (visible <768px) -->
      <div class="d-md-none">
        <?php foreach ($hospitals as $hospital) : ?>
          <?php
          $statusClass = 'text-success';
          if ($hospital->status === 'Amber') $statusClass = 'text-warning';
          elseif ($hospital->status === 'Red') $statusClass = 'text-danger';
          elseif ($hospital->status === 'Recruiting') $statusClass = 'text-info';
          ?>
          <div class="list-card-item">
            <div>
              <div class="fw-semibold" style="color: var(--color-text-main);"><?= esc($hospital->name) ?></div>
              <div class="small text-secondary"><?= esc($hospital->code) ?> · <?= esc($hospital->category) ?></div>
              <div class="mt-1">
                <span class="<?= $statusClass ?> small font-monospace">● <?= esc($hospital->status) ?></span>
                <span class="small font-monospace text-secondary ms-2">
                  <?= esc($hospital->created_at ? $hospital->created_at->format('Y-m-d H:i') : '') ?>
                </span>
              </div>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
              <a href="<?= url_to('admin.hospitals.edit', $hospital->id) ?>" class="btn btn-sm btn-outline-secondary touch-target">Edit</a>
              <a href="<?= url_to('admin.hospitals.delete', $hospital->id) ?>"
                class="btn btn-sm btn-danger touch-target"
                onclick="return confirm('Are you sure you want to delete this hospital facility?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Desktop Table (visible md+) -->
      <div class="d-none d-md-block">
        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--color-text-main);">
            <thead>
              <tr class="mono-label text-secondary">
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
                $statusClass = 'text-success';
                if ($hospital->status === 'Amber') $statusClass = 'text-warning';
                elseif ($hospital->status === 'Red') $statusClass = 'text-danger';
                elseif ($hospital->status === 'Recruiting') $statusClass = 'text-info';
                ?>
                <tr>
                  <td class="font-monospace small text-secondary"><?= esc($hospital->id) ?></td>
                  <td class="font-monospace"><?= esc($hospital->code) ?></td>
                  <td class="fw-semibold"><?= esc($hospital->name) ?></td>
                  <td><?= esc($hospital->category) ?></td>
                  <td>
                    <span class="<?= $statusClass ?> small font-monospace">
                      ● <?= esc($hospital->status) ?>
                    </span>
                  </td>
                  <td class="small font-monospace text-secondary">
                    <?= esc($hospital->created_at ? $hospital->created_at->format('Y-m-d H:i') : '—') ?>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-2">
                      <a href="<?= url_to('admin.hospitals.edit', $hospital->id) ?>" class="btn btn-sm btn-outline-secondary px-3 touch-target">Edit</a>
                      <a href="<?= url_to('admin.hospitals.delete', $hospital->id) ?>"
                        class="btn btn-sm btn-danger px-3 touch-target"
                        onclick="return confirm('Are you sure you want to delete this hospital facility?');">Delete</a>
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
          <?= $pager->links('hospitals', 'default_full') ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>