"use strict";

// ===============================
// Déclarations globales
// ===============================
let fv;
let offCanvasEl;
let table;
let editingRow = null;
let rowToDelete = null;

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
// Rôles proposés, par catégorie (on peut aussi saisir un rôle libre, 40 caractères max)
// La couleur du badge suit la catégorie.
// ===============================
const ROLE_GROUPS = [
  { label: "Direction", badge: "bg-label-primary", roles: [
    "Gérant", "Manager", "Directeur IT", "Directeur commercial", "Directeur administratif et financier",
  ] },
  { label: "Gestion de projet", badge: "bg-label-info", roles: [
    "Chef de projet IT", "Product Owner", "Scrum Master", "Business Analyst",
  ] },
  { label: "Développement", badge: "bg-label-success", roles: [
    "Développeur Full-Stack", "Développeur Frontend", "Développeur Backend", "Développeur Mobile",
    "Développeur PHP", "Développeur Java", "Développeur Python", "Développeur .NET",
    "Développeur DevOps", "Architecte logiciel", "Stagiaire développeur",
  ] },
  { label: "Design", badge: "bg-label-warning", roles: [
    "UI/UX Designer", "Web Designer", "Graphiste", "Motion Designer",
  ] },
  { label: "Qualité et tests", badge: "bg-label-dark", roles: [
    "QA Engineer", "Testeur logiciel", "Ingénieur automatisation de tests",
  ] },
  { label: "Infrastructure et données", badge: "bg-label-secondary", roles: [
    "Administrateur systèmes et réseaux", "Administrateur base de données", "Ingénieur cloud",
    "Ingénieur sécurité informatique", "Data Analyst", "Data Engineer",
  ] },
  { label: "Stocki et relation client", badge: "bg-label-danger", roles: [
    "Commercial", "Chargé de clientèle", "Support client Stocki", "Formateur Stocki",
    "Technicien support informatique", "Consultant ERP", "Consultant CRM",
  ] },
  { label: "Administration et finance", badge: "bg-label-facebook", roles: [
    "Comptable", "Responsable administratif", "Assistant administratif", "Ressources humaines",
  ] },
];
const OTHER_ROLES_LABEL = "Autres rôles utilisés";

