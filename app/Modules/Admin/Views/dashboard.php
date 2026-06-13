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
 * @var int $userCount
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container py-5 mt-5">
  <!-- Blueprint Header -->
  <div class="mb-5 reveal">
    <div class="s-label">
      <div class="s-label-line"></div>
      <span class="s-label-text">Admin Panel</span>
    </div>
    <h1 class="s-title">
      System<br><span class="fst-italic text-secondary">Administration.</span>
    </h1>
    <p class="text-secondary mt-2" style="max-width: 600px;">
      Welcome to the ClearBay administrative dashboard. Monitor program signups, fleet telemetry, facility statuses, and queue handovers.
    </p>
  </div>

  <!-- Metric Overview Cards — using BS5 grid -->
  <div class="row g-4 mb-5">
    <!-- Pilots Stat -->
    <div class="col-6 col-lg-4">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Pilot Signups</span>
            <span class="d-block admin-stat-val">
              <?= esc((string) $pilotCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.pilots.list') ?>" class="btn btn-outline-secondary btn-sm w-100">
              Manage Signups
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Handovers Stat -->
    <div class="col-6 col-lg-4">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Handovers Queue</span>
            <span class="d-block admin-stat-val">
              <?= esc((string) $handoverCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.handovers.list') ?>" class="btn btn-outline-secondary btn-sm w-100">
              Manage Queue
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Hospitals Stat -->
    <div class="col-6 col-lg-4">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Facilities</span>
            <span class="d-block admin-stat-val">
              <?= esc((string) $hospitalCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.hospitals.list') ?>" class="btn btn-outline-secondary btn-sm w-100">
              Manage Hospitals
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Ambulances Stat -->
    <div class="col-6 col-lg-4">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">Active Fleet</span>
            <span class="d-block admin-stat-val">
              <?= esc((string) $ambulanceCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.ambulances.list') ?>" class="btn btn-outline-secondary btn-sm w-100">
              Manage Fleet
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Stat -->
    <div class="col-6 col-lg-4">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2">User Accounts</span>
            <span class="d-block admin-stat-val">
              <?= esc((string) $userCount) ?>
            </span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.users.list') ?>" class="btn btn-outline-secondary btn-sm w-100">
              Manage Users
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- System Performance Analytics -->
    <div class="col-6 col-lg-4">
      <div class="card border-primary border-opacity-25 p-4 h-100" style="background: rgba(78, 138, 99, 0.03);">
        <div class="d-flex flex-column justify-content-between h-100">
          <div>
            <span class="mono-label d-block mb-2 text-primary">System Performance</span>
            <span class="d-block admin-stat-val fs-2">Analytics</span>
          </div>
          <div class="mt-4">
            <a href="<?= url_to('admin.analytics') ?>" class="btn btn-primary btn-sm w-100">
              View Analytics
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Operational Shortcuts Panel -->
  <div class="card border-secondary border-opacity-10 p-4 p-md-5" style="background: var(--color-bg-card);">
    <h3 class="font-monospace text-uppercase fs-6 mb-4" style="color: var(--color-text-main);">Quick Registry Actions</h3>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.pilots.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center touch-target">
          Manual Pilot Signup &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.handovers.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center touch-target">
          Dispatch New Handover &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.hospitals.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center touch-target">
          Register Hospital &nbsp;+
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="<?= url_to('admin.ambulances.new') ?>" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center touch-target">
          Register Ambulance &nbsp;+
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>