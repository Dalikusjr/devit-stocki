</div>
<!--/ Content -->

<!-- Footer (fixe en bas de l'écran, effet verre comme le header) -->
<?php $piedUtilisateur = \App\Helpers\Helpers::currentUser(); ?>
<footer class="content-footer footer bg-footer-theme devit-footer">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-2 text-nowrap">
        <img src="assets/img/branding/brand-header.png" alt="" class="devit-footer-logo d-none d-sm-block" width="56" height="22">
        <span>© <?= date('Y') ?> <span class="fw-semibold text-heading">DevIt | Stocki</span><span class="d-none d-md-inline"> · Tous droits réservés</span></span>
      </div>

      <nav class="d-none d-lg-flex align-items-center gap-3" aria-label="Liens rapides">
        <a class="footer-link" href="p-dashboard"><i class="ti ti-smart-home ti-xs me-1"></i>Tableau de bord</a>
        <a class="footer-link" href="p-stats"><i class="ti ti-chart-dots ti-xs me-1"></i>Statistiques</a>
        <a class="footer-link" href="p-profil"><i class="ti ti-user ti-xs me-1"></i>Mon profil</a>
      </nav>

      <div class="d-flex align-items-center gap-2 text-nowrap">
        <?php if ($piedUtilisateur): ?>
          <span class="d-none d-md-inline small" title="Utilisateur connecté">
            <span class="devit-footer-dot" aria-hidden="true"></span>
            <?= htmlspecialchars(trim($piedUtilisateur['prenom'] . ' ' . $piedUtilisateur['nom'])) ?>
          </span>
        <?php endif ?>
        <span class="badge bg-label-secondary" title="Version de l'application">v1.0.2</span>
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->

<div class="content-backdrop fade"></div>
</div>
<!--/ Content wrapper -->
</div>

<!--/ Layout container -->
</div>
</div>

<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>

<!-- Drag Target Area To SlideIn Menu On Small Screens -->
<div class="drag-target"></div>

<!--/ Layout wrapper -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->

<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/libs/hammer/hammer.js"></script>
<script src="assets/vendor/libs/i18n/i18n.js"></script>
<script src="assets/vendor/js/menu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
<?php if ($pageScript ?? false): ?>
  <script src="assets/js/<?= $pageActive ?>.js"></script>
<?php endif ?>
<!-- Page JS -->
<script src="assets/js/main.js"></script>
<script src="assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

</body>

</html>