// Comparaison sans accents ni majuscules : « developpeur » trouve « Développeur »
function normalizeRole(value) {
  return String(value ?? "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim();
}
function roleBadgeClass(role) {
  const key = normalizeRole(role);
  const group = ROLE_GROUPS.find((g) => g.roles.some((r) => normalizeRole(r) === key));
  return group ? group.badge : "bg-label-secondary";
}

// Liste déroulante groupée (select2) : rôles du catalogue + rôles déjà attribués, saisie libre possible
function buildRoleOptions(usedRoles) {
  const known = new Set(ROLE_GROUPS.flatMap((g) => g.roles.map(normalizeRole)));
  const others = [...new Set(usedRoles.filter((r) => r && !known.has(normalizeRole(r))))].sort((a, b) =>
    a.localeCompare(b, "fr"),
  );
  const groups = others.length ? [...ROLE_GROUPS, { label: OTHER_ROLES_LABEL, roles: others }] : ROLE_GROUPS;
  return groups.map((g) => ({ text: g.label, children: g.roles.map((r) => ({ id: r, text: r })) }));
}
function initRoleSelect(usedRoles) {
  const $role = $("#role");
  const current = $role.val();
  if ($role.hasClass("select2-hidden-accessible")) $role.select2("destroy");
  $role.empty().append(new Option("", "", false, false));
  $role.select2({
    data: buildRoleOptions(usedRoles),
    placeholder: "Choisir ou saisir un rôle",
    allowClear: true,
    tags: true, // rôle libre
    width: "100%",
    dropdownParent: $("#add-new-record"),
    language: {
      noResults: () => "Aucun rôle trouvé",
      searching: () => "Recherche…",
    },
    // recherche insensible aux accents, dans le rôle et dans le nom de la catégorie
    matcher: (params, data) => {
      const term = normalizeRole(params.term);
      if (!term) return data;
      if (data.children) {
        const groupMatch = normalizeRole(data.text).includes(term);
        const children = groupMatch ? data.children : data.children.filter((c) => normalizeRole(c.text).includes(term));
        return children.length ? $.extend({}, data, { children }) : null;
      }
      return normalizeRole(data.text).includes(term) ? data : null;
    },
    // rôle libre : 40 caractères maximum, et pas de doublon d'un rôle existant (accents/majuscules)
    createTag: (params) => {
      const term = String(params.term ?? "").trim().replace(/\s+/g, " ");
      if (!term || term.length > 40) return null;
      const exists = $role.find("option").toArray().some((o) => normalizeRole(o.value) === normalizeRole(term));
      return exists ? null : { id: term, text: term, newTag: true };
    },
    // « Ajouter « … » » en dernier : Entrée choisit d'abord un rôle existant correspondant
    insertTag: (data, tag) => data.push(tag),
    templateResult: (data) =>
      data.newTag ? $("<span>").append($("<i class='ti ti-plus me-1'>"), document.createTextNode(`Ajouter « ${data.text} »`)) : data.text,
  });
  if (current) setRoleValue(current);
}
function setRoleValue(role) {
  const $role = $("#role");
  if (role && !$role.find("option").toArray().some((o) => o.value === role)) {
    $role.append(new Option(role, role, false, false));
  }
  $role.val(role || null).trigger("change.select2"); // affichage seulement
}

// ===============================
// DOMContentLoaded : Initialisation du formulaire et OffCanvas
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const formAddNewRecord = document.getElementById("form-add-new-record");

  // Gestion du bouton "Créer un nouveau record"
  setTimeout(() => {
    const offCanvasElement = document.querySelector("#add-new-record");
    // liste des rôles prête dès l'ouverture, complétée quand le tableau est chargé
    initRoleSelect([]);
    // validation seulement sur un choix de l'utilisateur (pas lors d'une remise à zéro)
    $("#role").on("select2:select select2:clear", function () {
      if (fv) fv.revalidateField("role");
    });
    // Délégation : le bouton est créé par DataTables, parfois après ce délai
    {
      $(document).on("click", ".create-new", function () {
        editingRow = null;
        // repartir d'un formulaire vide (une modification a pu être abandonnée)
        fv.resetForm(true);
        setRoleValue("");
        offCanvasElement.querySelector(".dt-mDp").placeholder = "*********";
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        // Titre du formulaire
        offCanvasElement.querySelector("#titre").innerHTML =
          "Nouveau utilisateur";

        fv.enableValidator("mDp", "notEmpty");
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
          notEmpty: { message: "Le rôle est requis" },
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
      mDp: {
        validators: {
          notEmpty: { message: "Le mot de passe est requis" },
          callback: {
            message:
              "8 caractères minimum, dont une minuscule et un chiffre, un symbole ou un espace",
            callback: (input) =>
              input.value === "" ||
              (input.value.length >= 8 &&
                /\p{Ll}/u.test(input.value) &&
                /[^\p{L}]/u.test(input.value)),
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
  table = $(".datatables-users").DataTable({
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
    },
    ajax: {
      url: "api/user",
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
        render: (data) => `<span class="fw-bold">${escapeHtml(data)}</span>`,
      },
      {
        targets: 3,
        responsivePriority: 2,
        render: (data) => escapeHtml(data),
      },
      {
        targets: 4,
        responsivePriority: 5,
        render: (data) => escapeHtml(data),
      },
      {
        targets: 5,
        render: (data) =>
          `<span><i class="ti ti-phone me-2"></i>${escapeHtml(data)}</span>`,
        responsivePriority: 6,
      },
      {
        targets: 6,
        className: "text-center",
        responsivePriority: 6,
        render: (data, type, row) =>
          `<strong class="badge ${roleBadgeClass(data)}">${escapeHtml(data)}</strong>${
            Number(row.is_admin)
              ? '<span class="badge bg-label-primary ms-1"><i class="ti ti-shield-check me-1"></i>Admin</span>'
              : ""
          }`,
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
          columns: [1, 2, 3, 4, 5, 6],
        },
      },
      {
        extend: "pdfHtml5",
        title: "Liste des Utilisateurs - Stocki Devit",
        text: '<i class="ti ti-file-type-pdf me-sm-1"></i> <span class="d-none d-sm-inline-block">Exporter en PDF</span>',
        className: "btn btn-default",
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6],
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
          header: (row) =>
            escapeHtml(row.data()["nom"] + " " + row.data()["prenom"]),
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

  // les rôles déjà attribués complètent la liste (à chaque rechargement du tableau)
  table.on("xhr.dt", function (e, settings, json) {
    if (json && Array.isArray(json.data)) initRoleSelect(json.data.map((u) => u.role));
  });

  // ===============================
  // Événements de modification
  // ===============================
  $(".datatables-users tbody").on("click", ".edit-record", function () {
    editingRow = table.row($(this).closest("tr"));
    const data = editingRow.data();
    fv.resetForm(true);
    const offCanvasElement = document.querySelector("#add-new-record");
    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
    offCanvasElement.querySelector("#titre").innerHTML =
      "Modifier l'utilisateur";
    offCanvasElement.querySelector(".dt-userLName").value = data.nom;
    offCanvasElement.querySelector(".dt-userFName").value = data.prenom;
    setRoleValue(data.role);
    offCanvasElement.querySelector(".dt-tel").value = data.tel;
    offCanvasElement.querySelector(".dt-email").value = data.email;
    offCanvasElement.querySelector(".dt-mDp").value = "";
    offCanvasElement.querySelector(".dt-mDp").placeholder =
      "Laisser vide pour ne pas modifier";
    offCanvasElement.querySelector(".dt-isAdmin").checked = Number(data.is_admin) === 1;
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
      is_admin: $("#isAdmin").is(":checked"),
    };
    let lnk = editingRow ? "api/user/update" : "api/user/create";
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
  let userToDelete;
  $(".datatables-users tbody").on("click", ".delete-record", function () {
    userToDelete = table.row($(this).closest("tr")).data().iduser;
    $("#confirmDeleteModal .modal-title").text("Confirmer la suppression");
    $("#confirmDeleteModal .modal-body").text(
      "Êtes-vous sûr de vouloir supprimer cet utilisateur ?",
    );
    deleteModal.show();
  });

  $("#confirmDeleteBtn").on("click", function () {
    if (userToDelete) {
      $.ajax({
        url: "api/user/delete",
        type: "DELETE",
        contentType: "application/json",
        data: JSON.stringify({ userToDelete }),
        dataType: "json",
        success: function (res) {
          if (res.success) {
            table.ajax.reload(null, false);
            showTopNotification(res.message, "success", "check");
            userToDelete = null;
            deleteModal.hide();
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
});
