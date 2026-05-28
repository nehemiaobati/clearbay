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

<div class="container admin-page d-flex justify-content-center align-items-center">
  <div class="card blueprint-card p-4 p-md-5 w-100 style-login-card">
    <!-- Logo/Branding Header -->
    <div class="text-center mb-4">
      <div class="logo d-inline-flex align-items-center gap-2 mb-3 justify-content-center">
        <div class="logo-mark"></div>
        <span class="logo-name fs-5">ClearBay</span>
      </div>
      <h2 class="h5 text-muted">Clear the Bay. Free the Crew.</h2>
    </div>

    <!-- Login Form -->
    <form action="<?= url_to('auth.login.submit') ?>" method="POST" id="loginForm" class="form-dark" novalidate>
      <?= csrf_field() ?>

      <!-- Error Alert -->
      <?php if (session()->has('error')) : ?>
        <div class="alert alert-danger mb-3" role="alert">
          <?= (string) esc(session()->get('error')) ?>
        </div>
      <?php endif; ?>

      <!-- Email Floating Input -->
      <div class="form-floating mb-3">
        <input type="email" 
               name="email" 
               id="email" 
               class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
               placeholder="name@clearbay.com" 
               value="<?= (string) esc(old('email')) ?>" 
               required>
        <label for="email">Enter your email address</label>
        <?php if (session('errors.email')) : ?>
          <div class="invalid-feedback">
            <?= (string) esc(session('errors.email')) ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Password Floating Input -->
      <div class="form-floating mb-4 position-relative">
        <input type="password" 
               name="password" 
               id="password" 
               class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" 
               placeholder="Password" 
               required>
        <label for="password">Enter your password</label>
        <button type="button" 
                id="togglePassword" 
                class="btn position-absolute end-0 top-50 translate-middle-y me-2 text-muted toggle-password-btn">
          Show
        </button>
        <?php if (session('errors.password')) : ?>
          <div class="invalid-feedback">
            <?= (string) esc(session('errors.password')) ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Submit Button -->
      <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 d-flex align-items-center justify-content-center fs-6">
        <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
        <span id="submitText">Sign In</span>
      </button>
    </form>
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
        // Prevent multiple submissions
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Signing In...';
      });
    }
  });
</script>

<?= $this->endSection() ?>
