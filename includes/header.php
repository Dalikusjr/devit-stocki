<?php
// ==========================
// INITIALISATION PHP / TRAITEMENTS
// ==========================
$pageActive = $_GET['page'] ?? 'dash';
?>
<!doctype html>

<html
  lang="fr"
  class="layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
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
    href="../assets/img/favicon/favicon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css" />
  <!-- Icons -->
  <!-- <link rel="stylesheet" href="../assets/vendor/fonts/fontawesome.css" /> -->
  <link rel="stylesheet" href="../assets/vendor/fonts/tabler-icons.css" />
  <!-- <link rel="stylesheet" href="../assets/vendor/fonts/flag-icons.css" /> -->

  <!-- Core CSS -->
  <link
    rel="stylesheet"
    href="../assets/vendor/css/rtl/core.css"
    class="template-customizer-core-css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/css/rtl/theme-default.css"
    class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/node-waves/node-waves.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/typeahead-js/typeahead.css" />
  <link
    rel="stylesheet"
    href="../assets/vendor/libs/apex-charts/apex-charts.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>

</script>

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <!-- <script src="../assets/vendor/js/template-customizer.js"></script> -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>
  <script src="../assets/vendor/libs/@form-validation/umd/bundle/popular.min.js"></script>
  <script src="../assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js"></script>
  <script src="../assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js"></script>
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
          <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <img
              src="../assets/img/branding/brand.png"
              class="w-60 d-xl-none"
              alt="brand logo" />
            <h1 class="d-none d-xl-flex mt-2">
              DevIt- <span style="color: rgb(82, 82, 224)">Stocki</span>
            </h1>
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
                    class="badge bg-danger rounded-pill badge-notifications">5</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                  <li class="dropdown-menu-header border-bottom">
                    <div
                      class="dropdown-header d-flex align-items-center py-3">
                      <h5 class="text-body mb-0 me-auto">Notification</h5>
                      <a
                        href="javascript:void(0)"
                        class="dropdown-notifications-all text-body"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Tout marquer comme lu"><i class="ti ti-mail-opened fs-4"></i></a>
                    </div>
                  </li>
                  <li
                    class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <img
                                src="../assets/img/avatars/1.png"
                                alt
                                class="h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">Congratulation Lettie 🎉</h6>
                            <p class="mb-0">
                              Won the monthly best seller gold badge
                            </p>
                            <small class="text-muted">1h ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span
                                class="avatar-initial rounded-circle bg-label-danger">CF</span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">Charles Franklin</h6>
                            <p class="mb-0">Accepted your connection</p>
                            <small class="text-muted">12hr ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <img
                                src="../assets/img/avatars/2.png"
                                alt
                                class="h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">New Message ✉️</h6>
                            <p class="mb-0">
                              You have new message from Natalie
                            </p>
                            <small class="text-muted">1h ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span
                                class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-shopping-cart"></i></span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">Whoo! You have new order 🛒</h6>
                            <p class="mb-0">
                              ACME Inc. made new order $1,154
                            </p>
                            <small class="text-muted">1 day ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <img
                                src="../assets/img/avatars/9.png"
                                alt
                                class="h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">
                              Application has been approved 🚀
                            </h6>
                            <p class="mb-0">
                              Your ABC project application has been approved.
                            </p>
                            <small class="text-muted">2 days ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span
                                class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-chart-pie"></i></span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">Monthly report is generated</h6>
                            <p class="mb-0">
                              July monthly financial report is generated
                            </p>
                            <small class="text-muted">3 days ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <img
                                src="../assets/img/avatars/5.png"
                                alt
                                class="h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">Send connection request</h6>
                            <p class="mb-0">
                              Peter sent you connection request
                            </p>
                            <small class="text-muted">4 days ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <img
                                src="../assets/img/avatars/6.png"
                                alt
                                class="h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">New message from Jane</h6>
                            <p class="mb-0">
                              Your have new message from Jane
                            </p>
                            <small class="text-muted">5 days ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                      <li
                        class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span
                                class="avatar-initial rounded-circle bg-label-warning"><i class="ti ti-alert-triangle"></i></span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">CPU is running high</h6>
                            <p class="mb-0">
                              CPU Utilization Percent is currently at 88.63%,
                            </p>
                            <small class="text-muted">5 days ago</small>
                          </div>
                          <div
                            class="flex-shrink-0 dropdown-notifications-actions">
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                            <a
                              href="javascript:void(0)"
                              class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </li>
                  <li class="dropdown-menu-footer border-top">
                    <a
                      href="javascript:void(0);"
                      class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                      Voir toutes les notifications
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ Notification -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                  class="nav-link dropdown-toggle hide-arrow"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img
                      src="../assets/img/avatars/1.png"
                      alt
                      class="h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img
                              src="../assets/img/avatars/1.png"
                              alt
                              class="h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-medium d-block">John Doe</span>
                          <small class="text-muted">Admin</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="ti ti-user-check me-2 ti-sm"></i>
                      <span class="align-middle">Mon Profile</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="ti ti-settings me-2 ti-sm"></i>
                      <span class="align-middle">Configuration</span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="ti ti-logout me-2 ti-sm"></i>
                      <span class="align-middle">Déconnexion</span>
                    </a>
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
                <li class="menu-item <?= $pageActive == 'dash' ? 'active' : '' ?>">
                  <a href="?page=dash" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div data-i18n="Dashboard">Dashboard</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'projects' ? 'active' : '' ?>">
                  <a href="?page=projects" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-devices-code"></i>
                    <div data-i18n="Projets Dev">Projets Dev</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'paiments' ? 'active' : '' ?>">
                  <a href="?page=paiments" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-cash"></i>
                    <div data-i18n="Paiements">Paiements</div>
                  </a>
                </li>
                <li class="menu-item <?= $pageActive == 'maintain' ? 'active' : '' ?>">
                  <a href="?page=maintain" class="menu-link">
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
                      <a href="?page=abonn" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-package"></i>
                        <div data-i18n="Abonnement">Abonnement</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="?page=abonnvie" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-star"></i>
                        <div data-i18n="Licence à vie">Licence à vie</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="?page=clients" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-user-dollar"></i>
                    <div data-i18n="Clients">Clients</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="?page=users" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-user-star"></i>
                    <div data-i18n="Utilisateurs">Utilisateurs</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="#" class="menu-link">
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