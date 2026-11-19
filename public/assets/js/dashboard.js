"use strict";

// ===============================
// Tableau de bord : gain du mois, alertes de paiement, rappels de renouvellement
// ===============================
let dashboard = null;

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
function frDate(value) {
  const d = new Date(value + "T00:00:00");
  return isNaN(d)
    ? escapeHtml(value)
    : new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "short",
        year: "numeric",
      }).format(d);
}
function plural(n, word) {
  return `${n} ${word}${n > 1 ? "s" : ""}`;
}

const KINDS = {
  project: { label: "Projet", icon: "ti-code", cls: "bg-label-primary" },
  maintain: { label: "Maintenance", icon: "ti-tool", cls: "bg-label-info" },
  stocki: { label: "Stocki", icon: "ti-package", cls: "bg-label-warning" },
  licence: { label: "Licence", icon: "ti-star", cls: "bg-label-secondary" },
};

// État d'un paiement : icône + texte, jamais la couleur seule
function paymentState(a) {
  if (a.level === "overdue")
    return `<span class="badge bg-label-danger"><i class="ti ti-alert-circle me-1"></i>En retard de ${plural(-a.days, "jour")}</span>`;
  if (a.level === "due")
    return a.days < 0
      ? `<span class="badge bg-label-warning"><i class="ti ti-clock me-1"></i>À payer depuis ${plural(-a.days, "jour")}</span>`
      : `<span class="badge bg-label-warning"><i class="ti ti-clock me-1"></i>À payer aujourd'hui</span>`;
  return `<span class="badge bg-label-info"><i class="ti ti-calendar me-1"></i>Dans ${plural(a.days, "jour")}</span>`;
}

function renewalState(r) {
  switch (r.level) {
    case "overdue":
      return `<span class="badge bg-label-danger"><i class="ti ti-alert-circle me-1"></i>Dépassé de ${plural(-r.days, "jour")}</span>`;
    case "week":
      return `<span class="badge bg-label-danger"><i class="ti ti-bell-ringing me-1"></i>${r.days === 0 ? "Aujourd'hui" : "Dans " + plural(r.days, "jour")} · 1 semaine</span>`;
    case "twoweeks":
      return `<span class="badge bg-label-warning"><i class="ti ti-bell me-1"></i>Dans ${plural(r.days, "jour")} · 2 semaines</span>`;
    default:
      return `<span class="badge bg-label-info"><i class="ti ti-bell me-1"></i>Dans ${plural(r.days, "jour")} · 1 mois</span>`;
  }
}

function renderKpis(k) {
  const now = new Date();
  $("#kpiMonthName").text(
    new Intl.DateTimeFormat("fr-FR", { month: "long" }).format(now),
  );
  $("#kpiMonth").text(dt(k.month));
  $("#kpiMonthSplit").text(`Dev ${dt(k.monthDev)} · Stocki ${dt(k.monthStocki)}`);
  const prevName = new Intl.DateTimeFormat("fr-FR", { month: "long" }).format(
    new Date(now.getFullYear(), now.getMonth() - 1, 1),
  );
  const diff = k.month - k.previousMonth;
  $("#kpiMonthCompare").html(
    `<i class="ti ${diff >= 0 ? "ti-trending-up" : "ti-trending-down"} me-1"></i>${diff >= 0 ? "+" : "−"}${escapeHtml(dt(Math.abs(diff)))} par rapport à ${escapeHtml(prevName)}`,
  );

  $("#kpiCollect").text(dt(k.toCollect));
  $("#kpiCollectSub").text(
    k.toCollectCount === 0
      ? "Aucun impayé"
      : `${plural(k.toCollectCount, "paiement")} dont ${k.overdueCount} en retard`,
  );
  $("#kpiSoon").text(dt(k.soon));
  $("#kpiSoonSub").text(
    k.soonCount === 0 ? "Aucune échéance" : plural(k.soonCount, "échéance"),
  );
  $("#kpiRenewals").text(k.renewalsCount);
  $("#kpiRenewalsSub").text(
    k.renewalsOverdue > 0
      ? `${plural(k.renewalsOverdue, "renouvellement")} dépassé${k.renewalsOverdue > 1 ? "s" : ""}`
      : `${plural(k.activeProjects, "projet")} actif${k.activeProjects > 1 ? "s" : ""}`,
  );
}

function renderAlerts(filter) {
  const all = dashboard.payments;
  const count = (lvl) => all.filter((a) => a.level === lvl).length;
  $("#cAll").text(all.length);
  $("#cOverdue").text(count("overdue"));
  $("#cDue").text(count("due"));
  $("#cSoon").text(count("soon"));

  const items = filter === "all" ? all : all.filter((a) => a.level === filter);
  if (items.length === 0) {
    $("#alertsTable tbody").html(
      '<tr><td colspan="5" class="text-center text-muted py-4"><i class="ti ti-circle-check me-1"></i>Aucune alerte</td></tr>',
    );
    return;
  }
  const rows = items
    .map((a) => {
      const k = KINDS[a.kind] || KINDS.project;
      return `<tr>
        <td><div class="num">${frDate(a.date)}</div>${paymentState(a)}</td>
        <td>${escapeHtml(a.client)}</td>
        <td><span class="badge ${k.cls} me-1"><i class="ti ${k.icon} me-1"></i>${k.label}</span><span class="text-body">${escapeHtml(a.label)}</span></td>
        <td class="text-end num fw-semibold">${dt(a.montant)}</td>
        <td class="text-end"><a href="${escapeHtml(a.link)}" class="btn btn-sm btn-icon btn-text-secondary" title="Ouvrir"><i class="ti ti-arrow-right"></i></a></td>
      </tr>`;
    })
    .join("");
  $("#alertsTable tbody").html(rows);
}

function renderRenewals(renewals) {
  if (renewals.length === 0) {
    $("#renewals").html(
      '<div class="text-center text-muted py-4"><i class="ti ti-circle-check me-1"></i>Aucun renouvellement dans les 30 prochains jours</div>',
    );
    return;
  }
  // à venir (le plus proche d'abord), puis dépassés (le plus récent d'abord)
  const upcoming = renewals.filter((r) => r.days >= 0).sort((a, b) => a.days - b.days);
  const overdue = renewals.filter((r) => r.days < 0).sort((a, b) => b.days - a.days);
  const html = [...upcoming, ...overdue]
    .map(
      (r) => `<div class="renewal-item py-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="fw-semibold">${escapeHtml(r.titre)}</div>
            <small class="text-muted">${escapeHtml(r.client)}${r.tel ? " · " + escapeHtml(r.tel) : ""}</small>
          </div>
          <a href="p-projects" class="btn btn-sm btn-icon btn-text-secondary" title="Voir les projets"><i class="ti ti-arrow-right"></i></a>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
          <small class="num"><i class="ti ti-calendar me-1"></i>${frDate(r.date)}</small>${renewalState(r)}
        </div>
      </div>`,
    )
    .join("");
  $("#renewals").html(html);
}

$(document).ready(function () {
  $.getJSON("api/dashboard")
    .done((res) => {
      if (!res.success) {
        showTopNotification(res.message, "danger", "ban");
        return;
      }
      dashboard = res.data;
      renderKpis(dashboard.kpis);
      renderAlerts("all");
      renderRenewals(dashboard.renewals);
    })
    .fail(() =>
      showTopNotification("Erreur lors du chargement du tableau de bord", "danger", "ban"),
    );

  $('input[name="alertFilter"]').on("change", function () {
    if (dashboard) renderAlerts(this.value);
  });
});
