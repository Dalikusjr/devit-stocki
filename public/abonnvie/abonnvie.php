<div class="card">
    <div class="card-datatable table-responsive pt-0">
        <table class="datatables-abonnvie table-striped table">
            <thead>
                <tr>
                    <th></th>
                    <th>id</th>
                    <th>Client</th>
                    <th>Date de début</th>
                    <th>Montant</th>
                    <th>Payé</th>
                    <th>Reste à payer</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- =======================
OFFCANVAS - Licence à vie
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
                <label class="form-label" for="client">Client</label>
                <div class="input-group input-group-merge">
                    <select id="client" class="js-example-data-ajax form-control dt-client" style="width: 100%;" name="client"></select>
                </div>
            </div>

            <div class="col-sm-12">
                <label class="form-label" for="startDate">Date de début de la licence</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-calendar-check"></i></span>
                    <input type="date" id="startDate" class="form-control dt-startDate" name="startDate" />
                </div>
            </div>

            <div class="col-sm-12">
                <label class="form-label" for="cost">Montant de la licence (DT)</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-report-money"></i></span>
                    <input type="number" id="cost" class="form-control dt-cost"
                        name="cost" placeholder="0.000" step="any" />
                </div>
            </div>

            <div class="col-sm-12 div-create">
                <label class="form-label" for="advance">Avance payée (DT)</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-cash"></i></span>
                    <input type="number" id="advance" class="form-control dt-advance"
                        name="advance" placeholder="0.000" step="any" />
                </div>
                <small class="text-muted">Montant total pour un paiement comptant.</small>
            </div>

            <div class="col-sm-12 div-create">
                <label class="form-label" for="comment">Commentaire de l'avance</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-bubble-text"></i></span>
                    <input type="text" id="comment" class="form-control dt-comment"
                        name="comment" placeholder="Avance" maxlength="100" />
                </div>
            </div>

            <div class="col-sm-12">
                <label class="form-label" for="nbrInstall">Nombre d'échéances pour le reste</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-number"></i></span>
                    <select id="nbrInstall" class="form-select dt-nbrInstall" name="nbrInstall">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <small class="text-muted">Une échéance par mois à partir de la date de début.</small>
            </div>

            <div class="col-sm-12 mt-3">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
                <button type="reset" id="resetBtn" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
            </div>

        </form>
    </div>
</div>
<!-- / OFFCANVAS -->
