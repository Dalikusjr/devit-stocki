"use strict";

// ===============================
// Licences Stocki à vie : montant, avance, échéances, paiements
// ===============================
let fv;
let offCanvasEl;
let table;
let editingRow = null;
let abonnToDelete = null;

// Échappe les données avant insertion dans le HTML
function escapeHtml(value) {
  return String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
}
function formatMontant(value) {
  return $.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(value);
}
function today() {
  const d = new Date();
  return new Date(d.getTime() - d.getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 10);
}
function isSoldee(row) {
  return Math.round(parseFloat(row.reste) * 1000) <= 0;
}

function makeTable(data) {
  $("#header_perso").html("<th>Montant</th><th>Date de paiement</th>");
  let rows = "";

  if (Array.isArray(data) && data.length > 0) {
    data.forEach((h) => {
      rows += `
      <tr>
        <td>${formatMontant(h.montant)}</td>
        <td>${escapeHtml(h.payed_at)}</td>
      </tr>
    `;
    });
  } else {
    rows = `<tr><td colspan="2" class="text-center">Aucun paiement effectué</td></tr>`;
  }
  $("#historyTable tbody").html(rows);
  $("#historyModal").modal("show");
}

// Détail des échéances d'une licence (ligne dépliée)
function formatInstallments(echeances) {
  if (!Array.isArray(echeances) || echeances.length === 0) {
    return '<div class="p-3 text-center">Aucune échéance pour cette licence.</div>';
  }
  let html = `
        <table class="table table-sm table-striped w-100 mb-0">
            <thead>
                <tr>
                    <th>Commentaire</th>
                    <th>Montant</th>
                    <th>Date d'échéance</th>
                    <th>Statut</th>
                    <th>Date de paiement</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
    `;
  echeances.forEach((e) => {
    const paid = e.status === "paid";
    html += `
            <tr>
                <td>${escapeHtml(e.commentaire)}</td>
                <td>${formatMontant(e.echeance)}</td>
                <td>${escapeHtml(e.dateech)}</td>
                <td>
                    <span class="badge bg-label-${paid ? "success" : "danger"}">
                        ${paid ? "Payée" : "Non payée"}
                    </span>
                </td>
                <td>${escapeHtml(e.datepay || "")}</td>
                <td class="text-end">${
                  paid
                    ? ""
                    : `<button type="button" class="btn btn-sm btn-success pay-installment" data-idpay="${Number(e.idpay)}"><i class="ti ti-check me-1"></i>Marquer payée</button>`
                }</td>
            </tr>
        `;
  });
  html += `</tbody></table>`;
  return html;
}

// Liste déroulante des clients (recherche côté serveur)
function initClientSelect() {
  $(".js-example-data-ajax").select2({
    placeholder: "Rechercher un client...",
    width: "100%",
    dropdownParent: $("#add-new-record"),

    ajax: {
      url: "api/stockipremium/clients",
      dataType: "json",
      delay: 250,

      processResults: function (response) {
        return {
          results: (response.data || []).map((item) => ({
            id: String(item.idclient),
            text: `${item.nom} (${item.tel})`,
          })),
        };
      },
    },
  });
}

// ===============================
// DOMContentLoaded : Initialisation du formulaire et OffCanvas
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const formAddNewRecord = document.getElementById("form-add-new-record");
  const offCanvasElement = document.querySelector("#add-new-record");

  setTimeout(() => {
    initClientSelect();
    $(".js-example-data-ajax").on("select2:select", function () {
      fv.revalidateField("client");
    });
  }, 200);

  // Bouton "Ajouter" (créé par DataTables : écoute déléguée)
  $(document).on("click", ".create-new", function () {
    editingRow = null;
    fv.resetForm(true);
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Nouvelle licence";
    $(".js-example-data-ajax")
      .empty()
      .val(null)
      .prop("disabled", false)
      .trigger("change");
    offCanvasElement.querySelector(".dt-startDate").value = today();
    offCanvasElement.querySelector(".dt-nbrInstall").value = "1";
    offCanvasElement
      .querySelectorAll(".div-create")
      .forEach((e) => e.classList.remove("d-none"));
    offCanvasElement.querySelector(".dt-cost").disabled = false;
    offCanvasElement.querySelector(".dt-nbrInstall").disabled = false;
    offCanvasEl.show();
  });
  offCanvasElement
    .querySelector("#resetBtn")
    .addEventListener("click", function () {
      fv.resetForm(true);
    });

  // ===============================
  // Validation du formulaire
  // ===============================
  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      client: {
        validators: {
          notEmpty: { message: "Le client est requis" },
        },
      },
      startDate: {
        validators: {
          notEmpty: { message: "La date de début est requise" },
        },
      },
      cost: {
        validators: {
          notEmpty: { message: "Le montant est requis" },
          callback: {
            message: "Le montant doit être supérieur à 0",
            callback: function (input) {
              if (input.value === "") return true;
              return parseFloat(input.value) > 0;
            },
          },
        },
      },
      advance: {
        validators: {
          callback: {
            message: "L'avance ne peut pas dépasser le montant de la licence",
            callback: function (input) {
              if (input.value === "") return true;
              const avance = parseFloat(input.value);
              if (avance < 0) {
                return {
                  valid: false,
                  message: "L'avance ne peut pas être négative",
                };
              }
              const montant = parseFloat(
                formAddNewRecord.querySelector("#cost").value,
              );
              return isNaN(montant) || avance <= montant;
            },
          },
        },
      },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: "",
        rowSelector: ".col-sm-12",
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
    },
    init: (instance) => {
      instance.on("plugins.message.placed", function (e) {
        if (e.element.parentElement.classList.contains("input-group")) {
          e.element.parentElement.insertAdjacentElement(
            "afterend",
            e.messageElement,
          );
        }
      });
    },
  });
});

// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  table = $(".datatables-abonnvie").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "api/stockipremium",
      dataSrc: "data",
    },
    columns: [
      { data: null },
      { data: "id" },
      { data: "client_name" },
      { data: "datedebut" },
      { data: "montant" },
      { data: "paye" },
      { data: "reste" },
      { data: null },
      { data: null },
    ],
    columnDefs: [
      {
        className: "control",
        orderable: false,
        targets: 0,
        searchable: false,
        render: () => "",
      },
      { targets: 1, orderable: true, searchable: false, responsivePriority: 4 },
      {
        targets: 2,
        responsivePriority: 2,
        render: (data, type, row) =>
          `<div><strong>${escapeHtml(row.client_name)}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${escapeHtml(row.client_tel)}</span></div>`,
      },
      {
        targets: 3,
        responsivePriority: 6,
        render: (data) => escapeHtml(String(data ?? "").slice(0, 10)),
      },
      {
        targets: 4,
        responsivePriority: 5,
        render: (data) =>
          `<span class="badge bg-label-primary">${formatMontant(data)}</span>`,
      },
      {
        targets: 5,
        responsivePriority: 5,
        render: (data) =>
          `<span class="badge bg-label-success">${formatMontant(data)}</span>`,
      },
      {
        targets: 6,
        responsivePriority: 3,
        render: (data, type, row) =>
          isSoldee(row)
            ? `<span class="badge bg-label-secondary">${formatMontant(0)}</span>`
            : `<span class="badge bg-label-danger">${formatMontant(data)}</span>`,
      },
      {
        targets: 7,
        orderable: false,
        searchable: false,
        responsivePriority: 1,
        render: (data, type, row) =>
          isSoldee(row)
            ? '<span class="badge bg-success">Soldée</span>'
            : '<span class="badge bg-label-warning">En cours</span>',
      },
      {
        targets: -1,
        title: "Actions",
        className: "text-center",
        orderable: false,
        searchable: false,
        responsivePriority: 1,
        render: () =>
          '<div class="d-inline-flex">' +
          '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
          '<i class="ti ti-dots"></i></a>' +
          '<div class="dropdown-menu dropdown-menu-end">' +
          '<a href="javascript:;" class="dropdown-item edit-record"><i class="ti ti-pencil me-1"></i> Modifier</a>' +
          '<a href="javascript:;" class="dropdown-item view-history"><i class="ti ti-history me-1"></i> Historique</a>' +
          '<a href="javascript:;" class="dropdown-item delete-record"><i class="ti ti-trash me-1"></i> Supprimer</a>' +
          "</div></div>",
      },
    ],
    buttons: [
      {
        extend: "print",
        title: "Licences Stocki à vie - Stocki Devit",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Licences Stocki à vie - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7],
        },
      },
      {
        text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Licence</span>',
        className: "create-new btn btn-primary",
      },
    ],
    dom:
      '<"card-header d-flex flex-column flex-md-row align-items-center justify-content-between"<"head-label text-center"><"dt-action-buttons text-end"B>>' +
      '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
      "t" +
      '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
  });

  // ===============================
  // Clic sur une ligne : échéances de la licence
  // ===============================
  function loadInstallments(row, tr, animate) {
    $.getJSON("api/stockipremium/installments", { id: row.data().id })
      .done((res) => {
        if (!res.success) {
          row.child.hide();
          tr.removeClass("shown");
          showTopNotification(res.message, "danger", "ban");
          return;
        }
        row
          .child(
            `<div class="details-content" ${animate ? 'style="display:none;"' : ""}>${formatInstallments(res.data)}</div>`,
            "dt-child",
          )
          .show();
        if (animate) tr.next("tr").find(".details-content").slideDown(250);
      })
      .fail(() => {
        row.child.hide();
        tr.removeClass("shown");
        showTopNotification("Erreur lors de Traitement", "danger", "ban");
      });
  }

  $(".datatables-abonnvie tbody").on(
    "click",
    "tr",
    function (e) {
      // lignes de détail (et leur tableau interne) : pas de dépliage
      if ($(this).closest(".dt-child").length) return;
      if ($(e.target).closest("a, button, .dropdown-menu").length) return;
      const tr = $(this);
      const row = table.row(tr);
      if (!row.data()) return;

      if (row.child.isShown()) {
        tr.next("tr")
          .find(".details-content")
          .slideUp(200, function () {
            row.child.hide();
            tr.removeClass("shown");
          });
        return;
      }
      row
        .child(
          '<div class="details-content text-center py-3">Chargement...</div>',
          "dt-child",
        )
        .show();
      tr.addClass("shown");
      loadInstallments(row, tr, true);
    },
  );

  // Paiement d'une échéance depuis la ligne dépliée
  $(".datatables-abonnvie tbody").on("click", ".pay-installment", function () {
    const btn = $(this);
    const tr = btn.closest("tr.dt-child").prev("tr");
    const row = table.row(tr);
    btn.prop("disabled", true);
    $.ajax({
      url: "api/stockipremium/pay",
      type: "PATCH",
      contentType: "application/json",
      data: JSON.stringify({ id: row.data().id, idpay: btn.data("idpay") }),
      dataType: "json",
      success: function (res) {
        if (res.success) {
          showTopNotification(res.message, "success", "check");
          // met à jour payé / reste puis rouvre le détail
          table.ajax.reload(() => {
            table.rows().every(function () {
              if (this.data().id === row.data().id) {
                const node = $(this.node()).addClass("shown");
                loadInstallments(this, node, false);
              }
            });
          }, false);
        } else {
          btn.prop("disabled", false);
          showTopNotification(res.message, "danger", "ban");
        }
      },
      error: function () {
        btn.prop("disabled", false);
        showTopNotification("Erreur lors de Traitement", "danger", "ban");
      },
    });
  });

  // ===============================
  // Historique
  // ===============================
  $(".datatables-abonnvie tbody").on("click", ".view-history", function () {
    const id = table.row($(this).closest("tr")).data().id;
    $.getJSON("api/stockipremium/history", { id }, (res) => {
      if (res.success) {
        makeTable(res.data);
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    }).fail(() =>
      showTopNotification("Erreur lors de Traitement", "danger", "ban"),
    );
  });

  // ===============================
  // Modification
  // ===============================
  $(".datatables-abonnvie tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier la licence";
    // le client d'une licence ne change pas ; l'avance se gère par les échéances
    const option = new Option(data.client_name, data.id_client, true, true);
    $(offCanvasElement)
      .find(".js-example-data-ajax")
      .empty()
      .append(option)
      .prop("disabled", true)
      .trigger("change");
    offCanvasElement
      .querySelectorAll(".div-create")
      .forEach((e) => e.classList.add("d-none"));
    offCanvasElement.querySelector(".dt-startDate").value = String(
      data.datedebut,
    ).slice(0, 10);
    offCanvasElement.querySelector(".dt-cost").value = data.montant;
    offCanvasElement.querySelector(".dt-nbrInstall").value = String(
      Math.min(Math.max(Number(data.nbimpayees) || 1, 1), 3),
    );
    // après un premier paiement, montant et échéances ne changent plus
    const locked = parseFloat(data.paye) > 0;
    offCanvasElement.querySelector(".dt-cost").disabled = locked;
    offCanvasElement.querySelector(".dt-nbrInstall").disabled = locked;
    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const editing = editingRow ? editingRow.data() : null;
    const formData = {
      id: editing ? editing.id : null,
      idclient: $(".js-example-data-ajax").val(),
      datedebut: $("#startDate").val(),
      montant: $("#cost").val(),
      avance: $("#advance").val(),
      comment: $("#comment").val(),
      // champs grisés : on renvoie les valeurs actuelles
      nbrEcheance: $("#nbrInstall").prop("disabled")
        ? editing.nbimpayees
        : $("#nbrInstall").val(),
    };

    $.ajax({
      url: editing ? "api/stockipremium/update" : "api/stockipremium/create",
      type: editing ? "PATCH" : "POST",
      contentType: "application/json",
      data: JSON.stringify(formData),
      dataType: "json",
      success: function (res) {
        if (res.success) {
          table.ajax.reload(null, false);
          showTopNotification(res.message, "success", "check");
          offCanvasEl.hide();
          fv.resetForm(true);
          editingRow = null;
        } else {
          showTopNotification(res.message, "danger", "ban");
        }
      },
      error: function () {
        showTopNotification("Erreur lors de Traitement", "danger", "ban");
      },
    });
  });

  // ===============================
  // Suppression
  // ===============================
  const deleteModalEl = document.getElementById("confirmDeleteModal");
  const deleteModal = new bootstrap.Modal(deleteModalEl);
  $(".datatables-abonnvie tbody").on("click", ".delete-record", function () {
    abonnToDelete = table.row($(this).closest("tr")).data().id;
    $("#confirmDeleteModal .modal-title").text("Confirmer la suppression");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir supprimer cette licence ?",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (abonnToDelete) {
      $.ajax({
        url: "api/stockipremium/delete",
        type: "DELETE",
        contentType: "application/json",
        data: JSON.stringify({ abonnToDelete }),
        dataType: "json",
        success: function (res) {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            abonnToDelete = null;
            deleteModal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        error: function () {
          showTopNotification("Erreur lors de Traitement", "danger", "ban");
        },
      });
    }
  });
});
