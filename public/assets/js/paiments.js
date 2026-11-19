"use strict";
let table;
// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  table =$(".datatables-paiments").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "paiments/getAll.php",
      dataSrc: "data",
    },
    columns: [
      { data: null }, // Colonne pour le contrôle responsive
      { data: "idclient" },
      { data: "nom" },
      { data: "email" },
      { data: "tel" },
      { data: "nbprojects" },
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
        searchable: false,
        responsivePriority: 3,
        className: "text-center",
      },
      {
        targets: 2,
        orderable: true,
        responsivePriority: 1,
        render: (data, type, row) =>
          `<span class="fw-bold">${data}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 4,
      },
      {
        targets: 4,
        responsivePriority: 5,
        render: (data) =>
          `<span><i class="ti ti-phone me-2"></i>${data}</span>`,
      },
      { targets: 5, responsivePriority: 2, className: "text-center" },
    ],
    buttons: [
      {
        extend: "print",
        title: "Nombre des Projets par client- Stocki Devit",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
      },
      {
        extend: "pdfHtml5",
        title: "Nombre des Projets par client- Stocki Devit",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
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
          header: (row) => row.data()["projectTitle"],
        }),
        type: "column",
        renderer: (api, rowIdx, columns) => {
          const data = $.map(columns, (col) =>
            col.title !== ""
              ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}"><td>${col.title}:</td><td>${col.data}</td></tr>`
              : "",
          ).join("");
          return data
            ? $('<table class="table"/><tbody />').append(data)
            : false;
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
          .datatables-paiments tbody tr:hover {
              cursor: pointer;
          }
        </style>
      `);
    },
  });
  $(".datatables-paiments tbody").on("click", "tr", function () {
    let id = table.row($(this).closest("tr")).data().idclient; // récupérer la ligne
    window.location.href = 'dashboard.php?page=listeprojects&id=' + id;
  });
});
