// ==========================
// Déclarations et initialisations
// ==========================
$(document).ready(function () {
  var listePro = $("#listeProjets");

  // ==========================
  // Ajout d'un indicateur de chargement
  // ==========================
  listePro.html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Chargement des projets...</div>');

  // ==========================
  // Récupération des projets via JSON
  // ==========================
  $.getJSON("../assets/json/listeProjets.json")
    .done(function (data) {
      listePro.empty(); // Vider l'indicateur de chargement

      var projets = data.data;

      // ==========================
      // Gestion du cas : aucun projet
      // ==========================
      if (projets.length === 0) {
        listePro.html('<div class="no-projects"><i class="fas fa-folder-open"></i> Aucun projet à afficher</div>');
        return;
      }

      // ==========================
      // Génération des cartes projets
      // ==========================
      projets.forEach(function (projet) {
        // Card principale
        var div = $("<div></div>").addClass("project-card");

        // --------------------------
        // En-tête du projet
        // --------------------------
        var header = $("<div></div>").addClass("project-header");

        // Icône selon le statut
        var statusIcon = "fa-tasks";
        if (projet.statut === "Terminé") statusIcon = "fa-check-circle";
        else if (projet.statut === "En cours") statusIcon = "fa-spinner";

        header.html(`
          <div class="project-icon">
            <i class="fas ${statusIcon}"></i>
          </div>
          <div class="project-info">
            <h3 class="project-title">${projet.projet}</h3>
            <div class="project-details">
              <span class="amount-badge">${projet.montant_total}</span>
              <span class="status-badge status-${projet.statut.toLowerCase().replace(' ', '-')}">
                ${projet.statut}
              </span>
            </div>
          </div>
        `);

        div.append(header);

        // --------------------------
        // Barre de progression
        // --------------------------
        var paidCount = projet.tranches.filter(t => t.statut === "Payé").length;
        var progressPercent = projet.tranches.length > 0 ? (paidCount / projet.tranches.length) * 100 : 0;

        var progressBar = $(`
          <div class="progress-container">
            <div class="progress-labels">
              <span>Tranches payées: ${paidCount}/${projet.tranches.length}</span>
              <span>${Math.round(progressPercent)}%</span>
            </div>
            <div class="progress">
              <div class="progress-bar" role="progressbar" style="width: ${progressPercent}%"></div>
            </div>
          </div>
        `);

        div.append(progressBar);

        // --------------------------
        // Liste des tranches
        // --------------------------
        var tranchesContainer = $("<div></div>").addClass("tranches-container");
        var tranchesTitle = $("<h4></h4>").addClass("tranches-title").html("<i class='fas fa-list-ol'></i> Tranches de paiement");
        tranchesContainer.append(tranchesTitle);

        var ul = $("<ul></ul>").addClass("tranches-list");

        projet.tranches.forEach(function (tranche) {
          var li = $("<li></li>").addClass("tranche-item");

          // Déterminer la classe CSS en fonction du statut
          var statusClass = "";
          var icon = "";
          if (tranche.statut === "Payé") {
            statusClass = "paid";
            icon = "fa-check-circle";
          } else if (tranche.statut === "En attente") {
            statusClass = "pending";
            icon = "fa-clock";
          } else {
            statusClass = "unpaid";
            icon = "fa-times-circle";
          }

          li.html(`
            <div class="tranche-content">
              <div class="tranche-icon ${statusClass}">
                <i class="fas ${icon}"></i>
              </div>
              <div class="tranche-info">
                <div class="tranche-name">${tranche.nom}</div>
                <div class="tranche-details">
                  <span class="tranche-amount">${tranche.montant}</span>
                  ${tranche.date ? 
                    `<span class="tranche-date"><i class="far fa-calendar-alt"></i> ${tranche.date}</span>` : 
                    '<span class="no-date">Non payé</span>'
                  }
                </div>
              </div>
              <div class="tranche-status ${statusClass}">${tranche.statut}</div>
            </div>
          `);

          ul.append(li);
        });

        tranchesContainer.append(ul);
        div.append(tranchesContainer);

        // --------------------------
        // Ajouter la carte au conteneur
        // --------------------------
        listePro.append(div);
      });
    })
    .fail(function (error) {
      console.error("Erreur lors de la récupération des projets :", error);
      listePro.html('<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des projets</div>');
    });
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
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
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
      background: #ffb800;
      color: white;
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
      transition: width 0.5s ease;
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
    
    .tranche-icon.pending {
      background: rgba(255, 184, 0, 0.15);
      color: #ffb800;
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
      color: #6c757d;
      display: flex;
      align-items: center;
    }
    
    .tranche-date i {
      margin-right: 4px;
    }
    
    .no-date {
      color: #e74c3c;
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
  </style>
`);