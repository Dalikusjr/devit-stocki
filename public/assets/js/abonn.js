"use strict";

// ===============================
// Abonnements Stocki (mensuel, trimestriel, annuel)
// ===============================
let fv;
let offCanvasEl;
let table;
let editingRow = null;
let abonnToDelete = null;

const TYPES = {
  mensuel: "Mensuel",
  trimestriel: "Trimestriel",
  annuel: "Annuel",
};

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

$(document).on("change", ".update-status", function () {
  updateStatus(this);
});
function updateStatus(element) {
  const isChecked = $(element).is(":checked");
  const label = $(element).next(".check_label");
  label.text(isChecked ? "Payé" : "Non payé");
  label.toggleClass("bg-label-success", isChecked);
  label.toggleClass("bg-label-danger", !isChecked);
}
function updateStatusAjax(data) {
  $.ajax({
    url: "api/stocki/updatestatus",
    type: "PATCH",
    contentType: "application/json",
    data: JSON.stringify(data),
    dataType: "json",
    success: function (res) {
      // le rechargement remet aussi la case dans son état réel en cas de refus
      table.ajax.reload(null, false);
      showTopNotification(
        res.message,
        res.success ? "success" : "danger",
        res.success ? "check" : "ban",
      );
    },
    error: function () {
      table.ajax.reload(null, false);
      showTopNotification("Erreur lors de Traitement", "danger", "ban");
    },
  });
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

// Liste déroulante des clients (recherche côté serveur)
function initClientSelect() {
  $(".js-example-data-ajax").select2({
    placeholder: "Rechercher un client...",
    width: "100%",
    dropdownParent: $("#add-new-record"),

    ajax: {
      url: "api/stocki/clients",
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
    offCanvasElement.querySelector("#titre").innerHTML = "Nouvel abonnement";
    $(".js-example-data-ajax")
      .empty()
      .val(null)
      .prop("disabled", false)
      .trigger("change");
    offCanvasElement.querySelector(".dt-typeAbonn").value = "mensuel";
    offCanvasElement.querySelector(".dt-startDate").value = today();
    offCanvasElement.querySelector(".dt-status").checked = false;
    updateStatus(offCanvasElement.querySelector(".dt-status"));
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
  table = $(".datatables-abonn").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "api/stocki",
      dataSrc: "data",
    },
    columns: [
      { data: null },
      { data: "id" },
      { data: "client_name" },
      { data: "payment_type" },
      { data: "echeance" },
      { data: "startDate" },
      { data: "dateech" },
      { data: "status" },
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
        responsivePriority: 3,
        render: (data) =>
          `<strong class="badge bg-label-facebook">${escapeHtml(TYPES[data] || data)}</strong>`,
      },
      {
        targets: 4,
        responsivePriority: 6,
        render: (data) =>
          `<span class="badge bg-label-primary">${formatMontant(data)}</span>`,
      },
      {
        targets: 5,
        responsivePriority: 7,
        render: (data) => escapeHtml(String(data ?? "").slice(0, 10)),
      },
      {
        targets: 6,
        responsivePriority: 5,
        render: (data) => escapeHtml(data),
      },
      {
        targets: 7,
        responsivePriority: 1,
        render: (data, type, row) => {
          const checkboxId = `checkbox_${row.id}`;
          const checked = data === "paid" ? "checked" : "";
          return `
    <div class="d-flex align-items-center">
      <div class="check_container ms-2">
        <input id="${checkboxId}" type="checkbox" class="check_input check_input_dt" ${checked}>
        <label for="${checkboxId}" class="check_label badge ${
          checked ? "bg-label-success" : "bg-label-danger"
        } me-2" title="Changer le statut">
          ${checked ? "Payé" : "Non payé"}
        </label>
      </div>
    </div>
  `;
        },
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
        title: "Abonnements Stocki - Stocki Devit",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Abonnements Stocki - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7],
        },
      },
      {
        text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Abonnement</span>',
        className: "create-new btn btn-primary",
      },
    ],
    dom:
      '<"card-header d-flex flex-column flex-md-row align-items-center justify-content-between"<"head-label text-center"><"dt-action-buttons text-end"B>>' +
      '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
      "t" +
      '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    responsive: {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: (row) => escapeHtml(row.data()["client_name"]),
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) => {
            if (col.title !== "" && col.title !== "Actions") {
              let content = col.data;
              if (col.columnIndex === 7) {
                const isPayed = api.row(rowIdx).data().status === "paid";
                content = `<span class="badge ${isPayed ? "bg-label-success" : "bg-label-danger"}">${isPayed ? "Payé" : "Non payé"}</span>`;
              }
              return `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
               <td>${col.title}:</td><td>${content}</td>
             </tr>`;
            }
            return "";
          }).join("");

          return data
            ? $('<table class="table"/><tbody />').append(data)
            : false;
        },
      },
    },
  });

  // ===============================
  // Changement de statut
  // ===============================
  $(".datatables-abonn tbody").on("change", ".check_input_dt", function () {
    const data = table.row($(this).closest("tr")).data();
    updateStatus(this);
    updateStatusAjax({
      id: data.id,
      status: data.status === "paid" ? "unpaid" : "paid",
    });
  });

  // ===============================
  // Historique
  // ===============================
  $(".datatables-abonn tbody").on("click", ".view-history", function () {
    const id = table.row($(this).closest("tr")).data().id;
    $.getJSON("api/stocki/history", { id }, (res) => {
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
  $(".datatables-abonn tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier l'abonnement";
    // le client d'un abonnement ne change pas
    const option = new Option(data.client_name, data.id_client, true, true);
    $(offCanvasElement)
      .find(".js-example-data-ajax")
      .empty()
      .append(option)
      .prop("disabled", true)
      .trigger("change");
    offCanvasElement.querySelector(".dt-typeAbonn").value = data.payment_type;
    offCanvasElement.querySelector(".dt-cost").value = data.echeance;
    offCanvasElement.querySelector(".dt-startDate").value = String(
      data.startDate,
    ).slice(0, 10);
    offCanvasElement.querySelector(".dt-status").checked =
      data.status === "paid";
    updateStatus(offCanvasElement.querySelector(".dt-status"));
    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().id : null,
      idclient: $(".js-example-data-ajax").val(),
      paymentType: $("#typeAbonn").val(),
      montant: $("#cost").val(),
      startDate: $("#startDate").val(),
      status: $("#status").is(":checked") ? "paid" : "unpaid",
    };

    $.ajax({
      url: editingRow ? "api/stocki/update" : "api/stocki/create",
      type: editingRow ? "PATCH" : "POST",
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
  $(".datatables-abonn tbody").on("click", ".delete-record", function () {
    abonnToDelete = table.row($(this).closest("tr")).data().id;
    $("#confirmDeleteModal .modal-title").text("Confirmer la suppression");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir supprimer cet abonnement ?",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (abonnToDelete) {
      $.ajax({
        url: "api/stocki/delete",
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
