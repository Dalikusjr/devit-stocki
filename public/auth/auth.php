<?php
require_once "../../config.php";

use App\Helpers\Helpers;
// ========================== 
// INITIALISATION PHP
// ==========================
session_start();


// Adresse propre : la page de connexion s'affiche sous …/public/login
if (basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)) === 'auth.php') {
  Helpers::redirect('../login');
}

if (isset($_SESSION['username'])) {
  Helpers::redirect('p-dashboard');
}
?>
<!DOCTYPE html>

<html
  lang="fr"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="horizontal-menu-template">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Connexion · DevIt Stocki</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link
    rel="shortcut icon"
    href="assets/img/favicon/favicon.png"
    type="image/x-icon" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="assets/vendor/fonts/tabler-icons.css" />

  <!-- Core CSS -->
  <link
    rel="stylesheet"
    href="assets/vendor/css/rtl/core.css"
    class="template-customizer-core-css" />

  <link
    rel="stylesheet"
    href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />

  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="assets/js/config.js"></script>
</head>

<body>
  <!-- Content -->
  <style>
    /* Page de connexion : panneau aux couleurs du logo (bleu DevIt #024f8e, orange Stocki) */
    .devit-auth-cover {
      position: relative;
      overflow: hidden;
      margin: 1.5rem;
      border-radius: 1.25rem;
      min-height: calc(100vh - 3rem);
      padding: 3.5rem;
      color: #fff;
      background: linear-gradient(140deg, #072f5c 0%, #024f8e 48%, #2a78d6 100%);
    }
    .devit-auth-cover .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.55;
      pointer-events: none;
    }
    .devit-auth-cover .blob-orange { width: 260px; height: 260px; background: #ff9f2e; right: -110px; top: -110px; opacity: 0.3; }
    .devit-auth-cover .blob-sky { width: 380px; height: 380px; background: #4fb3ff; left: -120px; bottom: -140px; opacity: 0.45; }
    .devit-auth-content { position: relative; max-width: 560px; }
    .devit-auth-kicker {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: .8125rem; font-weight: 500;
      padding: .35rem .75rem; border-radius: 2rem;
      background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25);
    }
    .devit-auth-content h2 { color: #fff; font-size: 2rem; line-height: 1.25; margin: 1.25rem 0 .75rem; }
    .devit-auth-content .lead { color: rgba(255, 255, 255, .82); font-size: 1rem; }
    .devit-feature {
      display: flex; gap: .9rem; align-items: flex-start;
      padding: .9rem 1rem; border-radius: .9rem;
      background: rgba(255, 255, 255, .1);
      border: 1px solid rgba(255, 255, 255, .18);
      -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
    }
    .devit-feature + .devit-feature { margin-top: .75rem; }
    .devit-feature .ico {
      flex: 0 0 auto; width: 40px; height: 40px; border-radius: .7rem;
      display: inline-flex; align-items: center; justify-content: center;
      background: rgba(255, 255, 255, .16); color: #fff; font-size: 1.25rem;
    }
    .devit-feature .ico.accent { background: #f59a23; color: #fff; }
    .devit-feature strong { display: block; color: #fff; font-weight: 600; }
    .devit-feature span { color: rgba(255, 255, 255, .78); font-size: .875rem; }
    .devit-auth-form .brand-logo { height: 56px; width: auto; }
    .devit-auth-form .form-control:focus { box-shadow: none; }
    .devit-auth-footer { font-size: .8125rem; }
    @media (prefers-reduced-transparency: reduce) {
      .devit-feature { -webkit-backdrop-filter: none; backdrop-filter: none; background: rgba(0, 0, 0, .18); }
    }
  </style>

  <div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <!-- Présentation de l'application -->
      <div class="d-none d-lg-flex col-lg-7 p-0">
        <div class="devit-auth-cover d-flex align-items-center w-100">
          <span class="blob blob-orange" aria-hidden="true"></span>
          <span class="blob blob-sky" aria-hidden="true"></span>
          <div class="devit-auth-content">
            <span class="devit-auth-kicker"><i class="ti ti-building-skyscraper"></i>Application interne DevIt</span>
            <h2>Pilotez vos projets, paiements et abonnements au même endroit.</h2>
            <p class="lead mb-4">Suivi des échéances, rappels de renouvellement et gains en temps réel, pour DevIt et Stocki.</p>

            <div class="devit-feature">
              <span class="ico"><i class="ti ti-code"></i></span>
              <div><strong>Projets Dev</strong><span>Coûts, avances, tranches de paiement et accès sécurisés.</span></div>
            </div>
            <div class="devit-feature">
              <span class="ico accent"><i class="ti ti-package"></i></span>
              <div><strong>Stocki</strong><span>Abonnements mensuels, trimestriels, annuels et licences à vie.</span></div>
            </div>
            <div class="devit-feature">
              <span class="ico"><i class="ti ti-bell-ringing"></i></span>
              <div><strong>Rappels automatiques</strong><span>Renouvellements à 1 mois, 2 semaines et 1 semaine, impayés en retard.</span></div>
            </div>
            <div class="devit-feature">
              <span class="ico"><i class="ti ti-chart-dots"></i></span>
              <div><strong>Statistiques et gains</strong><span>Par mois, trimestre et année, Dev et Stocki.</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Connexion -->
      <div class="d-flex flex-column col-12 col-lg-5 p-sm-5 p-4 devit-auth-form">
        <div class="w-px-400 mx-auto my-auto w-100" style="max-width: 400px">
          <img class="brand-logo mb-5" src="assets/img/branding/brand-header.png" alt="DevIt | Stocki" />

          <h3 class="mb-1">Bienvenue sur DevIt Stocki 👋</h3>
          <p class="text-muted mb-4">Connectez-vous pour accéder à votre espace.</p>

          <?php if (!empty($_SESSION['error'])): ?>
            <div id="msgErreur" class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
              <i class="ti ti-alert-circle ti-sm"></i>
              <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
          <?php endif; ?>
          <?php $ancienEmail = $_SESSION['old_email'] ?? ''; unset($_SESSION['old_email']); ?>

          <form id="formAuthentication" class="mb-4" action="auth/login.php" method="POST" novalidate>
            <div class="mb-3">
              <label for="email" class="form-label">E-mail</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  name="email"
                  value="<?= htmlspecialchars($ancienEmail) ?>"
                  placeholder="vous@exemple.com"
                  autocomplete="username"
                  <?= $ancienEmail === '' ? 'autofocus' : '' ?> />
              </div>
            </div>
            <div class="mb-3 pb-2 form-password-toggle"><!-- .mb-3 : bloc attendu par la validation (rowSelector) -->
              <label class="form-label" for="password">Mot de passe</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  autocomplete="current-password"
                  <?= $ancienEmail !== '' ? 'autofocus' : '' ?> />
                <span class="input-group-text cursor-pointer" role="button" aria-label="Afficher le mot de passe">
                  <i class="ti ti-eye-off"></i>
                </span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" id="loginBtn">
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              <i class="ti ti-login-2"></i>
              <span class="label">Se connecter</span>
            </button>
          </form>

          <p class="text-muted small mb-0">
            <i class="ti ti-help-circle me-1"></i>Mot de passe oublié ? Contactez un administrateur de l'application.
          </p>
        </div>

        <div class="text-center text-muted devit-auth-footer mt-4">
          © <?= date('Y') ?> <span class="fw-semibold">DevIt | Stocki</span> · v1.0.2
        </div>
      </div>
    </div>
  </div>

  <!-- Vendors JS -->
  <script src="assets/vendor/libs/@form-validation/umd/bundle/popular.min.js"></script>
  <script src="assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js"></script>
  <script src="assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js"></script>

  <!-- Main JS -->
  <script src="assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="assets/js/pages-auth.js"></script>
</body>

</html>