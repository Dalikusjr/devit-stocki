"use strict";

// ===============================
// Mon profil : modification de ses propres informations
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("profilForm");
  const initials = (prenom, nom) =>
    ((prenom.trim()[0] || "") + (nom.trim()[0] || "")).toUpperCase() || "?";

  const fv = FormValidation.formValidation(form, {
    fields: {
      prenom: { validators: { notEmpty: { message: "Le prénom est requis" } } },
      nom: { validators: { notEmpty: { message: "Le nom est requis" } } },
      email: {
        validators: {
          notEmpty: { message: "L'e-mail est requis" },
          emailAddress: { message: "L'adresse e-mail n'est pas valide" },
        },
      },
      tel: { validators: { notEmpty: { message: "Le téléphone est requis" } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: "", rowSelector: ".col-md-6" }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
    },
  });

  fv.on("core.form.valid", function () {
    const data = {
      prenom: $("#prenom").val(),
      nom: $("#nom").val(),
      email: $("#email").val(),
      tel: $("#tel").val(),
    };
    $.ajax({
      url: "api/profile/update",
      type: "PATCH",
      contentType: "application/json",
      data: JSON.stringify(data),
      dataType: "json",
      success: function (res) {
        if (!res.success) {
          showTopNotification(res.message, "danger", "ban");
          return;
        }
        showTopNotification(res.message, "success", "check");
        // mise à jour immédiate de la carte et du header
        const ini = initials(data.prenom, data.nom);
        const full = `${data.prenom} ${data.nom}`.trim();
        $("#profilInitiales, .dropdown-user .avatar-initial").text(ini);
        $("#profilNom, .dropdown-user .fw-medium").text(full);
        $("#profilEmail").text(data.email);
        $("#profilTel").text(data.tel);
        form.querySelectorAll("input").forEach((i) => (i.defaultValue = i.value));
      },
      error: () => showTopNotification("Erreur lors de Traitement", "danger", "ban"),
    });
  });
});
