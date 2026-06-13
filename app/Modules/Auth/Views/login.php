<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
      <div class="card border-secondary border-opacity-10 p-4 p-md-5" style="background: var(--color-bg-card);">

        <!-- Logo/Branding Header -->
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center gap-2 mb-3 justify-content-center">
            <span class="logo-mark d-inline-block align-middle"></span>
            <span class="d-inline-block align-middle font-monospace text-uppercase fs-5" style="font-weight: 500; letter-spacing: 0.1em; color: var(--color-text-main);">ClearBay</span>
          </div>
          <p class="text-secondary mb-0">Clear the Bay. Free the Crew.</p>
        </div>

        <!-- Login Form -->
        <form action="<?= url_to('auth.login.submit') ?>" method="POST" id="loginForm" novalidate>
          <?= csrf_field() ?>

          <!-- Error Alert -->
          <?php if (session()->has('error')) : ?>
            <div class="alert alert-danger mb-4" role="alert">
              <?= (string) esc(session()->get('error')) ?>
            </div>
          <?php endif; ?>

          <!-- Email Input (Floating Label) -->
          <div class="form-floating mb-4">
            <input type="email"
              name="email"
              id="email"
              class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 <?= session('errors.email') ? 'is-invalid' : '' ?>"
              placeholder="name@clearbay.com"
              value="<?= (string) esc(old('email')) ?>"
              autocomplete="email"
              inputmode="email"
              required>
            <label for="email">Email Address *</label>
            <?php if (session('errors.email')) : ?>
              <div class="invalid-feedback">
                <?= (string) esc(session('errors.email')) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Password Input (Floating Label) -->
          <div class="form-floating mb-4 position-relative">
            <input type="password"
              name="password"
              id="password"
              class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-25 pe-5 <?= session('errors.password') ? 'is-invalid' : '' ?>"
              placeholder="Password"
              autocomplete="current-password"
              required>
            <label for="password">Password *</label>
            <button type="button"
              id="togglePassword"
              class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2 touch-target"
              aria-label="Toggle password visibility"
              style="z-index: 5; font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.3rem 0.6rem;">Show</button>
            <?php if (session('errors.password')) : ?>
              <div class="invalid-feedback">
                <?= (string) esc(session('errors.password')) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Submit Button -->
          <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center touch-target">
            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
            <span id="submitText">Sign In</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggle
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');

    if (passwordInput && toggleButton) {
      toggleButton.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleButton.textContent = type === 'password' ? 'Show' : 'Hide';
      });
    }

    // 2. Submit Loading State
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    const btnText = document.getElementById('submitText');

    if (form && btn && spinner && btnText) {
      form.addEventListener('submit', () => {
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Signing In...';
      });
    }
  });
</script>

<?= $this->endSection() ?>