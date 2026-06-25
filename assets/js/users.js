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
    var substringMatcher = function (strs) {
      return function findMatches(q, cb) {
        var matches, substrRegex;
        matches = [];
        substrRegex = new RegExp(q, "i");
        $.each(strs, function (i, str) {
          if (substrRegex.test(str)) {
            matches.push(str);
          }
        });

        cb(matches);
      };
    };
    var itJobs = [
      // Management
      "Manager",
      "Directeur IT",
      // Développement
      "Développeur Frontend",
      "Développeur Backend",
      "Développeur Full-Stack",
      "Développeur Mobile",
      "Développeur Web",
      "Développeur Python",
      "Développeur Java",
      "Développeur PHP",
      "Développeur .NET",
      "Développeur C++",
      "Développeur Cloud",
      "Développeur DevOps",

      // Design / UI / UX
      "UI Designer",
      "UX Designer",
      "Web Designer",
      "Product Designer",
      "Graphiste digital",
      "Motion Designer",

      // Test & Contrôle qualité
      "QA Engineer",
      "Testeur logiciel",
      "Ingénieur automatisation de tests",
      "Analyste qualité",
      "Contrôleur qualité logiciel",

      // Gestion & Pilotage
      "Chef de projet IT",
      "Scrum Master",
      "Product Owner",
      "Business Analyst",
      "IT Project Manager",

      // Données & Systèmes
      "Data Scientist",
      "Data Analyst",
      "Data Engineer",
      "Administrateur Base de données",
      "Administrateur Systèmes & Réseaux",
      "Ingénieur Sécurité Informatique",
      "Architecte Logiciel",
      "Architecte Cloud",

      // Support & autres
      "Technicien support informatique",
      "Consultant ERP",
      "Consultant CRM",
      "Formateur IT",
    ];
    if (isRtl) {
      $(".typeahead").attr("dir", "rtl");
    }

    // Basic
    // --------------------------------------------------------------------
    $(".typeahead").typeahead(
      {
        hint: !isRtl,
        highlight: true,
        minLength: 1,
      },
      {
        name: "itJobs",
        source: substringMatcher(itJobs),
      },
    );
    $(".typeahead").on(
      "typeahead:select typeahead:autocomplete",
      function (e, suggestion) {
        $(this).val(suggestion); // met à jour la valeur native lue par FormValidation
        fv.revalidateField("role"); // force la revalidation du champ
      },
    );
    if (newRecord) {
      newRecord.addEventListener("click", function () {
        editingRow = null;
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML =
          "Nouveau utilisateur";

        // Réinitialiser les champs
        offCanvasElement.querySelector(".dt-userLName").value = "";
        offCanvasElement.querySelector(".dt-userFName").value = "";
        $(".typeahead").typeahead("val", "");
        offCanvasElement.querySelector(".dt-email").value = "";
        offCanvasElement.querySelector(".dt-mDp").value = "";
        offCanvasElement.querySelector(".dt-tel").value = "";
        fv.enableValidator("mDp", "notEmpty");
        offCanvasEl.show();
      });
    }
  }, 200);

  // ===============================
  // Validation du formulaire
  // ===============================
  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      userLName: {
        validators: {
          notEmpty: { message: "Le nom d'utilisateur est requis" },
        },
      },
      userFName: {
        validators: {
          notEmpty: { message: "Le prénom d'utilisateur est requis" },
        },
      },
      tel: {
        validators: {
          notEmpty: { message: "Le numéro de téléphone est requis" },
        },
      },
      role: {
        validators: {
          notEmpty: { message: "Le Rôle est requis" },
        },
      },
      email: {
        validators: {
          notEmpty: { message: "L'E-mail' est requis" },
          emailAddress: {
            message: "L'adresse e-mail n'est pas valide",
          },
        },
      },
      mDp: {
        validators: {
          notEmpty: { message: "Le mot de passe est requis" },
          stringLength: {
            min: 6,
            message: "Le mot de passe doit contenir au moins 6 caractères",
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
  table = $(".datatables-users").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "users/getAll.php",
      dataSrc: "data",
    },
    columns: [
      { data: null }, // Colonne pour le contrôle responsive
      { data: "iduser" },
      { data: "email" },
      { data: "nom" },
      { data: "prenom" },
      { data: "tel" },
      { data: "role" },
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
        responsivePriority: 3,
        render: (data) => `<span class="fw-bold">${data}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 2,
      },
      {
        targets: 4,
        responsivePriority: 5,
      },
      {
        targets: 5,
        render: (data) =>
          `<span><i class="ti ti-phone me-2"></i>${data}</span>`,
        responsivePriority: 6,
      },
      {
        targets: 6,
        className: "text-center",
        responsivePriority: 6,
        render: (data) =>
          `<strong class="badge ${
            data == "Manager" ? "bg-label-facebook" : "bg-label-success"
          }">${data}</strong>`,
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
        title: "Liste des Utilisateurs - Stocki Devit",
        text: '<i class="ti ti-printer me-sm-1"></i> <span class="d-none d-sm-inline-block">Imprimer</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Liste des Utilisateurs - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5],
        },
      },
      {
        text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Ajouter Utilisateur</span>',
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
  $(".datatables-users tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();

    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier l'utilisateur";
    offCanvasElement.querySelector(".dt-userLName").value = data.nom;
    offCanvasElement.querySelector(".dt-userFName").value = data.prenom;
    $(".typeahead").typeahead("val", data.role);
    offCanvasElement.querySelector(".dt-tel").value = data.tel;
    offCanvasElement.querySelector(".dt-email").value = data.email;
    offCanvasElement.querySelector(".dt-mDp").value = "";
    offCanvasElement.querySelector(".dt-mDp").placeholder =
      "Laisser vide pour ne pas modifier";
    fv.disableValidator("mDp", "notEmpty");
    offCanvasEl.show();
  });

  // ===============================
  // Soumission du formulaire
  // ===============================
  fv.on("core.form.valid", function () {
    const formData = {
      id: editingRow ? editingRow.data().iduser : null,
      nom: $("#userLName").val(),
      prenom: $("#userFName").val(),
      role: $("#role").val(),
      tel: $("#tel").val(),
      email: $("#email").val(),
      password: $("#mDp").val(),
    };
    let lnk;
    if (editingRow) {
      lnk = "users/update.php";
    } else {
      lnk = "users/add.php";
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
  const deleteModalEl = document.getElementById("confirmDeleteModal");
  const deleteModal = new bootstrap.Modal(deleteModalEl);
  let userToDelete;
  $(".datatables-users tbody").on("click", ".delete-record", function () {
    userToDelete = table.row($(this).closest("tr")).data().iduser;
    $(".modal-body").text(
      "Êtes-vous sûr de vouloir supprimer cet utilisateur ?",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (userToDelete) {
      const lnk = "users/delete.php";
      $.post(
        lnk,
        { userToDelete },
        (res) => {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            userToDelete = null;
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
