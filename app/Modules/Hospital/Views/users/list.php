<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var array $users
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container admin-page">
    <!-- Blueprint Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center blueprint-header reveal mb-4">
        <div>
            <div class="s-label">
                <div class="s-label-line"></div>
                <span class="s-label-text"><?= esc($hospital->name) ?></span>
            </div>
            <h1 class="s-title m-0 mb-2">Manage Users</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url_to('hospital.dashboard') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">Dashboard</a>
            <a href="<?= url_to('hospital.users.new') ?>" class="btn btn-primary" style="min-height: 48px;">Add User +</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->has('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (session()->has('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card blueprint-card p-4 reveal">
        <div class="table-responsive">
            <table class="table queue-table align-middle">
                <thead>
                    <tr class="mono-label text-muted">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No users registered for this hospital.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($users as $u) : ?>
                            <tr>
                                <td class="fw-bold text-cream"><?= esc($u->name) ?></td>
                                <td><?= esc($u->email) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($u->role) ?></span></td>
                                <td>
                                    <span class="badge <?= $u->active === 1 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $u->active === 1 ? 'Active' : 'Suspended' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= url_to('hospital.users.edit', $u->id) ?>" class="btn btn-sm btn-outline-secondary" style="min-height: 48px; min-width: 48px;">Edit</a>
                                        <a href="<?= url_to('hospital.users.delete', $u->id) ?>"
                                            class="btn btn-sm btn-danger"
                                            style="min-height: 48px; min-width: 48px;"
                                            onclick="return confirm('Are you sure you want to deactivate this user?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>