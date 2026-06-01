<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var array $details
 */
$h = $details['hospital'];
$status = $h->status;
$status_color = 'bg-success';
if ($status === 'RED') $status_color = 'bg-danger';
elseif ($status === 'AMBER') $status_color = 'bg-warning text-dark';
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page max-width-600">
  <!-- Inner Back navigation -->
  <div class="mb-4 reveal">
    <a href="<?= url_to('ambulance.home') ?>" class="mono-label text-decoration-none admin-back">← Back to Map</a>
  </div>

  <div class="card blueprint-card p-4 p-md-5 reveal">
    <!-- Hospital Header -->
    <div class="text-center mb-4">
      <span class="mono-label text-muted d-block mb-1"><?= esc($h->category) ?></span>
      <h2 class="h3 fw-bold text-cream"><?= esc($h->name) ?></h2>
      <p class="text-muted small mt-1"><?= esc($h->address) ?></p>
    </div>

    <!-- Status Card -->
    <div class="p-4 rounded mb-4 text-center <?= $status === 'RED' ? 'bg-danger bg-opacity-10 border border-danger border-opacity-20' : ($status === 'AMBER' ? 'bg-warning bg-opacity-10 border border-warning border-opacity-20' : 'bg-success bg-opacity-10 border border-success border-opacity-20') ?>">
      <span class="badge <?= $status_color ?> py-2 px-3 fs-6 mb-2"><?= $status ?></span>
      <h3 class="h4 fw-bold m-0 text-cream">Wait Time Estimate: ~<?= esc($details['avg_wait']) ?> min</h3>
    </div>

    <!-- Queue & Bay details -->
    <div class="row g-3 mb-4 text-center">
      <div class="col-6">
        <div class="p-3 bg-secondary bg-opacity-10 rounded">
          <span class="d-block fs-3 fw-bold text-primary"><?= esc($h->bays_available) ?></span>
          <span class="mono-label text-muted small mt-1 d-block">Bays Available</span>
        </div>
      </div>
      <div class="col-6">
        <div class="p-3 bg-secondary bg-opacity-10 rounded">
          <span class="d-block fs-3 fw-bold text-warning"><?= esc($details['queue_count']) ?></span>
          <span class="mono-label text-muted small mt-1 d-block">Ambulances in Queue</span>
        </div>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="d-grid gap-3">
      <?php if ($status === 'RED') : ?>
        <button class="btn btn-secondary py-3 fw-bold fs-6 touch-target-btn" disabled>
          Hospital is currently full — please select another.
        </button>
      <?php else : ?>
        <a href="<?= url_to('ambulance.pre_notify', $h->id) ?>" class="btn btn-primary py-3 fw-bold fs-6 d-flex align-items-center justify-content-center touch-target-btn">
          Send Pre-Notification →
        </a>
      <?php endif; ?>

      <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $h->lat ?>,<?= $h->lng ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-outline-secondary py-3 fw-bold fs-6 d-flex align-items-center justify-content-center touch-target-btn">
        Get Directions (GPS)
      </a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>