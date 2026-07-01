"use strict";

let fv;
let offCanvasEl;
let table;
let editingRow = null;
let abonnToDelete = null;

$(document).on("change", ".update-status", function () {
  updateStatus(this);
});
function updateStatus(element, other = null) {
  const isChecked = $(element).is(":checked");
  const targetinput = other ? $(other) : $(element);
  const label = targetinput.next(".check_label");
  if (other) targetinput.prop("checked", isChecked);
  label.text(isChecked ? "Payé" : "Non payé");
  label.toggleClass("bg-label-success", isChecked);
  label.toggleClass("bg-label-danger", !isChecked);
}
function updateStatusAjax(data) {
  $.post(
    "maintains/updateStatus.php",
    data,
    (res) => {
      if (res.success) {
        table.ajax.reload(null, false);
        showTopNotification(res.message, "success", "check");
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    },
    "json",
  );
}

function getHistory(idIN) {
  let data;
  $.post(
    "maintains/getAllByProject.php",
    { idIN },
    (res) => {
      if (res.success) {
        makeTable(res);
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    },
    "json",
  );
}

function makeTable(data) {
  let $his_table = $("#historyTable tbody");
  let headers = `<th>Montant</th><th>Date</th>`;
  $his_table.html(headers);
  let rows = "";

  if (data.length > 0) {
    data.forEach((h) => {
      rows += `
      <tr>
        <td>${h.montant} TND</td>
        <td>${h.payed_at}</td>
      </tr>
    `;
    });
  } else {
    rows = `<tr><td colspan="3" class="text-center">Aucun paiement effectué</td></tr>`;
  }
  $his_table.html(rows);
  $("#historyModal").modal("show");
}
// ===============================
// DOMContentLoaded : Initialisation du formulaire et OffCanvas
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const formAddNewRecord = document.getElementById("form-add-new-record");

  // Gestion du bouton "Créer un nouveau record"
  setTimeout(() => {
    const newRecord = document.querySelector(".create-new");
    const offCanvasElement = document.querySelector("#add-new-record");
    $(".js-example-data-ajax").select2({
      placeholder: "Rechercher un projet...",
      width: "100%",

      dropdownParent: $("#add-new-record"),

      ajax: {
        url: "maintains/getAllSelect.php",
        dataType: "json",
        delay: 250,
        processResults: function (data) {
          return {
            results: data.map((item) => ({
              id: String(item.idproj),
              text: item.titre,
              idclient: String(item.idclient),
              nomclient: item.clnom,
            })),
          };
        },
      },
    });
    if (newRecord) {
      newRecord.addEventListener("click", function () {
        editingRow = null;
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML =
          "Nouveau Abonnement";
        fv.resetForm(true);
        formAddNewRecord.reset();
        $(".js-example-data-ajax").val(null).trigger("change");
        updateStatus(offCanvasElement.querySelector(".dt-status"));
        offCanvasEl.show();
      });
    }
    const proj = $(".js-example-data-ajax");
    proj.on("select2:select", function (e) {
      const data = e.params.data;
      $(".dt-nomclt").val(data.nomclient);
    });
  }, 200);

  // ===============================
  // Validation du formulaire
  // ===============================
  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      project: {
        validators: {
          notEmpty: { message: "Le titre de projet est requis" },
        },
      },
      nomclt: {
        validators: {
          notEmpty: { message: "Le nom du client est requis" },
        },
      },
      cost: {
        validators: {
          // numeric: { message: "Veuillez saisir un montant valide" },
          notEmpty: { message: "Le montant est requis" },
          callback: {
            message: "Le montant ne peut pas être inférieur à 0",
            callback: function (input) {
              const montant = parseFloat(input.value);
              if (input.value === "") return true;
              return montant > 0;
            },
          },
          enabled: true,
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
  table = $(".datatables-maintain").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "maintains/getAll.php",
      dataSrc: "data",
    },
    columns: [
      { data: null },
      { data: "id" },
      { data: "titre" },
      { data: "nom" },
      { data: "montant" },
      { data: "status" },
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
        render: (data, type, row) =>
          `<div><strong>${row.nom}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${row.tel}</span></div>`,
      },
      { targets: 4, responsivePriority: 6 },
      {
        targets: 5,
        responsivePriority: 1,
        render: (data, type, row) => {
          var checkboxId = `checkbox_${row.id}`;
          var checked = data === "payer" ? "checked" : "";
          var label = data === "payer" ? "Payé" : "Non payé";

          return `
    <div class="d-flex align-items-center">
      <div class="check_container ms-2">
        <input id="${checkboxId}" type="checkbox" class="check_input check_input_dt" ${checked} data-id="${
          row.id
        }">
        <label for="${checkboxId}" class="check_label badge ${
          checked ? "bg-label-success" : "bg-label-danger"
        } me-2" titlae="Changer le statut">
          ${label}
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
        title: "Abonnements de maintenance - Stocki Devit",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Abonnements de maintenance - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
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
          header: (row) => row.data()["titre"],
          // après ouverture du modal
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) => {
            if (col.title !== "" && col.title !== "Actions") {
              let content = col.data;
              if (col.columnIndex === 5) {
                const isPayed = content === "payer";
                content = `<span class="badge ${isPayed ? "bg-label-success" : "bg-label-danger"}">${isPayed ? "Payé" : "Non payé"}</span>`;
              }

              return `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
               <td>${col.title}:</td><td>${content}</td>
             </tr>`;
            }
            return "";
          }).join("");

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
        },
      );
      $("head").append(
        `<style>
            /* Masquer l’input natif */
            .check_input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            /* Quand la case est cochée → changer le badge */
            .check_input:checked + .badge {
                background-color: #28a745 !important; /* vert */
                color: #fff !important;
                box-shadow: 0 0 10px rgba(40,167,69,0.6);
            }
            .check_label {
            cursor: pointer;           /* indique qu’on peut cliquer */
           transition: transform 0.2s ease, box-shadow 0.2s ease;
          }

          .check_label:hover {
          transform: scale(1.05);    /* léger zoom au survol */
          box-shadow: 0 2px 6px rgba(0,0,0,0.15);
          }

        </style>`,
      );
    },
  });

  $(".datatables-maintain tbody").on("change", ".check_input_dt", function () {
    const data = table.row($(this).closest("tr")).data();

    const updateData = {
      id: data.id,
      cost: data.montant,
      status: data.status == "payer" ? "inpayer" : "payer",
    };

    updateStatus(this);
    updateStatusAjax(updateData);
  });

  $(".datatables-maintain tbody").on("click", ".view-history", function () {
    const id = table.row($(this).closest("tr")).data().id;
    $.post(
      "maintains/getAllByProject.php",
      { id },
      (res) => {
        if (res) {
          makeTable(res.data);
        } else {
          showTopNotification(res.message, "danger", "ban");
        }
      },
      "json",
    );
  });

  $(".datatables-maintain tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Modifier Abonnement";
    offCanvasElement.querySelector(".dt-nomclt").value = data.nom;
    offCanvasElement.querySelector(".dt-cost").value = data.montant;
    offCanvasElement.querySelector(".dt-status").checked =
      data.status === "payer";
    updateStatus(offCanvasElement.querySelector(".dt-status"));
    const $selectProject = $(offCanvasElement).find(".js-example-data-ajax");
    const option = new Option(data.titre, data.idproj, true, true);
    $selectProject.append(option);
    const select2Data = $selectProject.select2("data")[0];
    select2Data.idclient = data.idclient;
    select2Data.nomclient = data.nom;
    $selectProject.trigger("change");
    offCanvasEl.show();
  });
  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const proj = $(".js-example-data-ajax").select2("data")[0];
    const formData = {
      id: editingRow ? editingRow.data().id : null,
      idproj: proj.id,
      idclient: proj.idclient,
      montant: $("#cost").val(),
      nomclt: proj.nomclient,
      status: $("#status").is(":checked") ? "payer" : "inpayer",
    };
    let lnk = editingRow ? "maintains/update.php" : "maintains/add.php";

    $.post(
      lnk,
      formData,
      (res) => {
        if (res.success) {
          table.ajax.reload(null, false);
          showTopNotification(res.message, "success", "check");
          offCanvasEl.hide();
          document.getElementById("form-add-new-record").reset();
          editingRow = null;
        } else {
          showTopNotification(res.message, "danger", "ban");
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
  $(".datatables-maintain tbody").on("click", ".delete-record", function () {
    abonnToDelete = table.row($(this).closest("tr")).data().id;
    $(".modal-body").text(
      "Êtes-vous sûr de vouloir supprimer cet abonnement ?",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (abonnToDelete) {
      const lnk = "maintains/delete.php";
      $.post(
        lnk,
        { abonnToDelete },
        (res) => {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            abonnToDelete = null;
            deleteModal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        "json",
      );
    }
  });
});
