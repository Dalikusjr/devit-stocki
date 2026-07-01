</div>
<!--/ Content -->

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme border-top">
  <div class="container-xxl">
    <div
      class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        © <span id="current-year"></span>
        <span class="fw-bolder">Devit-Stocki</span>.
        <span class="d-none d-sm-inline-block">
          Tous droits réservés.</span>
      </div>

      <div class="d-none d-lg-inline-block">
        <span class="badge bg-label-secondary">v1.0.2</span>
      </div>
    </div>
  </div>
</footer>

<script>
  // Optimisation : On évite document.write pour de meilleures performances
  document.getElementById("current-year").textContent =
    new Date().getFullYear();
</script>
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

<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/libs/hammer/hammer.js"></script>
<script src="../assets/vendor/libs/i18n/i18n.js"></script>
<script src="../assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
<?php if ($pageActive != 'dash'): ?>
  <script src="../assets/js/<?= $pageActive ?>.js"></script>
<?php endif ?>
<!-- Page JS -->
<script src="../assets/js/main.js"></script>
<script src="../assets/js/dashboards.js"></script>
<script src="../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

</body>

</html>