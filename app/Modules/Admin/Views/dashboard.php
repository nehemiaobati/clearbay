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

<div class="container" style="margin-top: 120px; margin-bottom: 80px;">
  <!-- Blueprint Header -->
  <div class="blueprint-header reveal">
    <div class="s-label">
      <div class="s-label-line"></div>
      <span class="s-label-text">Admin Panel</span>
    </div>
    <h1 class="s-title" style="font-family: var(--serif); font-weight: 700; color: var(--cream);">
      System<br><span class="ital dim">Administration.</span>
    </h1>
    <p class="text-muted mt-2" style="font-family: var(--sans); font-size: 1.1rem; max-width: 600px;">
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
            <span class="d-block" style="font-family: var(--serif); font-size: 3rem; font-weight: 700; color: var(--sage-ll); line-height: 1;">
              <?= esc($pilotCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.pilots.list') ?>" class="btn btn-outline-secondary btn-sm w-100" style="padding: 0.5rem 1rem !important; font-size: 0.72rem !important;">
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
            <span class="d-block" style="font-family: var(--serif); font-size: 3rem; font-weight: 700; color: var(--sage-ll); line-height: 1;">
              <?= esc($handoverCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary btn-sm w-100" style="padding: 0.5rem 1rem !important; font-size: 0.72rem !important;">
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
            <span class="d-block" style="font-family: var(--serif); font-size: 3rem; font-weight: 700; color: var(--sage-ll); line-height: 1;">
              <?= esc($hospitalCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary btn-sm w-100" style="padding: 0.5rem 1rem !important; font-size: 0.72rem !important;">
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
            <span class="d-block" style="font-family: var(--serif); font-size: 3rem; font-weight: 700; color: var(--sage-ll); line-height: 1;">
              <?= esc($ambulanceCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.ambulances.list') ?>" class="btn btn-outline-secondary btn-sm w-100" style="padding: 0.5rem 1rem !important; font-size: 0.72rem !important;">
              Manage Fleet
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Operational Shortcuts Panel -->
  <div class="card blueprint-card p-4 p-md-5">
    <h3 style="font-family: var(--serif); font-weight: 700; color: var(--cream); margin-bottom: 1.5rem;">Quick Registry Actions</h3>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.pilots.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center" style="font-size: 0.78rem !important;">
          Manual Pilot Signup &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.handovers.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center" style="font-size: 0.78rem !important;">
          Dispatch New Handover &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.hospitals.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center" style="font-size: 0.78rem !important;">
          Register Hospital &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.ambulances.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center" style="font-size: 0.78rem !important;">
          Register Ambulance &nbsp;+
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
