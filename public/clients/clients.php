<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table class="datatables-clients table-striped table">
      <thead>
        <tr>
          <th></th>
          <th>id</th>
          <th>Nom</th>
          <th>E-mail</th>
          <th>Téléphone</th>
          <th>Adresse</th>
          <th></th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- / CONTENT -->

<!-- =======================
OFFCANVAS - NOUVEAU CIENT
======================= -->
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="titre"></h5>
    <button
      type="button"
      class="btn-close text-reset"
      data-bs-dismiss="offcanvas"
      aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">

      <div class="col-sm-12">
        <label class="form-label" for="Name">Nom</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-user-dollar"></i></span>
          <input type="text" id="Name" class="form-control dt-Name"
            name="Name" placeholder="Nom" maxlength="40" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="email">Email</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-at"></i></span>
          <input type="email" id="email" class="form-control dt-email"
            name="email" placeholder="exemple@mail.com" maxlength="40" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="tel">Téléphone</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" id="tel" class="form-control dt-tel"
            name="tel" placeholder="+216 XX XXX XXX" maxlength="40" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="addr">Adresse</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
          <input type="text" id="addr" class="form-control dt-addr"
            name="addr" placeholder="Adresse" maxlength="100" />
        </div>
      </div>

      <div class="col-sm-12">
        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
        <button type="reset" id="resetBtn" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
      </div>

    </form>
  </div>
</div>
<!-- / OFFCANVAS -->
<!-- =======================
MODAL - Historique du client (tous modules)
======================= -->
<div class="modal fade" id="clientHistoryModal" tabindex="-1" aria-labelledby="clientHistoryTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="clientHistoryTitle">Historique du client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-4">
          <div class="col-sm-6">
            <div class="border rounded p-3">
              <div class="text-muted small">Total encaissé</div>
              <div class="fs-4 fw-semibold" id="histTotalReceived">–</div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="border rounded p-3">
              <div class="text-muted small">Reste dû (impayés et échéances à venir)</div>
              <div class="fs-4 fw-semibold" id="histTotalUnpaid">–</div>
            </div>
          </div>
        </div>
        <h6 class="mb-2"><i class="ti ti-clock-exclamation me-1"></i>Impayés et échéances à venir</h6>
        <div class="table-responsive mb-4">
          <table class="table table-sm align-middle mb-0" id="histUnpaid">
            <thead><tr><th>Échéance</th><th>Type</th><th>Objet</th><th class="text-end">Montant</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <h6 class="mb-2"><i class="ti ti-history me-1"></i>Paiements reçus</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0" id="histReceived">
            <thead><tr><th>Date</th><th>Type</th><th>Objet</th><th class="text-end">Montant</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
