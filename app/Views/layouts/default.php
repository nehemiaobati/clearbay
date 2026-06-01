<?php

/**
 * @var string $page_title
 * @var string $meta_description
 * @var string $canonical_url
 * @var string $robots_tag
 * @var string $meta_image
 */
$meta_image = $meta_image ?? base_url('assets/images/brand.png');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="<?= (string) esc($robots_tag) ?>">
  <meta name="description" content="<?= (string) esc($meta_description) ?>">
  <link rel="canonical" href="<?= (string) esc($canonical_url) ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= (string) esc($canonical_url) ?>">
  <meta property="og:title" content="<?= (string) esc($page_title) ?>">
  <meta property="og:description" content="<?= (string) esc($meta_description) ?>">
  <meta property="og:image" content="<?= (string) esc($meta_image) ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@ClearBayHealth">
  <meta name="twitter:title" content="<?= (string) esc($page_title) ?>">
  <meta name="twitter:description" content="<?= (string) esc($meta_description) ?>">
  <meta name="twitter:image" content="<?= (string) esc($meta_image) ?>">
  <meta name="twitter:image:alt" content="ClearBay — Real-Time Ambulance Off-Load Management">

  <title><?= (string) esc($page_title) ?></title>

  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Preconnect and Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=IBM+Plex+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Project Custom CSS Stylesheet -->
  <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

  <!-- ━━━━ NAV ━━━━ -->
  <?php
    $session = session();
    $isLoggedIn = $session->get('is_logged_in');
    $userRole = $session->get('user_role');
  ?>
  <nav id="nav">
    <a href="/" class="logo">
      <div class="logo-mark"></div>
      <span class="logo-name">ClearBay</span>
    </a>
    <ul class="nav-pills">
      <li><a href="/#problem">Problem</a></li>
      <li><a href="/#how">Solution</a></li>
      <li><a href="/#serve">Who We Serve</a></li>
      <li><a href="/#evidence">Research</a></li>
      <li><a href="/#signup">Contact</a></li>
      <?php if ($isLoggedIn): ?>
        <?php
          $dashboardRoute = 'auth.login';
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
        ?>
        <li><a href="<?= url_to($dashboardRoute) ?>">Dashboard</a></li>
        <li><a href="<?= url_to('auth.logout') ?>">Sign Out</a></li>
      <?php else: ?>
        <li><a href="<?= url_to('auth.login') ?>">Login</a></li>
      <?php endif; ?>
    </ul>
    <a href="/#signup" class="nav-btn btn btn-primary">Join Pilot</a>
  </nav>


  <!-- Content Section -->
  <div class="container mt-3">
    <?= $this->include('partials/flash_messages') ?>
  </div>
  <?= $this->renderSection('content') ?>

  <!-- ━━━━ FOOTER ━━━━ -->
  <footer>
    <a href="/" class="logo">
      <div class="logo-mark"></div>
      <span class="logo-name">ClearBay</span>
    </a>
    <span class="footer-tagline">Clear the Bay. Free the Crew. Save the Next Life.</span>
    <ul class="footer-links">
      <li><a href="/#problem">Problem</a></li>
      <li><a href="/#how">Solution</a></li>
      <li><a href="/#serve">Who We Serve</a></li>
      <li><a href="/#evidence">Research</a></li>
      <li><a href="<?= url_to('auth.login') ?>">Login</a></li>
      <li><a href="mailto:info@clearbayhealthke.com">info@clearbayhealthke.com</a></li>
      <li><a href="#">© 2026 ClearBay Health Ltd</a></li>
    </ul>
  </footer>

  <!-- Bootstrap 5 JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <!-- Project JS Application script -->
  <script src="/js/app.js" defer></script>
</body>

</html>