// ==========================
// Déclarations et initialisations
// ==========================
let projets;
let projectsStore = {};
let tranchesStore = {};
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
        projectsStore[projet.idproj] = projet;
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
            tranchesStore[tranche.idpay] = tranche;
            ////////////////////////////////////////////////////////////////ici la finction renderTranche
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
// ==========================
// Ajout du CSS pour styliser les composants
// ==========================
$("head").append(`
  <style>
    .loading-spinner, .no-projects, .error-message {
      text-align: center;
      padding: 30px;
      font-size: 18px;
      color: #6c757d;
    }
    
    .loading-spinner i {
      margin-right: 10px;
      color: #4361ee;
    }
    
    .error-message i {
      color: #e74c3c;
      margin-right: 10px;
    }
    
    .no-projects i {
      color: #95a5a6;
      margin-right: 10px;
    }
    
    .project-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 25px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }
    
    .project-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .project-header {
      display: flex;
      align-items: center;
      padding: 20px;
      background: linear-gradient(to right, rgba(255, 255, 255, 0.95) 0%,rgba(255, 255, 255, 0.7) 50%, rgba(255, 255, 255, 0.4) 100%), 
              url("../assets/img/pages/profile-banner.png");
      background-size: cover;
      background-position: center;
      border-bottom: 1px solid #e3e6f0;
    }
    
    .project-footer {
      display: flex;
      align-items: center;
      padding: 20px;
      background: linear-gradient(to left, rgba(255, 255, 255, 0.95) 0%,rgba(255, 255, 255, 0.7) 50%, rgba(255, 255, 255, 0.4) 100%), 
              url("../assets/img/front-pages/backgrounds/footer-bg-light.png");
      background-size: cover;
      background-position: center;
      border-bottom: 1px solid #e3e6f0;
    }

    .project-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: #4361ee;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      flex-shrink: 0;
    }
    
    .project-icon i {
      font-size: 20px;
      color: white;
    }
    
    .project-info {
      flex-grow: 1;
    }
    
    .project-title {
      font-weight: 700;
      color: #2d3748;
      margin: 0 0 5px 0;
      font-size: 1.25rem;
    }
    
    .project-details {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .amount-badge {
      background: #4361ee;
      color: white;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }
    
    .status-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }
    
    .status-terminé {
      background: #4cc9f0;
      color: white;
    }
    
    .status-en-cours {
      background: #f72585;
      color: white;
    }
    
    .status-en-attente {
      padding:0px 5px 0px 5px;
      background: #ffb800;
      color: black;
    }
    
    .progress-container {
      padding: 15px 20px;
      background: #f8f9fa;
    }
    
    .progress-labels {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 0.85rem;
      color: #6c757d;
    }
    
    .progress {
      height: 8px;
      background-color: #e9ecef;
      border-radius: 4px;
      overflow: hidden;
    }
    
.progress-bar {
  height: 100%;
  background: linear-gradient(to right, #4361ee, #3a0ca3);
  background-size: 1000px; /* Le dégradé ne s'étire pas, il est "découvert" par la barre */
  transition: width 0.5s ease;
  border-radius: 4px; /* Un petit arrondi rend l'animation plus douce */
}
    
    .tranches-container {
      padding: 20px;
    }
    
    .tranches-title {
      font-size: 1.1rem;
      color: #2d3748;
      margin: 0 0 15px 0;
      display: flex;
      align-items: center;
    }
    
    .tranches-title i {
      margin-right: 8px;
      color: #4361ee;
    }
    
    .tranches-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .tranche-item {
      padding: 12px 0;
      border-bottom: 1px solid #f1f3f9;
    }
    
    .tranche-item:last-child {
      border-bottom: none;
    }
    
    .tranche-content {
      display: flex;
      align-items: center;
    }
    
    .tranche-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      flex-shrink: 0;
    }
    
    .tranche-icon.paid {
      background: rgba(76, 201, 240, 0.15);
      color: #4cc9f0;
    }
    
    .tranche-icon.unpaid {
      background: rgba(247, 37, 133, 0.15);
      color: #f72585;
    }
    
    .tranche-info {
      flex-grow: 1;
    }
    
    .tranche-name {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 4px;
    }
    
    .tranche-details {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 0.9rem;
    }
    
    .tranche-amount {
      color: #4361ee;
      font-weight: 600;
    }

    
    .tranche-date {
      color: #e74c3c;
      display: flex;
      align-items: center;
    }
  
    
    .pay-date {
      color: #198754;
      display: flex;
      align-items: center;
    }
    
    .tranche-status {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    
    .tranche-status.paid {
      background: rgba(76, 201, 240, 0.15);
      color: #4cc9f0;
    }
    
    .tranche-status.pending {
      background: rgba(255, 184, 0, 0.15);
      color: #ffb800;
    }
    
    .tranche-status.unpaid {
      background: rgba(247, 37, 133, 0.15);
      color: #f72585;
    }
    
    @media (max-width: 768px) {
      .project-header {
        flex-direction: column;
        text-align: center;
      }
      
      .project-icon {
        margin-right: 0;
        margin-bottom: 10px;
      }
      
      .project-details {
        justify-content: center;
      }
      
      .tranche-content {
        flex-wrap: wrap;
      }
      
      .tranche-status {
        margin-top: 10px;
        width: 100%;
        text-align: center;
      }
    }
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
            .bg-label-success{
            background-color: #28a745 !important;
            color: #fff !important;
            }
  </style>
`);
