"use strict";

// ===============================
// Déclarations globales
// ===============================
let fv;
let offCanvasEl;
let table;
let editingRow = null;
let clientToDelete = null;

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

// ===============================
// DOMContentLoaded : Initialisation du formulaire et OffCanvas
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const formAddNewRecord = document.getElementById("form-add-new-record");

  // Gestion du bouton "Créer un nouveau record"
  setTimeout(() => {
    const offCanvasElement = document.querySelector("#add-new-record");
    // Délégation : le bouton est créé par DataTables, parfois après ce délai
    {
      $(document).on("click", ".create-new", function () {
        editingRow = null;
        // repartir d'un formulaire vide (une modification a pu être abandonnée)
        fv.resetForm(true);
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML = "Nouveau client";
        offCanvasEl.show();
      });
    }
    offCanvasElement
      .querySelector("#resetBtn")
      .addEventListener("click", function () {
        fv.resetForm(true);
      });
  }, 200);

  // ===============================
  // Validation du formulaire
  // ===============================
  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      Name: {
        validators: {
          notEmpty: { message: "Le nom du client est requis" },
        },
      },
      addr: {
        validators: {
          notEmpty: { message: "L'adresse du client est requis" },
        },
      },
      tel: {
        validators: {
          notEmpty: { message: "Le numéro de téléphone est requis" },
        },
      },
      email: {
        validators: {
          notEmpty: { message: "L'E-mail est requis" },
          emailAddress: {
            message: "L'adresse e-mail n'est pas valide",
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
  table = $(".datatables-clients").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "api/client",
      dataSrc: "data",
    },
    columns: [
      { data: null }, // Colonne pour le contrôle responsive
      { data: "idclient" },
      { data: "nom" },
      { data: "email" },
      { data: "tel" },
      { data: "adresse" },
      { data: null },
      // Colonne pour les actions
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
      {
        targets: 1,
        orderable: true,
        className: "text-center",
        searchable: false,
        responsivePriority: 4,
      },
      {
        targets: 2,
        orderable: true,
        responsivePriority: 2,
        render: (data) => `<span class="fw-bold">${escapeHtml(data)}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 2,
        render: (data) => escapeHtml(data),
      },
      {
        targets: 4,
        responsivePriority: 6,
        render: (data) =>
          `<span><i class="ti ti-phone me-2"></i>${escapeHtml(data)}</span>`,
      },
      {
        targets: 5,
        responsivePriority: 6,
        render: (data) =>
          `<span><i class="ti ti-map-pin me-2"></i>${escapeHtml(data)}</span>`,
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
          '<a href="javascript:;" class="dropdown-item view-history"><i class="ti ti-history me-1"></i> Historique</a>' +
          '<a href="javascript:;" class="dropdown-item edit-record"><i class="ti ti-pencil me-1"></i> Modifier</a>' +
          '<a href="javascript:;" class="dropdown-item delete-record"><i class="ti ti-trash me-1"></i> Supprimer</a>' +
          "</div></div>",
      },
    ],
    buttons: [
      {
        extend: "print",
        title: "Liste des Clients - Stocki Devit",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Liste des Clients - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
      },
      {
        text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Client</span>',
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
          header: (row) => escapeHtml(row.data()["nom"]),
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) => {
            if (col.title !== "" && col.title !== "Actions") {
              let content = col.data;
              return `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
               <td>${col.title}:</td><td>${content}</td>
             </tr>`;
            }
          }).join("");

          const table = data
            ? $('<table class="table"/><tbody />').append(data)
            : false;

          // Re-binder les checkbox dans le modal

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
        },
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
  // Historique complet du client (projets, maintenance, Stocki, licences)
  // ===============================
  const HIST_TYPES = {
    project: '<span class="badge bg-label-primary"><i class="ti ti-code me-1"></i>Projet</span>',
    maintain: '<span class="badge bg-label-info"><i class="ti ti-tool me-1"></i>Maintenance</span>',
    stocki: '<span class="badge bg-label-warning"><i class="ti ti-package me-1"></i>Stocki</span>',
    stocki_premium: '<span class="badge bg-label-secondary"><i class="ti ti-star me-1"></i>Licence</span>',
    licence: '<span class="badge bg-label-secondary"><i class="ti ti-star me-1"></i>Licence</span>',
  };
  const histMoney = (v) =>
    new Intl.NumberFormat("fr-FR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(v) || 0) + " DT";
  const histRows = (rows, empty) =>
    rows.length
      ? rows
          .map(
            (r) => `<tr>
              <td class="text-nowrap">${escapeHtml(r.d)}</td>
              <td>${HIST_TYPES[r.kind] || escapeHtml(r.kind)}</td>
              <td>${escapeHtml(r.objet)}</td>
              <td class="text-end text-nowrap">${histMoney(r.montant)}</td>
            </tr>`,
          )
          .join("")
      : `<tr><td colspan="4" class="text-center text-muted py-3">${empty}</td></tr>`;
  const historyModal = new bootstrap.Modal(document.getElementById("clientHistoryModal"));

  $(".datatables-clients tbody").on("click", ".view-history", function () {
    const client = table.row($(this).closest("tr")).data();
    $("#clientHistoryTitle").text("Historique — " + client.nom);
    $("#histTotalReceived, #histTotalUnpaid").text("…");
    $("#histUnpaid tbody, #histReceived tbody").html(
      '<tr><td colspan="4" class="text-center text-muted py-3">Chargement…</td></tr>',
    );
    historyModal.show();
    $.getJSON("api/client/history", { id: client.idclient })
      .done((res) => {
        if (!res.success) {
          historyModal.hide();
          showTopNotification(res.message, "danger", "ban");
          return;
        }
        const d = res.data;
        $("#histTotalReceived").text(histMoney(d.totalReceived));
        $("#histTotalUnpaid").text(histMoney(d.totalUnpaid));
        $("#histUnpaid tbody").html(histRows(d.unpaid, "Aucun impayé"));
        $("#histReceived tbody").html(histRows(d.received, "Aucun paiement reçu"));
      })
      .fail(() => {
        historyModal.hide();
        showTopNotification("Erreur lors de Traitement", "danger", "ban");
      });
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-clients tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Modifier le client";
    offCanvasElement.querySelector(".dt-Name").value = data.nom;
    offCanvasElement.querySelector(".dt-addr").value = data.adresse;
    offCanvasElement.querySelector(".dt-tel").value = data.tel;
    offCanvasElement.querySelector(".dt-email").value = data.email;
    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().idclient : null,
      nom: $("#Name").val(),
      adresse: $("#addr").val(),
      tel: $("#tel").val(),
      email: $("#email").val(),
    };
    let lnk = editingRow ? "api/client/update" : "api/client/create";
    let method = editingRow ? "PATCH" : "POST";
    $.ajax({
      url: lnk,
      type: method,
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
      error: function (xhr, status, error) {
        showTopNotification("Erreur lors de Traitement", "danger", "ban");
      },
    });
  });

  // ===============================
  // Événements de suppression
  // ===============================
  const deleteModalEl = document.getElementById("confirmDeleteModal");
  const deleteModal = new bootstrap.Modal(deleteModalEl);
  $(".datatables-clients tbody").on("click", ".delete-record", function () {
    clientToDelete = table.row($(this).closest("tr")).data().idclient;
    $("#confirmDeleteModal .modal-title").text("Confirmer la suppression");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir supprimer ce client ? Ses abonnements Stocki et son historique de paiements seront aussi supprimés.",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (clientToDelete) {
      $.ajax({
        url: "api/client/delete",
        type: "DELETE",
        contentType: "application/json",
        data: JSON.stringify({ clientToDelete }),
        dataType: "json",
        success: function (res) {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            clientToDelete = null;
            deleteModal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        error: function (xhr, status, error) {
          showTopNotification("Erreur lors de la suppression", "danger", "ban");
        },
      });
    }
  });
});
