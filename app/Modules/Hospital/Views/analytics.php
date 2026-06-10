<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 * @var array $analytics
 * @var string $range
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container admin-page">
  <!-- Inner Navigation Back-Link -->
  <div class="mb-4 reveal">
    <a href="<?= url_to('hospital.dashboard') ?>" class="mono-label text-decoration-none admin-back">← Back to Dashboard</a>
  </div>

  <!-- Header with Date Filters & Export -->
  <div class="row align-items-end g-3 mb-5 reveal">
    <div class="col-md-6">
      <div class="blueprint-header">
        <div class="s-label mb-1">
          <div class="s-label-line"></div>
          <span class="s-label-text">Analytics Overview</span>
        </div>
        <h1 class="s-title"><?= esc($hospital->name) ?></h1>
      </div>
    </div>
    <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end align-items-center">
      <a href="<?= url_to('hospital.analytics.export') ?>" class="btn btn-primary px-3">Export Report (PDF)</a>
      <form action="<?= url_to('hospital.analytics') ?>" method="GET" id="rangeForm" class="m-0">
        <div class="input-group">
          <span class="input-group-text bg-dark border-secondary border-opacity-20 text-muted mono-label">Range:</span>
          <select name="range" class="form-select bg-dark text-cream border-secondary border-opacity-20" onchange="document.getElementById('rangeForm').submit();">
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
      <div class="card blueprint-card p-4 h-100">
        <h3 class="admin-card-heading mb-3">Avg Off-Load Wait Time (Minutes)</h3>
        <div class="chart-container">
          <canvas id="waitChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Bar Chart: Total Handovers -->
    <div class="col-lg-6">
      <div class="card blueprint-card p-4 h-100">
        <h3 class="admin-card-heading mb-3">Total Handovers Completed</h3>
        <div class="chart-container">
          <canvas id="handoverChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Table: EMS Provider performance summary -->
  <div class="card blueprint-card p-4 reveal">
    <h3 class="admin-card-heading mb-4">Performance summary by EMS Provider</h3>
    <div class="table-responsive">
      <table class="table queue-table align-middle">
        <thead>
          <tr class="mono-label text-muted">
            <th>EMS Provider</th>
            <th>Total Handovers Completed</th>
            <th>Average Wait Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($analytics['provider_performance'])) : ?>
            <tr>
              <td colspan="3" class="text-center text-muted py-4">No completed handover logs found in selected range.</td>
            </tr>
          <?php else : ?>
            <?php foreach ($analytics['provider_performance'] as $row) : ?>
              <tr>
                <td class="fw-bold"><?= esc($row['provider']) ?></td>
                <td><?= esc($row['total_handovers']) ?></td>
                <td>
                  <span class="badge <?= (int)$row['avg_wait'] >= 30 ? 'bg-danger' : ((int)$row['avg_wait'] >= 15 ? 'bg-warning text-dark' : 'bg-success') ?>">
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
          label: 'Pre-ClearBay Baseline (60 min)',
          data: Array(waitLabels.length).fill(60),
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
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: colorCream }
          },
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: colorCream }
          }
        },
        plugins: {
          legend: { labels: { color: colorCream } }
        }
      }
    });

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
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: colorCream }
          },
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: colorCream }
          }
        },
        plugins: {
          legend: { labels: { color: colorCream } }
        }
      }
    });
  });
</script>

<?= $this->endSection() ?>
