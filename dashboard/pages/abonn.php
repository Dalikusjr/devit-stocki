<div class="card">
                <div class="card-datatable table-responsive pt-0">
                  <table class="datatables-abonn table-striped table">
                    <thead>
                      <tr>
                        <th></th>
                        <th>id</th>
                        <th>Client</th>
                        <th>Type d'abonnement</th>
                        <th>Montant d'abonnement</th>
                        <th>Date de début</th>
                        <th>Statut</th>
                        <th></th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            <!-- / CONTENT -->

            <!-- =======================
                 OFFCANVAS - NOUVEAU Abonnement
            ======================= -->
            <div class="offcanvas offcanvas-end" id="add-new-record">
              <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="titre"></h5>
                <button
                  type="button"
                  class="btn-close text-reset"
                  data-bs-dismiss="offcanvas"
                  aria-label="Close"
                ></button>
              </div>
              <div class="offcanvas-body flex-grow-1">
                <form
                  class="add-new-record pt-0 row g-2"
                  id="form-add-new-record"
                  onsubmit="return false"
                >
                  <div class="col-sm-12">
                    <label class="form-label" for="clientName"
                      >Nom de client</label
                    >
                    <div class="input-group input-group-merge">
                      <span id="clientName2" class="input-group-text"
                        ><i class="ti ti-user"></i
                      ></span>
                      <input
                        type="text"
                        id="clientName"
                        class="form-control dt-clientName"
                        name="clientName"
                        placeholder="John Doe"
                        aria-label="John Doe"
                        aria-describedby="clientName2"
                      />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <label for="typeAbonn" class="form-label"
                      >Type d'abonnement</label
                    >
                    <select
                      id="selectpickerBasic"
                      class="selectpicker w-100 border rounded dt-typeAbonn"
                      data-style="btn-default"
                      title="Choisir un Type d'abonnement"
                      name="typeAbonn"
                      id="typeAbonn"
                    >
                      <option value="Mensuel">Mensuel</option>
                      <option value="Trimestre">Trimestre</option>
                      <option value="Annuel">Annuel</option>
                    </select>
                  </div>

                  <div class="col-sm-12">
                    <label class="form-label" for="totalCost"
                      >Montant d'abonnement</label
                    >
                    <div class="input-group input-group-merge">
                      <span id="totalCost2" class="input-group-text"
                        ><i class="ti ti-report-money"></i
                      ></span>
                      <input
                        type="number"
                        class="form-control dt-totalCost"
                        id="totalCost"
                        name="totalCost"
                        aria-describedby="totalCost2"
                        placeholder="Montant TND"
                        aria-label="Montant TND"
                      />
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <label class="form-label" for="startDate"
                      >Date de début</label
                    >
                    <div class="input-group input-group-merge">
                      <span id="startDate2" class="input-group-text"
                        ><i class="ti ti-calendar-check"></i
                      ></span>
                      <input
                        type="date"
                        id="startDate"
                        name="startDate"
                        class="form-control dt-startDate"
                        aria-describedby="startDate2"
                      />
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <button
                      type="submit"
                      class="btn btn-primary data-submit me-sm-3 me-1"
                    >
                      Valider
                    </button>
                    <button
                      type="reset"
                      class="btn btn-outline-secondary"
                      data-bs-dismiss="offcanvas"
                    >
                      Annuler
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <!-- / OFFCANVAS -->

            <!-- =======================
                 MODAL CONFIRM DELETE
            ======================= -->
            <div
              class="modal"
              id="confirmDeleteModal"
              tabindex="-1"
              aria-labelledby="confirmDeleteLabel"
              aria-hidden="true"
            >
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
                      aria-label="Fermer"
                    ></button>
                  </div>
                  <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer cet abonnement ?
                  </div>
                  <div class="modal-footer">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-bs-dismiss="modal"
                    >
                      Annuler
                    </button>
                    <button
                      type="button"
                      class="btn btn-danger"
                      id="confirmDeleteBtn"
                    >
                      Supprimer
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- / MODAL -->