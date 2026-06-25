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
        offCanvasElement.querySelector("#titre").innerHTML = "Nouveau client";

        // Réinitialiser les champs
        offCanvasElement.querySelector(".dt-Name").value = "";
        offCanvasElement.querySelector(".dt-email").value = "";
        offCanvasElement.querySelector(".dt-addr").value = "";
        offCanvasElement.querySelector(".dt-tel").value = "";
        offCanvasEl.show();
      });
    }
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
      url: "clients/getAll.php",
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
      { targets: 1, orderable: true,className: "text-center", searchable: false, responsivePriority: 4 },
      {
        targets: 2,
        orderable: true,
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
          `<span><i class="ti ti-phone me-2"></i>${data}</span>`,
      },
      {
        targets: 5,
        responsivePriority: 6,
        render: (data) =>
          `<span><i class="ti ti-map-pin me-2"></i>${data}</span>`,
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
          header: (row) => row.data()["nom"] + " " + row.data()["prenom"],
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) =>
            col.title !== ""
              ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                  <td>${col.title}:</td><td>${col.data}</td>
                </tr>`
              : "",
          ).join("");

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
  // Événements de modification
  // ===============================
  $(".datatables-clients tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier le client";
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
    let lnk;
    if (editingRow) {
      lnk = "clients/update.php";
    } else {
      lnk = "clients/add.php";
    }
    $.post(
      lnk,
      formData,
      (res) => {
        if (res.success) {
          table.ajax.reload(null, false);
          showTopNotification(res.message, "success","check");
          offCanvasEl.hide();
          document.getElementById("form-add-new-record").reset();
          editingRow = null;
        } else {
          showTopNotification(res.message, "danger","ban");
        }
      },
      "json",
    );
  });

  // ===============================
  // Événements de suppression
  // ===============================
  const deleteModalEl = document.getElementById("confirmDeleteModal");
  const deleteModal = new bootstrap.Modal(deleteModalEl);
  let clientToDelete;
  $(".datatables-clients tbody").on("click", ".delete-record", function () {
    clientToDelete = table.row($(this).closest("tr")).data().idclient;
    $(".modal-body").text("Êtes-vous sûr de vouloir supprimer ce client ?");
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (clientToDelete) {
      const lnk = "clients/delete.php";
      $.post(
        lnk,
        { clientToDelete },
        (res) => {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success","check");
            clientToDelete = null;
            deleteModal.hide();
          } else {
            showTopNotification(res.message, "danger","ban");
          }
        },
        "json",
      );
    }
  });
});
