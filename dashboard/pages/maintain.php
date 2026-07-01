<div class="card">
    <div class="card-datatable table-responsive pt-0">
        <table class="datatables-maintain table-striped table">
            <thead>
                <tr>
                    <th></th>
                    <th>id</th>
                    <th>projet</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

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
            aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">

            <div class="col-sm-12">
                <label class="form-label" for="project">Projet</label>
                <div class="input-group input-group-merge">
                    <select class="js-example-data-ajax form-control dt-project" style="width: 100%;" name="project"></select>
                </div>
            </div>
           
            

            <div class="col-sm-12">
                <label class="form-label" for="nomclt">Nom de client</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                    <input type="text" id="nomclt" class="form-control dt-nomclt"
                        name="nomclt" placeholder="Le nom de client" readonly/>
                </div>
            </div>

            <div class="col-sm-12">
                <label class="form-label" for="cost">Montant (DT)</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-report-money"></i></span>
                    <input type="number" id="cost" class="form-control dt-cost"
                        name="cost" placeholder="0.000" />
                </div>
            </div>

            <div class="col-sm-12 mb-3">
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

            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
            </div>

        </form>
    </div>
</div>
<!-- / OFFCANVAS -->