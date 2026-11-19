<?php
$me = \App\Helpers\Helpers::currentUser();
$initiales = htmlspecialchars(\App\Helpers\Helpers::initials($me));
?>
<div class="row g-4">
  <!-- Carte d'identité -->
  <div class="col-xl-4 col-lg-5">
    <div class="card h-100">
      <div class="card-body text-center pt-5">
        <div class="avatar avatar-xl mx-auto mb-3" style="width:96px;height:96px">
          <span class="avatar-initial rounded-circle bg-label-primary fs-2 fw-semibold" id="profilInitiales"><?= $initiales ?></span>
        </div>
        <h4 class="mb-1" id="profilNom"><?= htmlspecialchars(trim(($me['prenom'] ?? '') . ' ' . ($me['nom'] ?? ''))) ?></h4>
        <div class="text-muted mb-2"><?= htmlspecialchars($me['role'] ?? '') ?></div>
        <?php if (!empty($me['is_admin'])): ?>
          <span class="badge bg-label-primary"><i class="ti ti-shield-check me-1"></i>Administrateur</span>
        <?php else: ?>
          <span class="badge bg-label-secondary"><i class="ti ti-user me-1"></i>Utilisateur</span>
        <?php endif ?>
        <hr class="my-4">
        <ul class="list-unstyled text-start mb-0">
          <li class="mb-2"><i class="ti ti-at me-2 text-muted"></i><span id="profilEmail"><?= htmlspecialchars($me['email'] ?? '') ?></span></li>
          <li class="mb-2"><i class="ti ti-phone me-2 text-muted"></i><span id="profilTel"><?= htmlspecialchars($me['tel'] ?? '') ?></span></li>
          <li><i class="ti ti-briefcase me-2 text-muted"></i><?= htmlspecialchars($me['role'] ?? '') ?></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Modification -->
  <div class="col-xl-8 col-lg-7">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Mon profil</h5>
        <small class="text-muted">Vos informations personnelles. Le rôle et les droits sont gérés par un administrateur.</small>
      </div>
      <div class="card-body">
        <form id="profilForm" class="row g-3" onsubmit="return false">
          <div class="col-md-6">
            <label class="form-label" for="prenom">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" maxlength="20" value="<?= htmlspecialchars($me['prenom'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="nom">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" maxlength="20" value="<?= htmlspecialchars($me['nom'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">E-mail (identifiant de connexion)</label>
            <input type="email" class="form-control" id="email" name="email" maxlength="40" value="<?= htmlspecialchars($me['email'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="tel">Téléphone</label>
            <input type="text" class="form-control" id="tel" name="tel" maxlength="20" value="<?= htmlspecialchars($me['tel'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="role">Rôle</label>
            <input type="text" class="form-control" id="role" value="<?= htmlspecialchars($me['role'] ?? '') ?>" disabled>
          </div>
          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary me-2">Enregistrer</button>
            <button type="reset" class="btn btn-outline-secondary">Annuler</button>
            <a href="p-parametres" class="btn btn-text-secondary float-sm-end"><i class="ti ti-lock me-1"></i>Changer le mot de passe</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
