<div class="alert alert-primary shadow-sm border-0 d-none align-items-center"
  id="notification"
  role="alert">
  <span class="alert-icon text-primary me-2">
    <i id="notification-icon" class=""></i>
  </span>
  <span id="notification-text"></span>
</div>
<!-- =======================
   MODAL CONFIRM DELETE
======================= -->
<div
  class="modal"
  id="confirmDeleteModal"
  tabindex="-1"
  aria-labelledby="confirmDeleteLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">
          Confirmer la suppression
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button
          id="closeBtn"
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal">
          Annuler
        </button>
        <button
          type="button"
          class="btn btn-danger"
          id="confirmDeleteBtn">
          Supprimer
        </button>
      </div>
    </div>
  </div>
</div>
<!-- / MODAL -->