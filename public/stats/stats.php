<style>
  /* Statistiques : couleurs des deux familles (palette validée) */
  .stats-page {
    --serie-dev: #2a78d6;
    --serie-stocki: #eb6834;
  }
  .stats-page .stat-label { font-size: .8125rem; color: var(--bs-secondary-color); }
  .stats-page .stat-value { font-size: 1.5rem; font-weight: 600; color: var(--bs-heading-color); line-height: 1.3; font-variant-numeric: tabular-nums; }
  .stats-page .stat-sub { font-size: .8125rem; color: var(--bs-secondary-color); }
  .stats-page .swatch { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: baseline; }
  .stats-page .swatch-dev { background: var(--serie-dev); }
  .stats-page .swatch-stocki { background: var(--serie-stocki); }
  .stats-page .source-row + .source-row { margin-top: 1.1rem; }
  .stats-page .source-track { height: 8px; border-radius: 4px; background: var(--bs-gray-100, #f1f0f2); overflow: hidden; }
  .stats-page .source-bar { height: 100%; border-radius: 4px; }
  .stats-page .num { font-variant-numeric: tabular-nums; }
  .stats-page #gainsChart { min-height: 340px; }
</style>

<div class="stats-page">
  <!-- Indicateurs -->
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl">
      <div class="card h-100"><div class="card-body">
        <div class="stat-label">Gain total</div>
        <div class="stat-value" id="kpiTotal">–</div>
        <div class="stat-sub">Tous les paiements encaissés</div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl">
      <div class="card h-100"><div class="card-body">
        <div class="stat-label">Gain du mois</div>
        <div class="stat-value" id="kpiMonth">–</div>
        <div class="stat-sub" id="kpiMonthLabel">&nbsp;</div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl">
      <div class="card h-100"><div class="card-body">
        <div class="stat-label"><span class="swatch swatch-dev"></span>Projets Dev</div>
        <div class="stat-value" id="kpiDev">–</div>
        <div class="stat-sub">Projets et maintenance</div>
      </div></div>
    </div>
    <div class="col-sm-6 col-xl">
      <div class="card h-100"><div class="card-body">
        <div class="stat-label"><span class="swatch swatch-stocki"></span>Projets Stocki</div>
        <div class="stat-value" id="kpiStocki">–</div>
        <div class="stat-sub">Abonnements et licences à vie</div>
      </div></div>
    </div>
    <div class="col-sm-12 col-xl">
      <div class="card h-100"><div class="card-body">
        <div class="stat-label">Clients</div>
        <div class="d-flex gap-4">
          <div><div class="stat-value" id="kpiClientsDev">–</div><div class="stat-sub">Dev</div></div>
          <div><div class="stat-value" id="kpiClientsStocki">–</div><div class="stat-sub">Stocki</div></div>
        </div>
      </div></div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Gains par période -->
    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="card-title m-0">Gains par période</h5>
          <div class="btn-group btn-group-sm" role="group" aria-label="Période">
            <input type="radio" class="btn-check" name="granularity" id="g-month" value="month" checked>
            <label class="btn btn-outline-primary" for="g-month">Mois</label>
            <input type="radio" class="btn-check" name="granularity" id="g-quarter" value="quarter">
            <label class="btn btn-outline-primary" for="g-quarter">Trimestre</label>
            <input type="radio" class="btn-check" name="granularity" id="g-year" value="year">
            <label class="btn btn-outline-primary" for="g-year">Année</label>
          </div>
        </div>
        <div class="card-body pt-0">
          <div id="gainsChart" role="img" aria-label="Gains Dev et Stocki par période"></div>
          <details class="mt-2">
            <summary class="text-muted small">Voir le tableau</summary>
            <div class="table-responsive mt-2">
              <table class="table table-sm mb-0" id="gainsTable">
                <thead><tr><th>Période</th><th class="text-end">Projets Dev</th><th class="text-end">Projets Stocki</th><th class="text-end">Total</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </details>
        </div>
      </div>
    </div>

    <!-- Répartition par source -->
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title m-0">Répartition par source</h5></div>
        <div class="card-body" id="sources"></div>
      </div>
    </div>
  </div>
</div>
