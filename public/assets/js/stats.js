"use strict";

// ===============================
// Statistiques et gains
// Dev = projets + maintenance ; Stocki = abonnements + licences à vie
// ===============================
const COLORS = { dev: "#2a78d6", stocki: "#eb6834" }; // palette validée (daltonisme)
let stats = null;
let chart = null;

function escapeHtml(value) {
  return String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
}
const nf = new Intl.NumberFormat("fr-FR", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});
function dt(value) {
  return nf.format(Number(value) || 0) + " DT";
}
// Axe : 12 500 -> « 12,5 k »
function dtShort(value) {
  const v = Number(value) || 0;
  if (Math.abs(v) >= 1000)
    return (
      new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 1 }).format(
        v / 1000,
      ) + " k"
    );
  return new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 0 }).format(v);
}

function renderKpis() {
  $("#kpiTotal").text(dt(stats.total));
  $("#kpiMonth").text(dt(stats.month));
  $("#kpiMonthLabel").text(
    new Intl.DateTimeFormat("fr-FR", { month: "long", year: "numeric" }).format(
      new Date(),
    ),
  );
  $("#kpiDev").text(dt(stats.dev));
  $("#kpiStocki").text(dt(stats.stocki));
  $("#kpiClientsDev").text(stats.clients.dev);
  $("#kpiClientsStocki").text(stats.clients.stocki);
}

function renderSources() {
  const sources = [
    { label: "Projets", value: stats.byType.project, family: "dev" },
    { label: "Maintenance", value: stats.byType.maintain, family: "dev" },
    { label: "Abonnements Stocki", value: stats.byType.stocki, family: "stocki" },
    { label: "Licences à vie", value: stats.byType.stocki_premium, family: "stocki" },
  ];
  const max = Math.max(...sources.map((s) => s.value), 0);
  const html = sources
    .map((s) => {
      const pct = stats.total > 0 ? (s.value / stats.total) * 100 : 0;
      const width = max > 0 ? Math.max((s.value / max) * 100, s.value > 0 ? 1 : 0) : 0;
      return `
        <div class="source-row">
          <div class="d-flex justify-content-between align-items-baseline mb-1">
            <span><span class="swatch swatch-${s.family}"></span>${escapeHtml(s.label)}</span>
            <span class="num"><strong>${dt(s.value)}</strong> <span class="text-muted small ms-1">${pct.toFixed(1).replace(".", ",")} %</span></span>
          </div>
          <div class="source-track" title="${escapeHtml(s.label)} : ${dt(s.value)}">
            <div class="source-bar" style="width:${width}%;background:${COLORS[s.family]}"></div>
          </div>
        </div>`;
    })
    .join("");
  $("#sources").html(html);
}

function renderTable(periods) {
  const rows = periods
    .map(
      (p) => `<tr>
        <td>${escapeHtml(p.label)}</td>
        <td class="text-end num">${dt(p.dev)}</td>
        <td class="text-end num">${dt(p.stocki)}</td>
        <td class="text-end num"><strong>${dt(p.dev + p.stocki)}</strong></td>
      </tr>`,
    )
    .join("");
  $("#gainsTable tbody").html(rows);
}

function renderChart(granularity) {
  const periods = stats.series[granularity];
  renderTable(periods);
  const options = {
    chart: {
      type: "bar",
      stacked: true,
      height: 340,
      toolbar: { show: false },
      fontFamily: "inherit",
      animations: { speed: 300 },
    },
    series: [
      { name: "Projets Dev", data: periods.map((p) => p.dev) },
      { name: "Projets Stocki", data: periods.map((p) => p.stocki) },
    ],
    colors: [COLORS.dev, COLORS.stocki],
    xaxis: {
      categories: periods.map((p) => p.label),
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { colors: "#6d6b77" } },
    },
    yaxis: {
      labels: { formatter: dtShort, style: { colors: "#6d6b77" } },
    },
    plotOptions: {
      bar: {
        columnWidth: granularity === "year" ? "30%" : "50%",
        borderRadius: 4,
        borderRadiusApplication: "end",
        borderRadiusWhenStacked: "last",
      },
    },
    // fin liseré blanc entre les segments empilés
    stroke: { show: true, width: 2, colors: ["#ffffff"] },
    dataLabels: { enabled: false },
    grid: {
      borderColor: "#ececee",
      strokeDashArray: 0,
      xaxis: { lines: { show: false } },
    },
    legend: {
      position: "top",
      horizontalAlign: "left",
      markers: { width: 10, height: 10, radius: 3 },
      itemMargin: { horizontal: 10 },
    },
    tooltip: {
      shared: true,
      intersect: false,
      y: { formatter: (v) => dt(v) },
    },
    states: { hover: { filter: { type: "darken", value: 0.9 } } },
    noData: { text: "Aucun paiement sur cette période" },
  };
  if (chart) chart.destroy();
  chart = new ApexCharts(document.querySelector("#gainsChart"), options);
  chart.render();
}

$(document).ready(function () {
  $.getJSON("api/stats")
    .done((res) => {
      if (!res.success) {
        showTopNotification(res.message, "danger", "ban");
        return;
      }
      stats = res.data;
      renderKpis();
      renderSources();
      renderChart($('input[name="granularity"]:checked').val());
    })
    .fail(() =>
      showTopNotification("Erreur lors du chargement des statistiques", "danger", "ban"),
    );

  $('input[name="granularity"]').on("change", function () {
    if (stats) renderChart(this.value);
  });
});
