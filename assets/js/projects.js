"use strict";

// ===============================
// Déclarations globales
// ===============================
let fv, offCanvasEl, table;
let editingRow = null;
let projToDelete = null;

document.addEventListener("DOMContentLoaded", function () {
  // ===============================
  // Initialisation du formulaire et OffCanvas
  // ===============================
  (function () {
    const formAddNewRecord = document.getElementById("form-add-new-record");

    setTimeout(() => {
      const newRecord = document.querySelector(".create-new"),
        offCanvasElement = document.querySelector("#add-new-record");

      if (newRecord) {
        newRecord.addEventListener("click", function () {
          editingRow = null;
          offCanvasElement.classList.add("w-50");
          offCanvasElement
            .querySelector(".div-clientLName")
            .classList.add("col-md-6");
          offCanvasElement.querySelector(".div-tel").classList.add("col-md-6");
          offCanvasElement
            .querySelector(".div-renewDate")
            .classList.add("col-md-6");
          offCanvasElement
            .querySelector(".div-startDate")
            .classList.add("col-md-6");
          offCanvasElement
            .querySelector(".div-totalCost")
            .classList.add("col-md-4");
          offCanvasElement.querySelectorAll(".div-hide").forEach((e) => {
            e.classList.remove("d-none");
          });
          fv.enableValidator("email", "notEmpty");
          fv.enableValidator("addr", "notEmpty");
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);

          // Titre du formulaire
          offCanvasElement.querySelector("#titre").innerHTML = "Nouveau projet";

          // Réinitialiser les champs
          formAddNewRecord.reset();
          offCanvasElement.querySelector(".dt-clientLName").readOnly = false;
          offCanvasElement.querySelector(".dt-tel").readOnly = false;
          offCanvasElement.querySelector(".dt-addr").readOnly = false;
          fv.resetForm(true);
          offCanvasEl.show();
        });
        const emailInput = offCanvasElement.querySelector(".dt-email");
        emailInput.addEventListener("blur", function () {
          let emailtmp = this.value.trim();
          if (emailtmp === "") return;
          const lnk = "projects/getClient.php";
          $.post(
            lnk,
            { emailtmp },
            (res) => {
              if (res.success) {
                offCanvasElement.querySelector(".dt-clientLName").value =
                  res.nom;
                offCanvasElement.querySelector(".dt-tel").value = res.tel;
                offCanvasElement.querySelector(".dt-addr").value = res.adresse;
                offCanvasElement.querySelector(".dt-clientLName").readOnly =
                  true;
                offCanvasElement.querySelector(".dt-tel").readOnly = true;
                offCanvasElement.querySelector(".dt-addr").readOnly = true;
              } else {
                offCanvasElement.querySelector(".dt-clientLName").readOnly =
                  false;
                offCanvasElement.querySelector(".dt-tel").readOnly = false;
                offCanvasElement.querySelector(".dt-addr").readOnly = false;
              }
            },
            "json",
          );
        });
      }
    }, 200);

    // ===============================
    // Validation du formulaire
    // ===============================
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        projectTitle: {
          validators: {
            notEmpty: { message: "Le titre du projet est requis" },
          },
        },
        clientLName: {
          validators: { notEmpty: { message: "Le nom du client est requis" } },
        },
        tel: {
          validators: {
            notEmpty: { message: "Le numéro de téléphone est requis" },
          },
        },
        startDate: {
          validators: { notEmpty: { message: "La date de début est requise" } },
        },
        renewDate: {
          validators: {
            notEmpty: { message: "La date de renouvellement est requise" },
          },
        },
        totalCost: {
          validators: {
            notEmpty: { message: "Le coût total est requis" },
            numeric: { message: "Veuillez saisir un nombre valide" },
            callback: {
              message: "Le coût total ne peut pas être inférieur ou égale à 0",
              callback: function (input) {
                const coutTotal = parseFloat(input.value);
                if (input.value === "") return true;
                if (isNaN(coutTotal)) return true;
                return coutTotal > 0;
              },
            },
            enabled: true,
          },
        },
        addr: {
          validators: {
            notEmpty: { message: "L'adresse du client est requis" },
            enabled: true,
          },
        },
        email: {
          validators: {
            notEmpty: { message: "L'E-mail est requis" },
            emailAddress: { message: "L'adresse e-mail n'est pas valide" },
            enabled: true,
          },
        },
        advancePaid: {
          validators: {
            numeric: { message: "Veuillez saisir un nombre valide" },
            callback: {
              message: "L'avance ne peut pas dépasser le coût total",
              callback: function (input) {
                const coutTotal = parseFloat(
                  formAddNewRecord.querySelector("#totalCost").value,
                );
                const avance = parseFloat(input.value);
                if (input.value === "") return true;
                if (avance < 0) {
                  return {
                    valid: false,
                    message: "Le montant ne peut pas être inférieur à 0",
                  };
                }
                if (isNaN(coutTotal)) return true;
                return avance <= coutTotal;
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
  })();
});

// ===============================
// Initialisation DataTable
// ===============================
$(document).ready(function () {
  table = $(".datatables-projects").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "projects/getAll.php",
      dataSrc: "data",
    },
    columns: [
      { data: null },
      { data: "idproj" },
      { data: "titre" },
      { data: null },
      { data: "datedebut" },
      { data: "daterenouv" },
      { data: "couttot" },
      { data: null },
      { data: "restepaye" },
      { data: "acces" },
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
      {
        targets: 1,
        orderable: true,
        className: "text-center",
        searchable: false,
        responsivePriority: 4,
      },
      {
        targets: 2,
        width: "250px",
        orderable: true,
        responsivePriority: 2,
        render: (data) => `<span class="fw-bold">${data}</span>`,
      },
      {
        targets: 3,
        width: "250px",
        responsivePriority: 2,
        orderable: false,
        render: (data, type, row) =>
          `<div><strong>${row.clnom}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${row.cltel}</span></div>`,
      },
      {
        targets: 4,
        className: "text-center",
        width: "60px",
        responsivePriority: 6,
      },
      {
        targets: 5,
        className: "text-center",
        width: "60px",
        responsivePriority: 6,
      },
      {
        targets: 6,
        className: "text-center",
        responsivePriority: 4,
        render: (data) =>
          `<span class="badge bg-label-primary">${$.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(data)}</span>`,
      },
      {
        targets: 7,
        className: "text-center",
        responsivePriority: 4,
        render: (data, type, row) => {
          const paye = parseFloat(row.couttot) - parseFloat(row.restepaye);
          return `<span class="badge bg-label-success">${$.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(paye)}</span>`;
        },
      },
      {
        targets: 8,
        className: "text-center",
        responsivePriority: 5,
        render: (data) => {
          const formattedCost = $.fn.dataTable.render
            .number(" ", ",", 2, " ", " DT ")
            .display(data);
          return data > 0
            ? `<span class="badge bg-label-danger">${formattedCost}</span>`
            : `<span class="badge bg-success">Payé</span>`;
        },
      },
      {
        targets: 9,
        orderable: false,
        searchable: false,
        className: "text-center",
        responsivePriority: 9,
        render: () =>
          '<a href="javascript:;" class="show-data"><i class="ti ti-key me-1"></i></a>',
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
        title: "Liste des Projets - Stocki Devit",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7, 8],
        },
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
      },
      {
        extend: "pdfHtml5",
        title: "Liste des Projets - Stocki Devit",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7, 8],
        },
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
      },
      {
        text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Projet</span>',
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
    },
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-projects tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasElement.classList.remove("w-50");
    offCanvasElement
      .querySelector(".div-clientLName")
      .classList.remove("col-md-6");
    offCanvasElement.querySelector(".div-tel").classList.remove("col-md-6");
    offCanvasElement
      .querySelector(".div-renewDate")
      .classList.remove("col-md-6");
    offCanvasElement
      .querySelector(".div-startDate")
      .classList.remove("col-md-6");
    offCanvasElement
      .querySelector(".div-totalCost")
      .classList.remove("col-md-4");
    fv.disableValidator("email", "notEmpty");
    fv.disableValidator("addr", "notEmpty");
    offCanvasElement.querySelectorAll(".div-hide").forEach((e) => {
      e.classList.add("d-none");
    });
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML = "Modifier le projet";

    offCanvasElement.querySelector(".dt-projectTitle").value = data.titre;
    offCanvasElement.querySelector(".dt-clientLName").value = data.clnom;
    offCanvasElement.querySelector(".dt-tel").value = data.cltel;
    offCanvasElement.querySelector(".dt-clientLName").readOnly = true;
    offCanvasElement.querySelector(".dt-tel").readOnly = true;
    offCanvasElement.querySelector(".dt-startDate").value = data.datedebut;
    offCanvasElement.querySelector(".dt-renewDate").value = data.daterenouv;
    offCanvasElement.querySelector(".dt-totalCost").value = data.couttot;
    offCanvasElement.querySelector(".dt-acces").value = data.acces;
    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      idproj: editingRow ? editingRow.data().idproj : null,
      titre: $("#projectTitle").val(),
      clnom: $("#clientLName").val(),
      email: $("#email").val(),
      adresse: $("#addr").val(),
      tel: $("#tel").val(),
      datedebut: $("#startDate").val(),
      daterenouv: $("#renewDate").val(),
      couttot: $("#totalCost").val(),
      avancepaye: $("#advancePaid").val(),
      comment: $("#comment").val(),
      acces: $("#acces").val(),
    };
    let lnk;
    if (editingRow) {
      lnk = "projects/update.php";
    } else {
      lnk = "projects/add.php";
    }
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
  const ModalEl = document.getElementById("confirmDeleteModal");
  const Modal = new bootstrap.Modal(ModalEl); // Ligne à supprimer

  // Clic sur le bouton "Supprimer" dans le tableau
  $(".datatables-projects tbody").on("click", ".delete-record", function () {
    projToDelete = table.row($(this).closest("tr")).data().idproj; // récupérer la ligne
    $("#confirmDeleteModal .modal-title").html("Confirmer la suppression");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir supprimer ce projet ?",
    );
    Modal.show(); // afficher le modal
  });

  // Confirmer la suppression
  $("#confirmDeleteBtn").on("click", function () {
    if (projToDelete) {
      const lnk = "projects/delete.php";
      $.post(
        lnk,
        { projToDelete },
        (res) => {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            projToDelete = null;
            Modal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        "json",
      );
    }
  });
  // ===============================
  // Événements de show data
  // ===============================

  // Clic sur le bouton "Supprimer" dans le tableau
  $(".datatables-projects tbody").on("click", ".show-data", function () {
    const text = table.row($(this).closest("tr")).data().acces;
    console.log(text);
    $("#confirmDeleteModal .modal-title").html(
      '<i class="ti ti-key me-2"></i> Données d\'accès',
    );
    $("#confirmDeleteModal .modal-body").html(
      '<textarea class="form-control" id="dataTextArea" rows="5" readOnly></textarea>',
    );
    $("#dataTextArea").val(text);
    $("#confirmDeleteBtn").addClass("d-none");
    $("#closeBtn").html("Fermer");
    Modal.show(); // afficher le modal
  });
  $("#confirmDeleteModal").on("hidden.bs.modal", function () {
    $("#confirmDeleteBtn").removeClass("d-none");
    $("#confirmDeleteModal .modal-title").html("");
    $("#confirmDeleteModal .modal-body").html("");
    $("#closeBtn").html("Annuler");
  });
});
