<?php

/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var string $metaImage
 */
$metaImage = $metaImage ?? base_url('assets/images/brand.png');
$pageTitle  = $pageTitle  ?? 'ClearBay';
$metaDescription = $metaDescription ?? 'ClearBay — Real-Time Ambulance Off-Load Management';
$canonicalUrl    = $canonicalUrl    ?? base_url();
$robotsTag       = $robotsTag       ?? 'noindex, nofollow';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="<?= (string) esc($robotsTag) ?>">
  <meta name="description" content="<?= (string) esc($metaDescription) ?>">
  <link rel="canonical" href="<?= (string) esc($canonicalUrl) ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= (string) esc($canonicalUrl) ?>">
  <meta property="og:title" content="<?= (string) esc($pageTitle) ?>">
  <meta property="og:description" content="<?= (string) esc($metaDescription) ?>">
  <meta property="og:image" content="<?= (string) esc($metaImage) ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@ClearBayHealth">
  <meta name="twitter:title" content="<?= (string) esc($pageTitle) ?>">
  <meta name="twitter:description" content="<?= (string) esc($metaDescription) ?>">
  <meta name="twitter:image" content="<?= (string) esc($metaImage) ?>">
  <meta name="twitter:image:alt" content="ClearBay — Real-Time Ambulance Off-Load Management">

  <title><?= (string) esc($pageTitle) ?></title>

  <!-- Bootstrap 5 CSS (locally vendored) -->
  <link href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">

  <!-- Preconnect & Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=IBM+Plex+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>

<body>

  <?php
  $session = session();
  $isLoggedIn = $session->get('is_logged_in');
  $userRole   = $session->get('user_role');
  $homeUrl    = url_to('home');

  $dashboardRoute = 'auth.login';
  if ($isLoggedIn) {
    switch ($userRole) {
      case 'nurse':
      case 'hospital_admin':
        $dashboardRoute = 'hospital.dashboard';
        break;
      case 'paramedic':
        $dashboardRoute = 'ambulance.home';
        break;
      case 'dispatcher':
        $dashboardRoute = 'dispatcher.index';
        break;
      case 'admin':
        $dashboardRoute = 'admin.dashboard';
        break;
    }
  }
  ?>

  <!-- ━━━━ BS5 NAVBAR + OFF-CANVAS ━━━━ -->
  <nav class="navbar navbar-dark fixed-top border-bottom border-secondary border-opacity-10 py-3" style="background: var(--color-bg-main);">
    <div class="container-fluid p-0" style="width: 88%; margin: 0 auto;">
      <!-- Brand -->
      <a class="navbar-brand p-0" href="<?= $homeUrl ?>">
        <span class="logo-mark d-inline-block align-middle"></span>
        <span class="d-inline-block align-middle ms-2 font-monospace text-uppercase" style="font-weight: 500; letter-spacing: 0.1em; color: var(--color-text-main);">ClearBay</span>
      </a>

      <!-- Desktop nav (lg+) -->
      <div class="d-none d-lg-flex align-items-center gap-3">
        <ul class="navbar-nav me-3 flex-row align-items-center gap-1">
          <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>#problem">Problem</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>#how">Solution</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>#serve">Who We Serve</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>#evidence">Research</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>#signup">Contact</a></li>
          <?php if ($isLoggedIn): ?>
            <li class="nav-item"><a class="nav-link" href="<?= url_to($dashboardRoute) ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-danger" href="<?= url_to('auth.logout') ?>">Sign Out</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= url_to('auth.login') ?>">Login</a></li>
          <?php endif; ?>
        </ul>
        <a href="<?= $homeUrl ?>#signup" class="btn btn-primary btn-sm nav-btn">Join Pilot</a>
      </div>

      <!-- Hamburger toggle (below lg) — touch-friendly -->
      <button class="navbar-toggler d-lg-none border-0 p-2 touch-target" type="button" data-bs-toggle="offcanvas" data-bs-target="#navOffcanvas" aria-controls="navOffcanvas" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>

  <!-- Offcanvas mobile nav -->
  <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="navOffcanvas" aria-labelledby="navOffcanvasLabel" style="background: var(--color-bg-main); border-right: 1px solid var(--color-border);">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-10">
      <span class="offcanvas-title font-monospace" id="navOffcanvasLabel" style="color: var(--color-text-main);">ClearBay</span>
      <button type="button" class="btn-close btn-close-white touch-target" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
      <ul class="navbar-nav flex-grow-1 gap-2">
        <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= $homeUrl ?>#problem" data-bs-dismiss="offcanvas">Problem</a></li>
        <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= $homeUrl ?>#how" data-bs-dismiss="offcanvas">Solution</a></li>
        <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= $homeUrl ?>#serve" data-bs-dismiss="offcanvas">Who We Serve</a></li>
        <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= $homeUrl ?>#evidence" data-bs-dismiss="offcanvas">Research</a></li>
        <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= $homeUrl ?>#signup" data-bs-dismiss="offcanvas">Contact</a></li>
        <?php if ($isLoggedIn): ?>
          <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= url_to($dashboardRoute) ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link fs-5 py-3 text-danger" href="<?= url_to('auth.logout') ?>">Sign Out</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link fs-5 py-3" href="<?= url_to('auth.login') ?>">Login</a></li>
          <li class="nav-item mt-4"><a href="<?= $homeUrl ?>#signup" class="btn btn-primary w-100 py-3">Join Pilot</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- Spacer for fixed navbar -->
  <div style="height: 72px;"></div>

  <!-- Flash Messages -->
  <div class="container mt-3">
    <?= $this->include('partials/flash_messages') ?>
  </div>

  <!-- Main Content -->
  <?= $this->renderSection('content') ?>

  <!-- ━━━━ FOOTER ━━━━ -->
  <footer class="border-top border-secondary border-opacity-10 py-5 mt-auto" style="background: var(--color-bg-main);">
    <div class="d-flex flex-wrap align-items-center gap-3" style="width: 88%; margin: 0 auto;">
      <a href="<?= $homeUrl ?>" class="text-decoration-none d-flex align-items-center gap-2">
        <span class="logo-mark d-inline-block"></span>
        <span class="font-monospace text-uppercase" style="font-weight: 500; letter-spacing: 0.1em; color: var(--color-text-main);">ClearBay</span>
      </a>
      <span class="font-monospace small text-secondary fst-italic" style="font-family: var(--font-serif);">Clear the Bay. Free the Crew. Save the Next Life.</span>
      <ul class="d-flex flex-wrap list-unstyled gap-3 ms-auto mb-0">
        <li><a href="<?= $homeUrl ?>#problem" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">Problem</a></li>
        <li><a href="<?= $homeUrl ?>#how" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">Solution</a></li>
        <li><a href="<?= $homeUrl ?>#serve" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">Who We Serve</a></li>
        <li><a href="<?= $homeUrl ?>#evidence" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">Research</a></li>
        <li><a href="mailto:info@clearbayhealthke.com" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">info@clearbayhealthke.com</a></li>
        <li><a href="#" class="font-monospace small text-uppercase text-decoration-none text-secondary" style="letter-spacing: 0.05em;">© 2026 ClearBay Health Ltd</a></li>
      </ul>
    </div>
  </footer>

  <!-- Bootstrap 5 JS Bundle (deferred) -->
  <script src="<?= base_url('assets/bootstrap/js/bootstrap.bundle.min.js') ?>" defer></script>
  <script src="<?= base_url('js/app.js') ?>" defer></script>
</body>

</html>