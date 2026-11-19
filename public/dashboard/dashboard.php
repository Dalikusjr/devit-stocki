<style>
  .dash .stat-label { font-size: .8125rem; color: var(--bs-secondary-color); }
  .dash .stat-value { font-size: 1.5rem; font-weight: 600; color: var(--bs-heading-color); line-height: 1.3; font-variant-numeric: tabular-nums; }
  .dash .stat-sub { font-size: .8125rem; color: var(--bs-secondary-color); }
  .dash .stat-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
  .dash .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
  .dash .alerts-scroll { max-height: 470px; overflow-y: auto; }
  .dash .alerts-scroll thead th { position: sticky; top: 0; background: var(--bs-card-bg, #fff); z-index: 1; }
  .dash .renewal-item + .renewal-item { border-top: 1px solid var(--bs-border-color); }
  .dash .filter-chips .btn { --bs-btn-padding-y: .2rem; --bs-btn-padding-x: .6rem; --bs-btn-font-size: .8125rem; }
</style>

<div class="dash">
  <!-- Indicateurs -->
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100"><div class="card-body d-flex gap-3">
        <span class="stat-icon bg-label-success"><i class="ti ti-currency-dollar ti-md"></i></span>
        <div>
          <div class="stat-label">Gain du mois <span id="kpiMonthName"></span></div>
          <div class="stat-value" id="kpiMonth">–</div>
          <div class="stat-sub" id="kpiMonthSplit">&nbsp;</div>
          <div class="stat-sub" id="kpiMonthCompare">&nbsp;</div>
        </div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100"><div class="card-body d-flex gap-3">
        <span class="stat-icon bg-label-danger"><i class="ti ti-alert-triangle ti-md"></i></span>
        <div>
          <div class="stat-label">Impayés à encaisser</div>
          <div class="stat-value" id="kpiCollect">–</div>
          <div class="stat-sub" id="kpiCollectSub">&nbsp;</div>
        </div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100"><div class="card-body d-flex gap-3">
        <span class="stat-icon bg-label-warning"><i class="ti ti-calendar-due ti-md"></i></span>
        <div>
          <div class="stat-label">Échéances des 7 prochains jours</div>
          <div class="stat-value" id="kpiSoon">–</div>
          <div class="stat-sub" id="kpiSoonSub">&nbsp;</div>
        </div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100"><div class="card-body d-flex gap-3">
        <span class="stat-icon bg-label-primary"><i class="ti ti-refresh ti-md"></i></span>
        <div>
          <div class="stat-label">Renouvellements sous 30 jours</div>
          <div class="stat-value" id="kpiRenewals">–</div>
          <div class="stat-sub" id="kpiRenewalsSub">&nbsp;</div>
        </div>
      </div></div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Alertes de paiement -->
    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h5 class="card-title m-0">Alertes de paiement</h5>
            <small class="text-muted">Impayés et échéances des 7 prochains jours</small>
          </div>
          <div class="btn-group filter-chips" role="group" aria-label="Filtrer les alertes">
            <input type="radio" class="btn-check" name="alertFilter" id="f-all" value="all" checked>
            <label class="btn btn-outline-secondary" for="f-all">Toutes <span class="ms-1" id="cAll"></span></label>
            <input type="radio" class="btn-check" name="alertFilter" id="f-overdue" value="overdue">
            <label class="btn btn-outline-secondary" for="f-overdue">En retard <span class="ms-1" id="cOverdue"></span></label>
            <input type="radio" class="btn-check" name="alertFilter" id="f-due" value="due">
            <label class="btn btn-outline-secondary" for="f-due">À payer <span class="ms-1" id="cDue"></span></label>
            <input type="radio" class="btn-check" name="alertFilter" id="f-soon" value="soon">
            <label class="btn btn-outline-secondary" for="f-soon">À venir <span class="ms-1" id="cSoon"></span></label>
          </div>
        </div>
        <div class="card-body pt-0">
          <div class="table-responsive alerts-scroll">
            <table class="table table-sm align-middle mb-0" id="alertsTable">
              <thead>
                <tr><th>Échéance</th><th>Client</th><th>Objet</th><th class="text-end">Montant</th><th></th></tr>
              </thead>
              <tbody><tr><td colspan="5" class="text-center text-muted py-4">Chargement…</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Rappels de renouvellement -->
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0">Rappels de renouvellement</h5>
          <small class="text-muted">Projets Dev : 1 mois, 2 semaines et 1 semaine avant</small>
        </div>
        <div class="card-body pt-0 alerts-scroll" id="renewals">
          <div class="text-center text-muted py-4">Chargement…</div>
        </div>
      </div>
    </div>
  </div>
</div>
