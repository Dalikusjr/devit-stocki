"use strict";

// ===============================
// Déclarations globales
// ===============================
let fv, offCanvasEl, table;
let editingRow = null;
let projToDelete = null;
let projToArchive = null;

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

// Fonction pour générer le contenu de l'accordéon
function formatDetails(echeances) {
  if (!Array.isArray(echeances) || echeances.length === 0) {
    return '<div class="p-3 text-center">Aucune échéance trouvée pour ce projet.</div>';
  }
  let html = `
        <table class="table table-sm table-striped w-100 mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Commentaire</th>
                    <th>Montant</th>
                    <th>Date d'échéance</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
    `;
  echeances.forEach((e) => {
    html += `
            <tr>
                <td>${e.idpay}</td>
                <td>${escapeHtml(e.commentaire)}</td>
                <td>${Number(e.echeance).toFixed(2)} DT</td>
                <td>${e.dateech}</td>
                <td>
                    <span class="badge bg-label-${e.status === "paid" ? "success" : "danger"}">
                        ${e.status === "paid" ? "Payée" : "Impayée"}
                    </span>
                </td>
            </tr>
        `;
  });
  html += `</tbody></table>`;
  return html;
}
document.addEventListener("DOMContentLoaded", function () {
  // ===============================
  // Initialisation du formulaire et OffCanvas
  // ===============================
  (function () {
    const formAddNewRecord = document.getElementById("form-add-new-record");
    if (!window.IS_ADMIN) {
      formAddNewRecord.querySelector(".div-acces").classList.add("d-none");
    }

    setTimeout(() => {
      const offCanvasElement = document.querySelector("#add-new-record");

      if (offCanvasElement) {
        // Délégation : le bouton est créé par DataTables, parfois après ce délai
        $(document).on("click", ".create-new", function () {
          if (editingRow) {
            editingRow = null;
            fv.resetForm(true);
          }
          offCanvasElement
            .querySelector(".dt-totalCost")
            .removeAttribute("disabled");
          offCanvasElement
            .querySelector(".dt-nbrInstall")
            .removeAttribute("disabled");
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
          offCanvasElement.querySelector(".dt-clientLName").readOnly = false;
          offCanvasElement.querySelector(".dt-tel").readOnly = false;
          offCanvasElement.querySelector(".dt-addr").readOnly = false;
          offCanvasEl.show();
        });
        offCanvasElement
          .querySelector("#resetBtn")
          .addEventListener("click", function () {
            fv.resetForm(true);
          });
        const emailInput = offCanvasElement.querySelector(".dt-email");
        emailInput.addEventListener("blur", function () {
          let emailtmp = this.value.trim();
          if (emailtmp === "") return;
          $.post(
            "api/project/client",
            { emailtmp },
            (res) => {
              if (res.success && res.data) {
                offCanvasElement.querySelector(".dt-clientLName").value =
                  res.data.nom;
                offCanvasElement.querySelector(".dt-tel").value = res.data.tel;
                offCanvasElement.querySelector(".dt-addr").value =
                  res.data.adresse;
                offCanvasElement.querySelector(".dt-clientLName").readOnly =
                  true;
                offCanvasElement.querySelector(".dt-tel").readOnly = true;
                offCanvasElement.querySelector(".dt-addr").readOnly = true;
              } else {
                offCanvasElement.querySelector(".dt-clientLName").readOnly =
                  false;
                offCanvasElement.querySelector(".dt-tel").readOnly = false;
                offCanvasElement.querySelector(".dt-addr").readOnly = false;
                if (!res.success) {
                  showTopNotification(res.message, "danger", "ban");
                }
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
          },
        },
        nbrInstall: {
          validators: {
            notEmpty: { message: "Le nombre d'échéance est requis" },
            numeric: { message: "Veuillez saisir un nombre valide" },
            callback: {
              message: "Le nombre d'échéance doit être 1, 2 ou 3",
              callback: function (input) {
                if (input.value === "" || input.disabled) return true;
                // en modification, un nombre inchangé reste accepté
                if (editingRow && input.value == editingRow.data().nbech)
                  return true;
                return /^[1-3]$/.test(input.value.trim());
              },
            },
          },
        },
        addr: {
          validators: {
            notEmpty: { message: "L'adresse du client est requis" },
          },
        },
        email: {
          validators: {
            notEmpty: { message: "L'E-mail est requis" },
            emailAddress: { message: "L'adresse e-mail n'est pas valide" },
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
      url: "api/project",
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
      { data: "payer" },
      { data: "reste" },
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
        render: (data) => `<span class="fw-bold">${escapeHtml(data)}</span>`,
      },
      {
        targets: 3,
        width: "250px",
        responsivePriority: 2,
        orderable: false,
        render: (data, type, row) =>
          `<div><strong>${escapeHtml(row.clnom)}</strong><br><span class="text-muted"><i class="ti ti-phone me-2"></i>${escapeHtml(row.cltel)}</span></div>`,
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
        render: (data) => {
          return `<span class="badge bg-label-success">${$.fn.dataTable.render.number(" ", ",", 2, " ", " DT ").display(data)}</span>`;
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
        // accès visibles des seuls administrateurs (le serveur ne les envoie pas aux autres)
        render: () =>
          window.IS_ADMIN
            ? '<a href="javascript:;" class="show-data"><i class="ti ti-key me-1"></i></a>'
            : '<span class="text-muted" title="Réservé aux administrateurs"><i class="ti ti-lock me-1"></i></span>',
      },
      {
        targets: -1,
        title: "Actions",
        className: "text-center",
        orderable: false,
        searchable: false,
        responsivePriority: 1,
        render: (data, type, row) => {
          const action =
            row.status == "completed"
              ? '<a href="javascript:;" class="dropdown-item archive-record"><i class="ti ti-archive me-1"></i> Archiver</a>'
              : '<a href="javascript:;" class="dropdown-item delete-record"><i class="ti ti-trash me-1"></i> Supprimer</a>';
          return (
            '<div class="d-inline-flex">' +
            '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
            '<i class="ti ti-dots"></i></a>' +
            '<div class="dropdown-menu dropdown-menu-end">' +
            '<a href="javascript:;" class="dropdown-item edit-record"><i class="ti ti-pencil me-1"></i> Modifier</a>' +
            action +
            "</div></div>"
          );
        },
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
          header: (row) => escapeHtml(row.data()["titre"]),
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
  $(".datatables-projects tbody").on("click", "tr:not(.child)", function (e) {
    if ($(e.target).closest("a, button, .dropdown-menu, td.control").length)
      return;
    let tr = $(this);
    let row = table.row(tr);
    if (!row.data()) return;
    let projectId = row.data().idproj;

    if (row.child.isShown()) {
      tr.next("tr")
        .find(".details-content")
        .slideUp(200, function () {
          row.child.hide();
          tr.removeClass("shown");
        });

      return;
    }

    row
      .child(
        `
        <div class="details-content text-center py-3">
            <i class="bx bx-loader-alt bx-spin"></i> Chargement...
        </div>
    `,
      )
      .show();

    tr.addClass("shown");

    $.ajax({
      url: "api/project/installment",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({ projectId }),
      dataType: "json",
      success: function (res) {
        if (res.success) {
          row
            .child(
              `
                    <div class="details-content" style="display:none;">
                        ${formatDetails(res.data)}
                    </div>
                `,
            )
            .show();

          tr.next("tr").find(".details-content").slideDown(250);
        } else {
          row.child.hide();
          tr.removeClass("shown");
          showTopNotification(res.message, "danger", "ban");
        }
      },
      error: function () {
        row.child.hide();
        tr.removeClass("shown");
        showTopNotification("Erreur lors du traitement", "danger", "ban");
      },
    });
  });
  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-projects tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasElement.querySelector(".dt-totalCost").removeAttribute("disabled");
    offCanvasElement
      .querySelector(".dt-nbrInstall")
      .removeAttribute("disabled");
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
    offCanvasElement.querySelector(".dt-nbrInstall").value = data.nbech;
    if (data.payer > 0) {
      offCanvasElement
        .querySelector(".dt-totalCost")
        .setAttribute("disabled", "");
      offCanvasElement
        .querySelector(".dt-nbrInstall")
        .setAttribute("disabled", "");
    }
    offCanvasElement.querySelector(".dt-acces").value = data.acces ?? "";
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
      nbrEch: $("#nbrInstall").val(),
    };
    let lnk = editingRow ? "api/project/update" : "api/project/create";
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
  const ModalEl = document.getElementById("confirmDeleteModal");
  const Modal = new bootstrap.Modal(ModalEl);

  // Clic sur le bouton "Supprimer" dans le tableau
  $(".datatables-projects tbody").on("click", ".delete-record", function () {
    projToArchive = null;
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
      $.ajax({
        url: "api/project/delete",
        type: "DELETE",
        contentType: "application/json",
        data: JSON.stringify({ projToDelete }),
        dataType: "json",
        success: function (res) {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            projToDelete = null;
            Modal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        error: function (xhr, status, error) {
          showTopNotification("Erreur lors de Traitement", "danger", "ban");
        },
      });
    }
  });
  // ===============================
  // Événements d'archivage
  // ===============================

  // Clic sur le bouton "Archiver" dans le tableau
  $(".datatables-projects tbody").on("click", ".archive-record", function () {
    projToDelete = null;
    projToArchive = table.row($(this).closest("tr")).data().idproj;
    $("#confirmDeleteModal .modal-title").html("Confirmer l'archivage");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir archiver ce projet ?",
    );
    Modal.show();
  });

  // Confirmer l'archivage
  $("#confirmDeleteBtn").on("click", function () {
    if (projToArchive) {
      $.ajax({
        url: "api/project/archive",
        type: "PATCH",
        contentType: "application/json",
        data: JSON.stringify({ projToArchive }),
        dataType: "json",
        success: function (res) {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            projToArchive = null;
            Modal.hide();
          } else {
            showTopNotification(res.message, "danger", "ban");
          }
        },
        error: function (xhr, status, error) {
          showTopNotification("Erreur lors de Traitement", "danger", "ban");
        },
      });
    }
  });
  // ===============================
  // Événements de show data
  // ===============================

  // Clic sur le bouton "Supprimer" dans le tableau
  $(".datatables-projects tbody").on("click", ".show-data", function () {
    const text = table.row($(this).closest("tr")).data().acces;
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
    projToDelete = null;
    projToArchive = null;
    $("#confirmDeleteBtn").removeClass("d-none");
    $("#confirmDeleteModal .modal-title").html("");
    $("#confirmDeleteModal .modal-body").html("");
    $("#closeBtn").html("Annuler");
  });
});
