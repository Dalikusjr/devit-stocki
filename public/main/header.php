<?php
// ==========================
// INITIALISATION PHP / TRAITEMENTS
// ==========================
$pageActive = preg_replace('/[^a-z]/', '', $_GET['page'] ?? 'dashboard') ?: 'dashboard';
?>
<!doctype html>

<html
  lang="fr"
  class="light-style layout-menu-fixed layout-footer-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="horizontal-menu-template">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Dashboard</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link
    rel="icon"
    type="image/x-icon"
    href="assets/img/favicon/favicon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css" />
  <!-- Icons -->
  <link rel="stylesheet" href="assets/vendor/fonts/tabler-icons.css" />

  <!-- Core CSS -->
  <link
    rel="stylesheet"
    href="assets/vendor/css/rtl/core.css"
    class="template-customizer-core-css" />
  <link
    rel="stylesheet"
    href="assets/vendor/css/rtl/theme-default.css"
    class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link
    rel="stylesheet"
    href="assets/vendor/libs/node-waves/node-waves.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link
    rel="stylesheet"
    href="assets/vendor/libs/apex-charts/apex-charts.css" />
  <!-- select2 : style du template (identique aux autres champs) -->
  <link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />

  </script>

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <!-- <script src="assets/vendor/js/template-customizer.js"></script> -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="assets/js/config.js"></script>
  <script src="assets/vendor/libs/@form-validation/umd/bundle/popular.min.js"></script>
  <script src="assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js"></script>
  <script src="assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js"></script>
  <script>
    // Niveau d'accès de l'utilisateur connecté (affichage uniquement : le serveur vérifie aussi)
    window.IS_ADMIN = <?= \App\Helpers\Helpers::isAdmin() ? 'true' : 'false' ?>;
  </script>
</head>

