<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var \App\Modules\Hospital\Entities\Hospital $hospital
 * @var \App\Modules\Auth\Entities\User|null $user
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
            <h1 class="s-title m-0 mb-2"><?= isset($user) ? 'Edit User' : 'Add User' ?></h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url_to('hospital.users.list') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">Back to Users</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->has('errors') && is_array(session('errors'))) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach (session('errors') as $err) : ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (session()->has('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- User Form -->
    <div class="card blueprint-card p-4 reveal">
        <?php if (isset($user)) : ?>
            <?php $formAction = url_to('hospital.users.update', $user->id); ?>
        <?php else : ?>
            <?php $formAction = url_to('hospital.users.create'); ?>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <!-- Name -->
            <div class="mb-3 form-floating">
                <input type="text"
                    name="name"
                    id="name"
                    class="form-control <?= session()->has('errors') && isset(session('errors')['name']) ? 'is-invalid' : '' ?>"
                    placeholder="Full Name"
                    value="<?= esc(old('name', $user->name ?? '')) ?>"
                    required
                    minlength="3"
                    maxlength="255"
                    style="min-height: 56px;">
                <label for="name">Full Name</label>
                <?php if (session()->has('errors') && isset(session('errors')['name'])) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors')['name']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="mb-3 form-floating">
                <input type="email"
                    name="email"
                    id="email"
                    class="form-control <?= session()->has('errors') && isset(session('errors')['email']) ? 'is-invalid' : '' ?>"
                    placeholder="Email Address"
                    value="<?= esc(old('email', $user->email ?? '')) ?>"
                    required
                    maxlength="255"
                    style="min-height: 56px;">
                <label for="email">Email Address</label>
                <?php if (session()->has('errors') && isset(session('errors')['email'])) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors')['email']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Role -->
            <div class="mb-3 form-floating">
                <select name="role"
                    id="role"
                    class="form-select <?= session()->has('errors') && isset(session('errors')['role']) ? 'is-invalid' : '' ?>"
                    required
                    style="min-height: 56px;">
                    <option value="">-- Select Role --</option>
                    <option value="nurse" <?= old('role', $user->role ?? '') === 'nurse' ? 'selected' : '' ?>>Nurse</option>
                    <option value="hospital_admin" <?= old('role', $user->role ?? '') === 'hospital_admin' ? 'selected' : '' ?>>Hospital Administrator</option>
                </select>
                <label for="role">User Role</label>
                <?php if (session()->has('errors') && isset(session('errors')['role'])) : ?>
                    <div class="invalid-feedback"><?= esc(session('errors')['role']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Active Status (edit only) -->
            <?php if (isset($user)) : ?>
                <div class="mb-3 form-floating">
                    <select name="active"
                        id="active"
                        class="form-select <?= session()->has('errors') && isset(session('errors')['active']) ? 'is-invalid' : '' ?>"
                        required
                        style="min-height: 56px;">
                        <option value="1" <?= old('active', $user->active ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= old('active', $user->active ?? 1) == 0 ? 'selected' : '' ?>>Suspended</option>
                    </select>
                    <label for="active">Account Status</label>
                    <?php if (session()->has('errors') && isset(session('errors')['active'])) : ?>
                        <div class="invalid-feedback"><?= esc(session('errors')['active']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- New Password (edit only) -->
            <?php if (isset($user)) : ?>
                <div class="mb-3">
                    <label for="new_password" class="form-label text-cream">New Password <small class="text-muted">(optional)</small></label>
                    <div class="input-group">
                        <input type="password"
                            name="new_password"
                            id="new_password"
                            class="form-control <?= session()->has('errors') && isset(session('errors')['new_password']) ? 'is-invalid' : '' ?>"
                            placeholder="Leave blank to keep current password"
                            minlength="6"
                            autocomplete="new-password"
                            style="min-height: 56px;">
                        <button type="button"
                            class="btn btn-outline-secondary"
                            id="generatePasswordBtn"
                            style="min-height: 56px; min-width: 48px;"
                            aria-label="Generate random password">🔑</button>
                        <button type="button"
                            class="btn btn-outline-secondary"
                            id="togglePasswordBtn"
                            style="min-height: 56px; min-width: 48px;"
                            aria-label="Toggle password visibility">👁️</button>
                    </div>
                    <?php if (session()->has('errors') && isset(session('errors')['new_password'])) : ?>
                        <div class="text-danger small mt-1"><?= esc(session('errors')['new_password']) ?></div>
                    <?php endif; ?>
                    <div class="form-text text-muted">Leave blank to keep current password unchanged. Minimum 6 characters.</div>
                </div>
            <?php endif; ?>

            <!-- Submit -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary flex-fill" style="min-height: 48px;">
                    <?= isset($user) ? 'Update User' : 'Create User' ?>
                </button>
                <a href="<?= url_to('hospital.users.list') ?>" class="btn btn-outline-secondary" style="min-height: 48px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('new_password');
        const generateBtn = document.getElementById('generatePasswordBtn');
        const toggleBtn = document.getElementById('togglePasswordBtn');

        // Generate random password (12 chars, mixed case + digits)
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let password = '';
                for (let i = 0; i < 12; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }
                passwordInput.value = password;
            });
        }

        // Toggle password visibility
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleBtn.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    });
</script>

<?= $this->endSection() ?>