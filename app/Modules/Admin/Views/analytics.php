<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $analytics
 * @var string $range
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-5 mt-5">
  <!-- Inner Navigation Back-Link -->
  <div class="mb-4 reveal">
    <a href="<?= url_to('admin.dashboard') ?>" class="mono-label text-decoration-none" style="color: var(--color-brand-primary);">← Back to Admin Panel</a>
  </div>

  <!-- Header with Date Filters & Export -->
  <div class="row align-items-end g-3 mb-5 reveal">
    <div class="col-md-6">
      <div>
        <div class="s-label mb-1">
          <div class="s-label-line"></div>
          <span class="s-label-text">Analytics Overview</span>
        </div>
        <h1 class="s-title">All Facilities</h1>
      </div>
    </div>
    <div class="col-md-6 text-md-end d-flex flex-column flex-sm-row gap-2 justify-content-md-end align-items-stretch align-items-md-center">
      <a href="<?= url_to('admin.analytics.export') ?>" class="btn btn-primary px-3 touch-target">Export Report</a>
      <form action="<?= url_to('admin.analytics') ?>" method="GET" id="rangeForm" class="m-0">
        <div class="input-group">
          <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary mono-label">Range:</span>
          <select name="range" class="form-select bg-dark text-light border-secondary border-opacity-25" onchange="document.getElementById('rangeForm').submit();" style="min-height: 48px;">
            <option value="7" <?= $range === '7' ? 'selected' : '' ?>>Past 7 Days</option>
            <option value="30" <?= $range === '30' ? 'selected' : '' ?>>Past 30 Days</option>
            <option value="90" <?= $range === '90' ? 'selected' : '' ?>>Past 90 Days</option>
          </select>
        </div>
      </form>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row g-4 mb-5 reveal">
    <!-- Line Chart: Average Wait Times -->
    <div class="col-lg-6">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <h3 class="font-monospace text-uppercase fs-6 mb-3" style="color: var(--color-text-main);">Avg Off-Load Wait Time (Minutes)</h3>
        <div class="chart-container">
          <canvas id="waitChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Bar Chart: Total Handovers -->
    <div class="col-lg-6">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <h3 class="font-monospace text-uppercase fs-6 mb-3" style="color: var(--color-text-main);">Total Handovers Completed</h3>
        <div class="chart-container">
          <canvas id="handoverChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Tables Row -->
  <div class="row g-4 mb-5 reveal">
    <!-- Facility Breakdown Table -->
    <div class="col-lg-6">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <h3 class="font-monospace text-uppercase fs-6 mb-4" style="color: var(--color-text-main);">Performance summary by Facility</h3>

        <!-- Mobile Card List (<768px) -->
        <div class="d-md-none">
          <?php if (empty($analytics['facility_performance'])) : ?>
            <p class="text-center text-secondary py-4">No completed handover logs found in selected range.</p>
          <?php else : ?>
            <?php foreach ($analytics['facility_performance'] as $row) : ?>
              <div class="list-card-item flex-column align-items-start gap-2 py-3 border-bottom border-secondary border-opacity-10">
                <div class="d-flex justify-content-between align-items-center w-100">
                  <span class="fw-semibold" style="color: var(--color-text-main);"><?= esc($row['hospital_name']) ?></span>
                  <span class="badge <?= esc($row['status_class']) ?>">
                    <?= esc($row['avg_wait']) ?> min
                  </span>
                </div>
                <div class="d-flex justify-content-between w-100">
                  <span class="mono-label small text-secondary"><?= esc($row['total_handovers']) ?> handovers completed</span>
                  <span class="mono-label small text-secondary">Baseline: <?= esc($row['baseline_avg'] ?? 60) ?> min</span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Desktop Table (≥768px) -->
        <div class="d-none d-md-block">
          <div class="table-responsive">
            <table class="table align-middle" style="color: var(--color-text-main);">
              <thead>
                <tr class="mono-label text-secondary">
                  <th>Facility</th>
                  <th>Handovers</th>
                  <th>Baseline</th>
                  <th>Avg Wait</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($analytics['facility_performance'])) : ?>
                  <tr>
                    <td colspan="4" class="text-center text-secondary py-4">No completed handover logs found in selected range.</td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($analytics['facility_performance'] as $row) : ?>
                    <tr>
                      <td class="fw-semibold"><?= esc($row['hospital_name']) ?></td>
                      <td><?= esc($row['total_handovers']) ?></td>
                      <td class="mono-label"><?= esc($row['baseline_avg'] ?? 60) ?> min</td>
                      <td>
                        <span class="badge <?= esc($row['status_class']) ?>">
                          <?= esc($row['avg_wait']) ?> min
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- EMS Provider Table -->
    <div class="col-lg-6">
      <div class="card border-secondary border-opacity-10 p-4 h-100" style="background: var(--color-bg-card);">
        <h3 class="font-monospace text-uppercase fs-6 mb-4" style="color: var(--color-text-main);">Performance summary by EMS Provider</h3>

        <!-- Mobile Card List (<768px) -->
        <div class="d-md-none">
          <?php if (empty($analytics['provider_performance'])) : ?>
            <p class="text-center text-secondary py-4">No completed handover logs found in selected range.</p>
          <?php else : ?>
            <?php foreach ($analytics['provider_performance'] as $row) : ?>
              <div class="list-card-item flex-column align-items-start gap-2 py-3 border-bottom border-secondary border-opacity-10">
                <div class="d-flex justify-content-between align-items-center w-100">
                  <span class="fw-semibold" style="color: var(--color-text-main);"><?= esc($row['provider']) ?></span>
                  <span class="mono-label small text-secondary"><?= esc($row['total_ambulances']) ?> ambulances</span>
                </div>
                <span class="mono-label small text-secondary"><?= esc($row['total_handovers']) ?> handovers completed</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Desktop Table (≥768px) -->
        <div class="d-none d-md-block">
          <div class="table-responsive">
            <table class="table align-middle" style="color: var(--color-text-main);">
              <thead>
                <tr class="mono-label text-secondary">
                  <th>EMS Provider</th>
                  <th>Handovers</th>
                  <th>Ambulances</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($analytics['provider_performance'])) : ?>
                  <tr>
                    <td colspan="3" class="text-center text-secondary py-4">No completed handover logs found in selected range.</td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($analytics['provider_performance'] as $row) : ?>
                    <tr>
                      <td class="fw-semibold"><?= esc($row['provider']) ?></td>
                      <td><?= esc($row['total_handovers']) ?></td>
                      <td><?= esc($row['total_ambulances']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Theme Colors fetched dynamically from variables
    const style = getComputedStyle(document.documentElement);
    const colorSage = style.getPropertyValue('--sage').trim() || '#3D6B4F';
    const colorSageL = style.getPropertyValue('--sage-l').trim() || '#4E8A63';
    const colorSageLL = style.getPropertyValue('--sage-ll').trim() || '#7AB890';
    const colorRed = style.getPropertyValue('--red').trim() || '#C23B22';
    const colorCream = style.getPropertyValue('--cream').trim() || '#EDE9E0';

    // Analytics payload
    const waitsData = <?= json_encode($analytics['daily_waits']) ?>;
    const countsData = <?= json_encode($analytics['daily_counts']) ?>;

    if (waitsData && waitsData.length) {
      // A. Wait Times Chart
      const waitLabels = waitsData.map(d => d.day);
      const waitValues = waitsData.map(d => d.avg_wait);

      const ctxWait = document.getElementById('waitChart').getContext('2d');
      new Chart(ctxWait, {
        type: 'line',
        data: {
          labels: waitLabels,
          datasets: [{
            label: 'Average Wait (min)',
            data: waitValues,
            borderColor: colorSageL,
            backgroundColor: 'rgba(78, 138, 99, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.1
          }, {
            label: 'Pre-ClearBay Baseline (<?= esc($analytics['aggregate_baseline']) ?> min)',
            data: Array(waitLabels.length).fill(<?= (int) $analytics['aggregate_baseline'] ?>),
            borderColor: colorRed,
            borderWidth: 1,
            borderDash: [5, 5],
            pointRadius: 0,
            fill: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(255, 255, 255, 0.05)'
              },
              ticks: {
                color: colorCream
              }
            },
            x: {
              grid: {
                color: 'rgba(255, 255, 255, 0.05)'
              },
              ticks: {
                color: colorCream
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                color: colorCream
              }
            }
          }
        }
      });
    }

    if (countsData && countsData.length) {
      // B. Total Handovers Chart
      const handoverLabels = countsData.map(d => d.day);
      const handoverValues = countsData.map(d => d.count);

      const ctxHandover = document.getElementById('handoverChart').getContext('2d');
      new Chart(ctxHandover, {
        type: 'bar',
        data: {
          labels: handoverLabels,
          datasets: [{
            label: 'Handovers Completed',
            data: handoverValues,
            backgroundColor: colorSage,
            borderColor: colorSageLL,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(255, 255, 255, 0.05)'
              },
              ticks: {
                color: colorCream
              }
            },
            x: {
              grid: {
                color: 'rgba(255, 255, 255, 0.05)'
              },
              ticks: {
                color: colorCream
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                color: colorCream
              }
            }
          }
        }
      });
    }
  });
</script>

<?= $this->endSection() ?>