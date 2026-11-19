<div class="row g-4">
  <div class="col-xl-8 col-lg-10">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Configuration · Sécurité</h5>
        <small class="text-muted">Changer le mot de passe de votre compte.</small>
      </div>
      <div class="card-body">
        <form id="passwordForm" class="row g-3" onsubmit="return false" autocomplete="off">
          <div class="col-md-6">
            <label class="form-label" for="currentPassword">Mot de passe actuel</label>
            <div class="input-group input-group-merge">
              <input type="password" class="form-control" id="currentPassword" name="currentPassword" autocomplete="current-password">
              <span class="input-group-text cursor-pointer toggle-password" role="button" aria-label="Afficher le mot de passe"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="w-100 m-0"></div>
          <div class="col-md-6">
            <label class="form-label" for="newPassword">Nouveau mot de passe</label>
            <div class="input-group input-group-merge">
              <input type="password" class="form-control" id="newPassword" name="newPassword" autocomplete="new-password">
              <span class="input-group-text cursor-pointer toggle-password" role="button" aria-label="Afficher le mot de passe"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="confirmPassword">Confirmer le nouveau mot de passe</label>
            <div class="input-group input-group-merge">
              <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" autocomplete="new-password">
              <span class="input-group-text cursor-pointer toggle-password" role="button" aria-label="Afficher le mot de passe"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="col-12">
            <h6 class="mb-2">Exigences du mot de passe :</h6>
            <ul class="ps-3 mb-0 small" id="pwRules">
              <li data-rule="length">Au minimum 8 caractères — plus il est long, mieux c'est</li>
              <li data-rule="lower">Au moins une lettre minuscule</li>
              <li data-rule="other">Au moins un chiffre, un symbole ou un espace</li>
            </ul>
          </div>
          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary me-2">Enregistrer</button>
            <button type="reset" class="btn btn-outline-secondary">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
