"use strict";

// ==========================
// INITIALISATION DES VARIABLES
// ==========================
const dataTableMaintainSelector = ".datatables-maintain";
const dataTableProjectsSelector = ".datatables-projects";
$(() => {
  $(dataTableMaintainSelector).DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: "../assets/json/maintenance.json",
    columns: [
      { data: null },
      { data: "id" }, 
      { data: "projet" },
      { data: "client" },
      { data: "montant" },
      { data: "statut" },
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
          `<div><strong>${data.nom}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${data.tel}</span></div>`,
      },
      { targets: 4, responsivePriority: 6 },
      {
        targets: 5,
        responsivePriority: 1,
        render: (data, type, row, meta) => {
          var checkboxId = `checkbox_${meta.row}`;
          var checked = data === "Payé" ? "checked" : "";
          var label = data;

          return `
    <div class="d-flex align-items-center">
      <div class="check_container ms-2">
        <input id="${checkboxId}" type="checkbox" class="check_input" ${checked} data-row="${
          meta.row
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
    ],
    dom:
      '<"card-header d-flex flex-column flex-md-row align-items-center justify-content-between"<"head-label text-center"><"dt-action-buttons text-end"B>>' +
      '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
      "t" +
      '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    responsive: {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: (row) => row.data()["projet"],
          // après ouverture du modal
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
          var isChecked = $(this).is(":checked");
          var badge = $(this).next(".badge");
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
  $(() => {
  $(dataTableProjectsSelector).DataTable({
    responsive: true,
    language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json" },
    ajax: "../assets/json/projectsdev.json",
    columns: [
      { data: null },
      { data: "id" },
      { data: "projectTitle" },
      { data: "client" },
      { data: "startDate" },
      { data: "renewalDate" },
      { data: "totalCost" },
      { data: "advancePaid" },
      { data: "balanceDue" },
      { data: null },
    ],
    columnDefs: [
      // ===============================
      // Définition des colonnes
      // ===============================
      { className: "control", orderable: false, targets: 0, searchable: false, render: () => "" },
      { targets: 1, orderable: true, searchable: false, responsivePriority: 4 },
      { targets: 2, orderable: false, responsivePriority: 2, render: (data) => `<span class="fw-bold">${data}</span>` },
      { 
        targets: 3, 
        responsivePriority: 2, 
        orderable: false, 
        render: (data) => `<div><strong>${data.name}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${data.phone}</span></div>` 
      },
      { targets: 4, responsivePriority: 6 },
      { targets: 5, responsivePriority: 6 },
      { 
        targets: 6, 
        responsivePriority: 4, 
        render: (data) => `<span class="badge bg-label-primary">${$.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(data)}</span>` 
      },
      { 
        targets: 7, 
        responsivePriority: 4, 
        render: (data) => `<span class="badge bg-label-success">${$.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(data)}</span>` 
      },
      { 
        targets: 8, 
        responsivePriority: 5, 
        render: (data) => {
          const formattedCost = $.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(data);
          return data > 0 ? `<span class="badge bg-label-danger">${formattedCost}</span>` : `<span class="badge bg-success">${formattedCost}</span>`;
        } 
      },
      { 
        targets: -1, 
        title: "Actions", 
        orderable: false, 
        searchable: false, 
        responsivePriority: 1, 
        render: () => (
          '<div class="d-inline-flex">' +
          '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
          '<i class="ti ti-dots"></i></a>' +
          '<div class="dropdown-menu dropdown-menu-end">' +
          '<a href="javascript:;" class="dropdown-item edit-record"><i class="ti ti-pencil me-1"></i> Modifier</a>' +
          '<a href="javascript:;" class="dropdown-item delete-record"><i class="ti ti-trash me-1"></i> Supprimer</a>' +
          '</div></div>'
        )
      }
    ],
    buttons: [
      { extend: "print", text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>', className: "btn btn-default" },
      { extend: "pdfHtml5", text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>', className: "btn btn-default" },
      { text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Projet</span>', className: "create-new btn btn-primary" },
    ],
    dom: '<"card-header d-flex flex-column flex-md-row align-items-center justify-content-between"<"head-label text-center"><"dt-action-buttons text-end"B>>' +
         '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
         "t" +
         '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    responsive: {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({ header: row => row.data()["projectTitle"] }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) => col.title !== "" ? 
            `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}"><td>${col.title}:</td><td>${col.data}</td></tr>` 
            : ""
          ).join("");
          return data ? $('<table class="table"/><tbody />').append(data) : false;
        }
      }
    },
    initComplete: function () {
      $(".even,.odd").hover(
        function () { $(this).data("bgcolor", $(this).css("background-color")).css("background-color", "#cce5ff"); },
        function () { $(this).css("background-color", $(this).data("bgcolor")); }
      );
    }
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-basic tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Modifier le projet";

    offCanvasElement.querySelector(".dt-projectTitle").value = data.projectTitle;
    offCanvasElement.querySelector(".dt-clientName").value = data.client.name;
    offCanvasElement.querySelector(".dt-telNumer").value = data.client.phone;
    offCanvasElement.querySelector(".dt-startDate").value = data.startDate;
    offCanvasElement.querySelector(".dt-renewDate").value = data.renewalDate;
    offCanvasElement.querySelector(".dt-totalCost").value = data.totalCost;
    offCanvasElement.querySelector(".dt-advancePaid").value = data.advancePaid;
    offCanvasElement.querySelector(".dt-balanceDue").value = data.balanceDue;

    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().id : Date.now(),
      projectTitle: $("#projectTitle").val(),
      client: { name: $("#clientName").val(), phone: $("#telNumer").val() },
      startDate: $("#startDate").val(),
      renewalDate: $("#renewDate").val(),
      totalCost: parseFloat($("#totalCost").val()),
      advancePaid: parseFloat($("#advancePaid").val()),
      balanceDue: parseFloat($("#balanceDue").val()),
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
  let rowToDelete = null; // Ligne à supprimer

  // Clic sur le bouton "Supprimer" dans le tableau
  $(".datatables-basic tbody").on("click", ".delete-record", function () {
    rowToDelete = table.row($(this).closest("tr")); // récupérer la ligne
    deleteModal.show(); // afficher le modal
  });

  // Confirmer la suppression
  $("#confirmDeleteBtn").on("click", function () {
    if (rowToDelete) {
      rowToDelete.remove().draw(); // supprimer la ligne
      rowToDelete = null;
      deleteModal.hide(); // fermer le modal
    }
  });
  });
});
