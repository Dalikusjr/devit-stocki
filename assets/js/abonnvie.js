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
        offCanvasElement.querySelector(".dt-resteApayer").value = "";
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
      resteApayer: {
        validators: {
          notEmpty: { message: "Le Montant est requis" },
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
});

// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  table = $(".datatables-abonnvie").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: "../assets/json/licenceVie.json",
    columns: [
      { data: null },
      { data: "id" },
      { data: "client" },
      { data: "date_debut" },
      { data: "montant_a_payer" },
      { data: "reste_a_payer" },
      { data: null },
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
      },
      {
        targets: 4,
        responsivePriority: 6,
        render: (data) =>
          `<span class="badge bg-label-facebook">${data} TND</span>`,
      },
      {
        targets: 5,
        responsivePriority: 6,
        render: (data) =>
          `<span class="badge ${
            data == 0 ? "bg-label-success" : "bg-label-danger"
          }">${data} TND</span>`,
      },
      {
        targets: 6,
        responsivePriority: 1,
        render: (data, type, row) => {
          return `<a href="javascript:;" class="view-history"><i class="ti ti-history"></i></a>`;
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
          return table;
        },
      },
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
    },
  });

  $(".datatables-abonnvie tbody").on("click", ".view-history", function () {
    const data = table.row($(this).closest("tr")).data();

    let rows = "";
    if (data.historique_paiements.length > 0) {
      data.historique_paiements.forEach((p) => {
        rows += `
        <tr>
          <td>${p.date}</td>
          <td>${p.montant} TND</td>
          <td>${p.mode}</td>
        </tr>
      `;
      });
    } else {
      rows = `<tr><td colspan="3" class="text-center">Aucun paiement effectué</td></tr>`;
    }

    $("#historyTable tbody").html(rows);
    $("#historyModal").modal("show");
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-abonnvie tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier l'abonnement";

    offCanvasElement.querySelector(".dt-clientName").value = data.client;
    offCanvasElement.querySelector(".dt-resteApayer").value =
      data.reste_a_payer;
    offCanvasElement.querySelector(".dt-totalCost").value =
      data.montant_a_payer;
    offCanvasElement.querySelector(".dt-startDate").value =
      data.date_debut;

    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().id : Date.now(),
      client: $("#clientName").val(),
      reste_a_payer: $("#resteApayer").val(),
      montant_a_payer: parseFloat($("#totalCost").val()),
      date_debut: $("#startDate").val(),
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

  $(".datatables-abonnvie tbody").on("click", ".delete-record", function () {
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
