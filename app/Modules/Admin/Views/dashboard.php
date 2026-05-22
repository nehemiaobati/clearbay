<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var int $pilotCount
 * @var int $handoverCount
 * @var int $hospitalCount
 * @var int $ambulanceCount
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
  <!-- Blueprint Header -->
  <div class="blueprint-header reveal">
    <div class="s-label">
      <div class="s-label-line"></div>
      <span class="s-label-text">Admin Panel</span>
    </div>
    <h1 class="s-title admin-heading">
      System<br><span class="ital dim">Administration.</span>
    </h1>
    <p class="text-muted mt-2 admin-subtitle">
      Welcome to the ClearBay administrative dashboard. Monitor program signups, fleet telemetry, facility statuses, and queue handovers.
    </p>
  </div>

  <!-- Metric Overview Cards -->
  <div class="row g-4 mb-5">
    <!-- Pilots Stat -->
    <div class="col-6 col-lg-3">
      <div class="card blueprint-card p-4 h-100">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Pilot Signups</span>
            <span class="d-block admin-stat-val">
              <?= esc($pilotCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.pilots.list') ?>" class="btn btn-outline-secondary btn-sm w-100 admin-dash-btn">
              Manage Signups
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Handovers Stat -->
    <div class="col-6 col-lg-3">
      <div class="card blueprint-card p-4 h-100">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Handovers Queue</span>
            <span class="d-block admin-stat-val">
              <?= esc($handoverCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary btn-sm w-100 admin-dash-btn">
              Manage Queue
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Hospitals Stat -->
    <div class="col-6 col-lg-3">
      <div class="card blueprint-card p-4 h-100">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Facilities</span>
            <span class="d-block admin-stat-val">
              <?= esc($hospitalCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary btn-sm w-100 admin-dash-btn">
              Manage Hospitals
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Ambulances Stat -->
    <div class="col-6 col-lg-3">
      <div class="card blueprint-card p-4 h-100">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Active Fleet</span>
            <span class="d-block admin-stat-val">
              <?= esc($ambulanceCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.ambulances.list') ?>" class="btn btn-outline-secondary btn-sm w-100 admin-dash-btn">
              Manage Fleet
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Operational Shortcuts Panel -->
  <div class="card blueprint-card p-4 p-md-5">
    <h3 class="admin-card-heading">Quick Registry Actions</h3>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.pilots.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center admin-quick-btn">
          Manual Pilot Signup &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.handovers.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center admin-quick-btn">
          Dispatch New Handover &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.hospitals.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center admin-quick-btn">
          Register Hospital &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.ambulances.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center admin-quick-btn">
          Register Ambulance &nbsp;+
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
