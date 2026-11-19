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
function updateStatusAjax(data) {
  $.ajax({
    url: "api/maintain/updatestatus",
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

// Liste déroulante des projets (recherche côté serveur)
function initProjectSelect() {
  $(".js-example-data-ajax").select2({
    placeholder: "Rechercher un projet...",
    width: "100%",
    dropdownParent: $("#add-new-record"),

    ajax: {
      url: "api/maintain/autoComplete",
      dataType: "json",
      delay: 250,

      processResults: function (response) {
        return {
          results: (response.data || []).map((item) => ({
            id: String(item.idproj),
            text: item.titre,
            nomclient: item.clnom,
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

  // Gestion du bouton "Créer un nouveau record"
  setTimeout(() => {
    const offCanvasElement = document.querySelector("#add-new-record");
    initProjectSelect();
    // Délégation : le bouton est créé par DataTables, parfois après ce délai
    {
      $(document).on("click", ".create-new", function () {
        editingRow = null;
        // repartir d'un formulaire vide (une modification a pu être abandonnée)
        fv.resetForm(true);
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML =
          "Nouveau Abonnement";
        $(".js-example-data-ajax").empty().val(null).trigger("change");
        offCanvasElement.querySelector(".dt-status").checked = false;
        updateStatus(offCanvasElement.querySelector(".dt-status"));
        offCanvasEl.show();
      });
    }
    offCanvasElement
      .querySelector("#resetBtn")
      .addEventListener("click", function () {
        fv.resetForm(true);
      });
    const proj = $(".js-example-data-ajax");
    proj.on("select2:select", function (e) {
      const data = e.params.data;
      $(".dt-nomclt").val(data.nomclient);
      fv.revalidateField("nomclt");
      fv.revalidateField("project");
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
            message: "Le montant doit être supérieur à 0",
            callback: function (input) {
              const montant = parseFloat(input.value);
              if (input.value === "") return true;
              return montant > 0;
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
  table = $(".datatables-maintain").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "api/maintain",
      dataSrc: "data",
    },
    columns: [
      { data: null },
      { data: "id" },
      { data: "titre" },
      { data: "client_nom" },
      { data: "echeance" },
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
        render: (data) => `<span class="fw-bold">${escapeHtml(data)}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 2,
        render: (data, type, row) =>
          `<div><strong>${escapeHtml(row.client_nom)}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${escapeHtml(row.client_tel)}</span></div>`,
      },
      {
        targets: 4,
        responsivePriority: 6,
        render: (data) =>
          `<span class="badge bg-label-primary">${formatMontant(data)}</span>`,
      },
      {
        targets: 5,
        responsivePriority: 1,
        render: (data, type, row) => {
          var checkboxId = `checkbox_${row.id}`;
          var checked = data === "paid" ? "checked" : "";
          var label = data === "paid" ? "Payé" : "Non payé";

          return `
    <div class="d-flex align-items-center">
      <div class="check_container ms-2">
        <input id="${checkboxId}" type="checkbox" class="check_input check_input_dt" ${checked} data-id="${
          row.id
        }">
        <label for="${checkboxId}" class="check_label badge ${
          checked ? "bg-label-success" : "bg-label-danger"
        } me-2" title="Changer le statut">
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
          header: (row) => escapeHtml(row.data()["titre"]),
          // après ouverture du modal
          focus: true,
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) => {
            if (col.title !== "" && col.title !== "Actions") {
              let content = col.data;
              if (col.columnIndex === 5) {
                const isPayed = content === "paid";
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
    },
  });

  $(".datatables-maintain tbody").on("change", ".check_input_dt", function () {
    const data = table.row($(this).closest("tr")).data();

    const updateData = {
      id: data.id,
      status: data.status === "paid" ? "unpaid" : "paid",
    };

    updateStatus(this);
    updateStatusAjax(updateData);
  });

  $(".datatables-maintain tbody").on("click", ".view-history", function () {
    const id = table.row($(this).closest("tr")).data().id;
    $.getJSON("api/maintain/history", { id }, (res) => {
      if (res.success) {
        makeTable(res.data);
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    }).fail(() =>
      showTopNotification("Erreur lors de Traitement", "danger", "ban"),
    );
  });

  $(".datatables-maintain tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Modifier Abonnement";
    offCanvasElement.querySelector(".dt-nomclt").value = data.client_nom;
    offCanvasElement.querySelector(".dt-cost").value = data.echeance;
    offCanvasElement.querySelector(".dt-status").checked =
      data.status === "paid";
    updateStatus(offCanvasElement.querySelector(".dt-status"));
    const $selectProject = $(offCanvasElement).find(".js-example-data-ajax");
    const option = new Option(data.titre, data.id_proj, true, true);
    $selectProject.empty().append(option).trigger("change");
    offCanvasEl.show();
  });
  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().id : null,
      idproj: $(".js-example-data-ajax").val(),
      montant: $("#cost").val(),
      status: $("#status").is(":checked") ? "paid" : "unpaid",
    };
    let lnk = editingRow ? "api/maintain/update" : "api/maintain/create";
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
  $(".datatables-maintain tbody").on("click", ".delete-record", function () {
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
        url: "api/maintain/delete",
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
