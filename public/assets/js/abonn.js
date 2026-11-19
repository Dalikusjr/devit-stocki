"use strict";

// ===============================
// Déclarations globales
// ===============================
let fv;
let offCanvasEl;
let table;
let editingRow = null;
let rowToDelete = null;

// ===============================
// DOMContentLoaded : Initialisation du formulaire et OffCanvas
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  (function () {
  const formAddNewRecord = document.getElementById("form-add-new-record");

  // Gestion du bouton "Créer un nouveau record"
  setTimeout(() => {
    const newRecord = document.querySelector(".create-new");
    const offCanvasElement = document.querySelector("#add-new-record");

    if (newRecord) {
      newRecord.addEventListener("click", function () {
        editingRow = null;
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);

        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML =
          "Nouveau Abonnement";

        // Réinitialiser les champs
        offCanvasElement.querySelector(".dt-clientName").value = "";
        offCanvasElement.querySelector(".dt-totalCost").value = "";
        offCanvasElement.querySelector(".dt-startDate").value = "";
        offCanvasEl.show();
      });
    }
  }, 200);

  // ===============================
  // Validation du formulaire
  // ===============================
  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      clientName: {
        validators: { notEmpty: { message: "Le nom du client est requis" } },
      },
      typeAbonn: {
        validators: {
          notEmpty: { message: "Le type d'abonnement est requis" },
        },
      },
      startDate: {
        validators: { notEmpty: { message: "La date de début est requise" } },
      },
      totalCost: {
        validators: { notEmpty: { message: "Le Montant est requis" } },
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
            e.messageElement
          );
        }
      });
    },
  });
})();
});

// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  table = $(".datatables-abonn").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: "../assets/json/abonnements.json",
    columns: [
      { data: null },
      { data: "id" },
      { data: "client" },
      { data: "type_abonnement" },
      { data: "montant_abonnement" },
      { data: "date_debut_abonnement" },
      { data: "etat_paiement" },
      { data: null },
    ],
    columnDefs: [
      // ===============================
      // Définition des colonnes
      // ===============================
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
        orderable: false,
        responsivePriority: 2,
        render: (data) => `<span class="fw-bold">${data}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 2,
        render: (data) =>
          `<strong class="badge bg-label-facebook">${data}</strong>`,
      },
      {
        targets: 4,
        responsivePriority: 6,
        render: (data) => `<span class="fw-bold">${data} TND</span>`,
      },
      { targets: 5, responsivePriority: 6 },
      {
        targets: 6,
        responsivePriority: 1,
        render: (data, type, row, meta) => {
          const checkboxId = `checkbox_${meta.row}`;
          const checked = data === "Payé" ? "checked" : "";
          const label = data;

          return `
          <div class="d-flex align-items-center">
            <div class="check_container ms-2">
              <input id="${checkboxId}" type="checkbox" class="check_input" ${checked} data-row="${
            meta.row
          }">
              <label for="${checkboxId}" class="check_label badge ${
            checked ? "bg-label-success" : "bg-label-danger"
          } me-2" title="Changer le statut">
                ${label}
              </label>
            </div>
          </div>`;
        },
      },
      {
        targets: -1,
        title: "Actions",
        orderable: false,
        searchable: false,
        responsivePriority: 1,
        render: () =>
          '<div class="d-inline-flex">' +
          '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
          '<i class="ti ti-dots"></i></a>' +
          '<div class="dropdown-menu dropdown-menu-end">' +
          '<a href="javascript:;" class="dropdown-item edit-record"><i class="ti ti-pencil me-1"></i> Modifier</a>' +
          '<a href="javascript:;" class="dropdown-item delete-record"><i class="ti ti-trash me-1"></i> Supprimer</a>' +
          "</div></div>",
      },
    ],
    buttons: [
      {
        extend: "print",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
      },
      {
        extend: "pdfHtml5",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
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
          header: (row) => row.data()["client"],
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) =>
            col.title !== ""
              ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                  <td>${col.title}:</td><td>${col.data}</td>
                </tr>`
              : ""
          ).join("");

          const table = data
            ? $('<table class="table"/><tbody />').append(data)
            : false;

          // Re-binder les checkbox dans le modal
          if (table) {
            table
              .find(".check_input")
              .off("change")
              .on("change", function () {
                const label = $(this).next("label");
                if ($(this).is(":checked")) {
                  label
                    .removeClass("bg-label-danger")
                    .addClass("bg-label-success")
                    .text("Payé");
                } else {
                  label
                    .removeClass("bg-label-success")
                    .addClass("bg-label-danger")
                    .text("Non payé");
                }
              });
          }

          return table;
        },
      },
    },
    drawCallback: function () {
      $(".check_input")
        .off("change")
        .on("change", function () {
          const isChecked = $(this).is(":checked");
          const badge = $(this).next(".badge");
          if (isChecked) {
            badge
              .removeClass("bg-label-danger")
              .addClass("bg-label-success")
              .text("Payé");
          } else {
            badge
              .removeClass("bg-label-success")
              .addClass("bg-label-danger")
              .text("Non payé");
          }
        });
    },
    initComplete: function () {
      $(".even,.odd").hover(
        function () {
          $(this)
            .data("bgcolor", $(this).css("background-color"))
            .css("background-color", "#cce5ff");
        },
        function () {
          $(this).css("background-color", $(this).data("bgcolor"));
        }
      );
      $("head").append(`
        <style>
          .check_input {
              position: absolute;
              opacity: 0;
              pointer-events: none;
          }
          .check_input:checked + .badge {
              background-color: #28a745 !important;
              color: #fff !important;
              box-shadow: 0 0 10px rgba(40,167,69,0.6);
          }
          .check_label {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
          }
          .check_label:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
          }
        </style>
      `);
    },
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-abonn tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier l'abonnement";

    offCanvasElement.querySelector(".dt-clientName").value = data.client;
    offCanvasElement.querySelector(".dt-typeAbonn").value =
      data.type_abonnement;
    offCanvasElement.querySelector(".dt-totalCost").value =
      data.montant_abonnement;
    offCanvasElement.querySelector(".dt-startDate").value =
      data.date_debut_abonnement;

    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().id : Date.now(),
      client: $("#clientName").val(),
      type_abonnement: $("#typeAbonn").val(),
      montant_abonnement: parseFloat($("#totalCost").val()),
      date_debut_abonnement: $("#startDate").val(),
      etat_paiement: editingRow ? editingRow.data().etat_paiement : "Non payé",
    };

    if (editingRow) table.row(editingRow).data(formData).draw();
    else table.row.add(formData).draw();

    offCanvasEl.hide();
    document.getElementById("form-add-new-record").reset();
    editingRow = null;
  });

  // ===============================
  // Événements de suppression
  // ===============================
  const deleteModalEl = document.getElementById("confirmDeleteModal");
  const deleteModal = new bootstrap.Modal(deleteModalEl);

  $(".datatables-abonn tbody").on("click", ".delete-record", function () {
    rowToDelete = table.row($(this).closest("tr"));
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (rowToDelete) {
      rowToDelete.remove().draw();
      rowToDelete = null;
      deleteModal.hide();
    }
  });
});
