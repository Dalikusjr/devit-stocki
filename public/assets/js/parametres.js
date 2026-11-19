"use strict";

// ===============================
// Configuration · Sécurité : changement du mot de passe
// (mêmes règles que le serveur : 8 caractères, une minuscule, un chiffre/symbole/espace)
// ===============================
const PW_RULES = {
  length: (v) => v.length >= 8,
  lower: (v) => /\p{Ll}/u.test(v),
  other: (v) => /[^\p{L}]/u.test(v),
};

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("passwordForm");

  // Afficher / masquer un mot de passe
  form.querySelectorAll(".toggle-password").forEach((t) =>
    t.addEventListener("click", function () {
      const input = this.parentElement.querySelector("input");
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      this.querySelector("i").className = show ? "ti ti-eye" : "ti ti-eye-off";
    }),
  );

  // Exigences cochées au fil de la saisie (icône + texte, pas la couleur seule)
  const rulesList = document.getElementById("pwRules");
  function refreshRules() {
    const v = document.getElementById("newPassword").value;
    rulesList.querySelectorAll("li").forEach((li) => {
      const okRule = PW_RULES[li.dataset.rule](v);
      li.classList.toggle("text-success", okRule);
      li.style.listStyleType = okRule ? "'✓  '" : "";
    });
  }
  document.getElementById("newPassword").addEventListener("input", refreshRules);

  const fv = FormValidation.formValidation(form, {
    fields: {
      currentPassword: { validators: { notEmpty: { message: "Le mot de passe actuel est requis" } } },
      newPassword: {
        validators: {
          notEmpty: { message: "Le nouveau mot de passe est requis" },
          callback: {
            message: "Le mot de passe ne respecte pas les exigences ci-dessous",
            callback: (input) => input.value === "" || Object.values(PW_RULES).every((r) => r(input.value)),
          },
        },
      },
      confirmPassword: {
        validators: {
          notEmpty: { message: "Veuillez confirmer le nouveau mot de passe" },
          identical: {
            compare: () => form.querySelector('[name="newPassword"]').value,
            message: "La confirmation ne correspond pas",
          },
        },
      },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: "", rowSelector: ".col-md-6" }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
    },
    init: (instance) => {
      instance.on("plugins.message.placed", function (e) {
        if (e.element.parentElement.classList.contains("input-group")) {
          e.element.parentElement.insertAdjacentElement("afterend", e.messageElement);
        }
      });
    },
  });

  form.addEventListener("reset", () => setTimeout(() => { fv.resetForm(true); refreshRules(); }));

  fv.on("core.form.valid", function () {
    $.ajax({
      url: "api/profile/password",
      type: "PATCH",
      contentType: "application/json",
      data: JSON.stringify({
        currentPassword: $("#currentPassword").val(),
        newPassword: $("#newPassword").val(),
        confirmPassword: $("#confirmPassword").val(),
      }),
      dataType: "json",
      success: function (res) {
        if (!res.success) {
          showTopNotification(res.message, "danger", "ban");
          return;
        }
        showTopNotification(res.message, "success", "check");
        form.reset();
      },
      error: () => showTopNotification("Erreur lors de Traitement", "danger", "ban"),
    });
  });
});
