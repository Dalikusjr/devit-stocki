            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="card">
                <div class="card-body d-flex gap-2">
                  <a
                    href="dashboard.php?page=paiments"
                    class="btn  btn-primary d-inline-flex align-items-center px-3 py-1 fw-bold text-white">
                    <i
                      class="ti ti-arrow-narrow-left me-sm-2"
                      style="font-size: 1.25rem"></i>
                    <span class="d-none d-sm-inline-block">Retour</span>
                  </a>
                  <h2 class="card-title m-0">Liste des projets</h2>
                </div>
                <div class="card-body">
                  <div id="listeProjets"></div>
                </div>
              </div>
            </div>
            <!-- =======================
OFFCANVAS - NOUVEAU Tranche
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
                  <div class="mb-3">
                    <label class="form-label" for="commentaire">Commentaire</label>
                    <input type="text" name="commentaire" class="form-control dt-commentaire" placeholder="Ex: Premier versement">
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="montant">Montant (DT)</label>
                    <input type="number" name="montant" class="form-control dt-montant" placeholder="0.000">
                  </div>

                  <div class="mb-3">
                    <label class="form-label d-block">Statut du paiement</label>
                    <div class="d-flex align-items-center">
                      <div class="check_container ms-2">
                        <input id="status" type="checkbox" class="check_input update-status d-none dt-status">
                        <label for="status" class="check_label badge bg-label-danger me-2">
                          Non payé
                        </label>
                      </div>
                    </div>
                  </div>

                  <div id="dateech" class="mb-3">
                    <label class="form-label" for="dateech">Date d'échéance</label>
                    <input type="date" name="dateech" class="form-control dt-dateech">
                  </div>

                  <div id="datepay" class="mb-3">
                    <label class="form-label" for="datepay">Date de paiement</label>
                    <input type="date" name="datepay" class="form-control dt-datepay" disabled>
                  </div>

                  <div class="col-sm-12 mt-4">
                    <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
                  </div>
                </form>
              </div>
            </div>