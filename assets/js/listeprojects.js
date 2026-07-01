// ==========================
// Déclarations et initialisations
// ==========================
let projets;
let listePro = null;
let fv;
let editingTranche = null;
let idproject = null;
let dueToDelete = null;
const options = { day: "numeric", month: "short", year: "numeric" };
const offCanvas = $("#add-new-record");
let offCanvasEl = new bootstrap.Offcanvas(offCanvas);
const deleteModal = new bootstrap.Modal("#confirmDeleteModal");

$(document).ready(function () {
  const formNewRecord = document.querySelector("#form-add-new-record");
  fv = FormValidation.formValidation(formNewRecord, {
    fields: {
      commentaire: {
        validators: {
          notEmpty: { message: "Le commentaire est requis" },
        },
      },
      montant: {
        validators: {
          notEmpty: { message: "Le montant de tranche est requis" },
          numeric: { message: "Veuillez saisir un nombre valide" },
          callback: {
            message: "Le montant ne peut pas être inférieur à 0",
            callback: function (input) {
              const montant = parseFloat(input.value);
              if (input.value === "") return true;
              return montant > 0;
            },
          },
          enabled: true,
        },
      },
      dateech: {
        validators: {
          notEmpty: { message: "La date de échéance est requis" },
        },
      },
      datepay: {
        validators: {
          notEmpty: { message: "La date de paiement est requis" },
          enabled: true,
        },
      },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: "",
        rowSelector: ".mb-3",
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
  fv.on("core.form.valid", function () {
    const $card = $(`.project-card-${idproject}`);
    const $old = $(`.ti-edit[data-id="${editingTranche}"]`).closest("li");
    const $list = $card.find("ul");
    const formData = {
      idproject,
      editingTranche,
      comment: $(".dt-commentaire").val(),
      montant: $(".dt-montant").val(),
      status: $(".dt-status").is(":checked") ? "payer" : "inpayer",
      dateech: $(".dt-dateech").val(),
      datepay: $(".dt-datepay").val(),
    };
    let lnk = editingTranche ? "paiments/update.php" : "paiments/add.php";
    $.post(
      lnk,
      formData,
      (res) => {
        if (res.success) {
          const data = res.tranche;
          const $new = $(renderTranche(data)).hide();
          if (editingTranche && $old.length) {
            $old.fadeOut(400, function () {
              $(this).replaceWith($new);
              $new.fadeIn(400);
            });
          } else {
            const $empty = $(".vide");
            if ($empty.length) {
              $empty.fadeOut(400, function () {
                $(this).remove();
                $list.show().append($new);
                $new.fadeIn(400);
              });
            } else {
              $list.append($new);
              $new.fadeIn(400);
            }
          }
          updateProgressBar(idproject, data.tranches_paye, data.tranches_total);
          $card.find(".status-en-attente").fadeOut(200, function () {
            $(this)
              .text(`Reste ${parseFloat(data.reste).toFixed(2)} DT`)
              .fadeIn(200);
          });
          showTopNotification(res.message, "success", "check");
          offCanvasEl.hide();
          editingTranche = null;
          idproject = null;
        } else {
          showTopNotification(res.message, "danger", "ban");
        }
      },
      "json",
    );
  });
  const urlParams = new URLSearchParams(window.location.search);
  const idclient = urlParams.get("id");
  listePro = $("#listeProjets");

  // ==========================
  // Ajout d'un indicateur de chargement
  // ==========================
  listePro.html(
    '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Chargement des projets...</div>',
  );

  // ==========================
  // Récupération des projets via JSON
  // ==========================
  $.getJSON("paiments/listeprojects.php", { idclient })
    .done(function (data) {
      listePro.empty(); // Vider l'indicateur de chargement

      projets = data.data;

      // ==========================
      // Génération des cartes projets
      // ==========================
      projets.forEach(function (projet) {
        // Card principale
        let div = $("<div></div>")
          .addClass(`project-card project-card-${projet.idproj}`)
          .attr("data-id", projet.idproj);

        // --------------------------
        // En-tête du projet
        // --------------------------
        let header = $("<div></div>").addClass("project-header");

        header.html(`
          <div class="d-none d-md-flex justify-items-center project-icon">
            <i class="ti ti-subtask"></i>
          </div>
          <div class="project-info">
            <h3 class="project-title">${projet.titre}</h3>
            <div class="project-details">
              <span class="amount-badge">${parseFloat(projet.couttot).toFixed(2)} DT</span>
              <span class="status-en-attente">Reste ${parseFloat(projet.restepaye).toFixed(2)} DT</span>
            </div>
          </div>
          <div>
            <button type="button" class="btn btn-primary btn-add-tranche" title="Ajouter tranche">
              <i class="ti ti-plus"></i>
            </button>
          </div>
        `);

        div.append(header);
        let tranchesContainer = $("<div></div>").addClass("tranches-container");
        div.append(renderProgressBar(projet));
        let tranchesTitle = $("<h4></h4>")
          .addClass("tranches-title")
          .html("<i class='fas fa-list-ol'></i> Tranches de paiement");
        tranchesContainer.append(tranchesTitle);

        let ul = $("<ul></ul>").addClass("tranches-list");
        if (projet.tranches_total == 0) {
          tranchesContainer.append(ul.hide());
          tranchesTitle.hide();
          tranchesContainer.append(
            '<div class="vide text-center p-4 text-muted"><i class="ti ti-receipt-off fs-1"></i><p class="mt-2">Aucune tranche de paiement pour ce projet.</p></div>',
          );
        } else {
          //tranchesContainer.empty();
          // --------------------------
          // Barre de progression
          // --------------------------

          // --------------------------
          // Liste des tranches
          // --------------------------

          projet.tranches.forEach(function (tranche) {
            ul.append(renderTranche(tranche));
          });

          tranchesContainer.append(ul);
        }
        div.append(tranchesContainer);

        // --------------------------
        // Ajouter la carte au conteneur
        // --------------------------
        listePro.append(div);
      });
    })
    .fail(function (error) {
      console.error("Erreur lors de la récupération des projets :", error);
      listePro.html(
        '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des projets</div>',
      );
    });
});

$(document).on("click", ".btn-add-tranche", function () {
  idproject = $(this).closest(".project-card").data("id");
  editingTranche = null;
  offCanvas.find("#titre").text("Nouveau Tranche");
  $(".dt-montant").val("");
  $(".dt-commentaire").val("");
  $(".dt-dateech").val("");
  $(".dt-datepay").prop("disabled", true).val("");
  offCanvas.find(".check_input").prop("checked", false);
  fv.disableValidator("datepay", "notEmpty");
  updateStatus(offCanvas.find(".check_input"));
  offCanvasEl.show();
});
$(document).on("click", ".ti-edit", function () {
  offCanvas.find("#titre").text("Modifié Tranche");
  editingTranche = $(this).data("id");
  idproject = $(this).closest(".project-card").data("id");
  $.post(
    "paiments/getTranche.php",
    { editingTranche },
    (res) => {
      if (res.success) {
        const data = res.data;
        const isChecked = data.status === "payer";
        offCanvas.find(".check_input").prop("checked", isChecked);
        if (isChecked) {
          fv.enableValidator("datepay", "notEmpty");
          $(".dt-datepay").prop("disabled", false).val(data.datepay);
        } else {
          fv.disableValidator("datepay", "notEmpty");
          $(".dt-datepay").prop("disabled", true).val("");
        }
        updateStatus(offCanvas.find(".check_input"));
        $(".dt-montant").val(data.echeance);
        $(".dt-commentaire").val(data.commentaire);
        $(".dt-dateech").val(data.dateech);
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    },
    "json",
  );
  offCanvasEl.show();
});

$(document).on("change", ".update-status", function () {
  updateStatus(this);
});
$(document).on("change", ".dt-status", function () {
  const isChecked = $(this).is(":checked");
  const input = $(".dt-datepay");
  updateStatus(this);
  if (isChecked) fv.enableValidator("datepay", "notEmpty");
  else fv.disableValidator("datepay", "notEmpty");
  input.prop("disabled", !isChecked ? true : false);
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

function renderProgressBar(projet) {
  const total = projet.tranches_total;
  const paidCount = projet.tranches_paye;
  const progressPercent = total > 0 ? Math.round((paidCount / total) * 100) : 0;

  return `
          <div class="progress-container">
            <div class="progress-labels">
              <span>Tranches payées: ${paidCount}/${total}</span>
              <span>${Math.round(progressPercent)}%</span>
            </div>
            <div class="progress">
              <div class="progress-bar" role="progressbar" style="width: ${progressPercent}%"></div>
            </div>
          </div>
        `;
}

function updateProgressBar(idproj, tranches_paye, tranches_total) {
  const $card = $(`.project-card-${idproj}`);

  const progressPercent =
    tranches_total > 0 ? Math.round((tranches_paye / tranches_total) * 100) : 0;

  // 1. Animation de la largeur avec jQuery
  $card.find(".progress-bar").css("width", progressPercent + "%");

  // 2. Mise à jour fluide des textes
  $card
    .find(".progress-labels span:first-child")
    .text(`Tranches payées: ${tranches_paye}/${tranches_total}`);
  $card.find(".progress-labels span:last-child").text(`${progressPercent}%`);
}

function renderTranche(tranche) {
  let li = $("<li></li>").addClass("tranche-item");

  const isPaid = tranche.status === "payer";
  const uniqueId = `tranche_${tranche.idpay}`;
  li.html(`
            <div class="tranche-content">
              <div class="d-none d-md-flex justify-items-center tranche-icon ${isPaid ? "paid" : "unpaid"}">
                <i class="ti ${isPaid ? "ti-rosette-discount-check" : "ti-clock"}"></i>
              </div>
              <div class="tranche-info">
                <div class="d-flex align-items-center mb-1">
            <div class="tranche-name fw-bold me-2">${tranche.commentaire}</div>
            <div class="tranche-actions d-flex gap-1">
                <i class="ti ti-edit text-primary cursor-pointer" title="Modifier" data-id="${tranche.idpay}"></i>
                <i class="ti ti-trash text-danger cursor-pointer" title="Supprimer" data-id="${tranche.idpay}"></i>
            </div>
            </div>
                <div class="tranche-details">
                  <span class="tranche-amount">${parseFloat(tranche.echeance).toFixed(2)} DT</span>
                  <span class="dateech ${isPaid ? "pay-date" : "tranche-date"}"><i class="ti ti-calendar-event me-2"></i>${new Intl.DateTimeFormat("fr-FR", options).format(new Date(tranche.dateech))}</span>
                </div>
              </div>
              <div class="d-none d-md-flex flex-column align-items-end">
                <div class="check_container ms-4 mb-2">
                  <label for="${uniqueId}" class="  badge ${
                    isPaid ? "bg-label-success" : "bg-label-danger"
                  } me-2">
                      ${isPaid ? "Payé" : "Non payé"}
                  </label>
                </div>
                ${
                  isPaid
                    ? `<span class="datepay pay-date"><i class="ti ti-calendar-event me-2"></i>${new Intl.DateTimeFormat("fr-FR", options).format(new Date(tranche.datepay))}</span>`
                    : ""
                }
              </div>
            </div>
           `);
  return li;
}
// ===============================
// Événements de suppression
// ===============================

$(document).on("click", ".ti-trash", function () {
  dueToDelete = $(this).data("id");
  idproject = $(this).closest(".project-card").data("id");
  $(".modal-body").text("Êtes-vous sûr de vouloir supprimer ce paiement ?");
  deleteModal.show();
});

$("#confirmDeleteBtn").on("click", function () {
  if (!dueToDelete) return;

  $.post(
    "paiments/delete.php",
    { dueToDelete, idproject },
    (res) => {
      if (res.success) {
        const $trash = $(`.ti-trash[data-id="${dueToDelete}"]`);
        const $card = $(`.project-card-${idproject}`);
        $card.find(".status-en-attente").fadeOut(200, function () {
          $(this)
            .text(`Reste ${parseFloat(res.reste).toFixed(2)} DT`)
            .fadeIn(200);
        });

        updateProgressBar(idproject, res.tranches_paye, res.tranches_total);

        $trash.closest(".tranche-item").fadeOut(300, function () {
          $(this).remove();
        });
        if (res.tranches_total == 0) {
          $card
            .find(".tranches-container")
            .empty()
            .html(
              '<div class="text-center p-4 text-muted"><i class="ti ti-receipt-off fs-1"></i><p class="mt-2">Aucune tranche de paiement pour ce projet.</p></div>',
            );
        }

        showTopNotification(res.message, "success", "check");
        dueToDelete = null;
        idproject = null;
        deleteModal.hide();
      } else {
        showTopNotification(res.message, "danger", "ban");
      }
    },
    "json",
  );
});
