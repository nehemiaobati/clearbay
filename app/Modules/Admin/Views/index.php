<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="container my-5">
    <div class="blueprint-header reveal">
        <div class="s-label">
            <div class="s-label-line"></div>
            <span class="s-label-text">Module</span>
        </div>
        <h1 class="s-title">Admin <span class="ital dim">Module</span></h1>
    </div>
    <div class="card blueprint-card p-4 mt-4">
        <p class="text-muted mb-0">Use the navigation or <a href="<?= url_to('admin.dashboard') ?>" class="text-decoration-none">return to the dashboard</a>.</p>
    </div>
</div>
<?= $this->endSection() ?>