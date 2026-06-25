"use strict";

// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  $(".datatables-maintain").DataTable({
    responsive: true,
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
        }
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

        </style>`
      );
    },
  });
});