<body>
  <!-- Layout wrapper -->
  <div
    class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">
      <!-- Navbar -->

      <nav
        class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme"
        id="layout-navbar">
        <div class="container-xxl">
          <div class="navbar-brand app-brand demo d-flex align-items-center py-0 me-3 me-xl-4">
            <!-- Logo officiel DevIt | Stocki (brand-header.png = brand.png recadré) -->
            <a href="p-dashboard" class="app-brand-link devit-brand" aria-label="DevIt Stocki : tableau de bord">
              <img
                src="assets/img/branding/brand-header.png"
                class="devit-brand-logo"
                width="122" height="48"
                alt="DevIt | Stocki" />
            </a>
            <a
              href="javascript:void(0);"
              class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
              <i class="ti ti-x ti-sm align-middle"></i>
            </a>
          </div>

          <div
            class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a
              class="nav-item nav-link px-0 me-xl-4"
              href="javascript:void(0)">
              <i class="ti ti-menu-3 ti-flashing-hover ti-sm"></i>
            </a>
          </div>

          <!-- Logo sur petits écrans (le bloc ci-dessus part dans le menu latéral sous 1200 px) -->
          <a href="p-dashboard" class="devit-brand d-xl-none" aria-label="DevIt Stocki : tableau de bord">
            <img
              src="assets/img/branding/brand-header.png"
              class="devit-brand-logo"
              width="92" height="36"
              alt="DevIt | Stocki" />
          </a>

          <div
            class="navbar-nav-right d-flex align-items-center"
            id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Notification -->
              <li
                class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a
                  class="nav-link dropdown-toggle hide-arrow"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-expanded="false">
                  <i class="ti ti-bell ti-tada-hover ti-md"></i>
                  <span
                    class="badge bg-danger rounded-pill badge-notifications d-none" id="notifBadge"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                  <li class="dropdown-menu-header border-bottom">
                    <div
                      class="dropdown-header d-flex align-items-center py-3">
                      <h5 class="text-body mb-0 me-auto">Notification</h5>
                      <span class="badge bg-label-primary" id="notifCount"></span>
                    </div>
                  </li>
                  <li
                    class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush" id="notifList">
                      <li class="list-group-item text-center text-muted">Chargement…</li>
                    </ul>
                  </li>
                  <li class="dropdown-menu-footer border-top">
                    <a
                      href="p-dashboard"
                      class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                      Voir le tableau de bord
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ Notification -->

              <!-- User -->
              <?php
              $currentUser = \App\Helpers\Helpers::currentUser();
              $initiales = htmlspecialchars(\App\Helpers\Helpers::initials($currentUser));
              $nomComplet = htmlspecialchars(trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')));
              $fonction = htmlspecialchars($currentUser['role'] ?? '');
              ?>
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                  class="nav-link dropdown-toggle hide-arrow"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown"
                  title="<?= $nomComplet ?>"
                  aria-label="Menu du compte">
                  <div class="avatar avatar-online">
                    <span class="avatar-initial rounded-circle bg-label-primary fw-semibold"><?= $initiales ?></span>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="p-profil">
                      <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <span class="avatar-initial rounded-circle bg-label-primary fw-semibold"><?= $initiales ?></span>
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-medium d-block"><?= $nomComplet ?></span>
                          <small class="text-muted d-block"><?= $fonction ?></small>
                          <?php if (!empty($currentUser['is_admin'])): ?>
                            <span class="badge bg-label-primary mt-1"><i class="ti ti-shield-check me-1"></i>Administrateur</span>
                          <?php endif ?>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="p-profil">
                      <i class="ti ti-user-check me-2 ti-sm"></i>
                      <span class="align-middle">Mon profil</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="p-parametres">
                      <i class="ti ti-settings me-2 ti-sm"></i>
                      <span class="align-middle">Configuration</span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <form action="auth/logout.php" method="POST">
                      <button type="submit" class="dropdown-item">
                        <i class="ti ti-logout me-2 ti-sm"></i>
                        <span class="align-middle">Déconnexion</span>
                      </button>
                    </form>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </div>
      </nav>

      <!-- / Navbar -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Menu -->
          <aside
            id="layout-menu"
            class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
            <div class="container-xxl d-flex h-100">
              <ul class="menu-inner">
                <!-- Dashboards -->
                <li class="menu-item <?= $pageActive == 'dashboard' ? 'active' : '' ?>">
                  <a href="p-dashboard" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div data-i18n="Dashboard">Dashboard</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'projects' ? 'active' : '' ?>">
                  <a href="p-projects" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-devices-code"></i>
                    <div data-i18n="Projets Dev">Projets Dev</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'paiments' ? 'active' : '' ?>">
                  <a href="p-paiments" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-cash"></i>
                    <div data-i18n="Paiements">Paiements</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'maintains' ? 'active' : '' ?>">
                  <a href="p-maintains" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-table-options"></i>
                    <div data-i18n="Maintenance">Maintenance</div>
                  </a>
                </li>
                <li class="menu-item ">
                  <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-coin"></i>

                    <div data-i18n="Stocki">Stocki</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="p-abonn" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-package"></i>
                        <div data-i18n="Abonnement">Abonnement</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="p-abonnvie" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-star"></i>
                        <div data-i18n="Licence à vie">Licence à vie</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="p-clients" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-user-dollar"></i>
                    <div data-i18n="Clients">Clients</div>
                  </a>
                </li>
                <?php if (\App\Helpers\Helpers::isAdmin()): ?>
                <li class="menu-item <?= $pageActive == 'users' ? 'active' : '' ?>">
                  <a href="p-users" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-user-star"></i>
                    <div data-i18n="Utilisateurs">Utilisateurs</div>
                  </a>
                </li>
                <?php endif ?>
                <li class="menu-item <?= $pageActive == 'stats' ? 'active' : '' ?>">
                  <a href="p-stats" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-chart-dots-filled"></i>
                    <div data-i18n="Statistique/Gain">Statistique/Gain</div>
                  </a>
                </li>
              </ul>
            </div>
          </aside>
          <!-- / Menu -->

          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">