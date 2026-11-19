<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table class="datatables-users table-striped table">
      <thead>
        <tr>
          <th></th>
          <th>id</th>
          <th>Email</th>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Téléphone</th>
          <th>Role</th>
          <th></th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- / CONTENT -->

<!-- =======================
OFFCANVAS - NOUVEAU PROJET
======================= -->
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="titre"></h5>
    <button
      type="button"
      class="btn-close text-reset"
      data-bs-dismiss="offcanvas"
      aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">

      <div class="col-sm-12">
        <label class="form-label" for="userLName">Nom</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-user"></i></span>
          <input type="text" id="userLName" class="form-control dt-userLName"
            name="userLName" placeholder="Nom" maxlength="20" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="userFName">Prénom</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-user"></i></span>
          <input type="text" id="userFName" class="form-control dt-userFName"
            name="userFName" placeholder="Prénom" maxlength="20" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="role">Rôle</label>
        <select id="role" class="form-select dt-role" name="role"></select>
        <small class="text-muted">Choisissez dans la liste ou tapez un nouveau rôle (40 caractères max).</small>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="email">Email</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-at"></i></span>
          <input type="email" id="email" class="form-control dt-email"
            name="email" placeholder="exemple@mail.com" maxlength="40" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="mDp">Mot de passe</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-password"></i></span>
          <input type="password" id="mDp" class="form-control dt-mDp"
            name="mDp" placeholder="*********" autocomplete="new-password" />
        </div>
      </div>

      <div class="col-sm-12">
        <label class="form-label" for="tel">Téléphone</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-phone"></i></span>
          <input type="text" id="tel" class="form-control dt-tel"
            name="tel" placeholder="+216 XX XXX XXX" maxlength="20" />
        </div>
      </div>

      <div class="col-sm-12">
        <div class="form-check mt-2">
          <input class="form-check-input dt-isAdmin" type="checkbox" id="isAdmin" name="isAdmin">
          <label class="form-check-label" for="isAdmin">Administrateur</label>
        </div>
        <small class="text-muted">Gère les utilisateurs et voit les accès des projets.</small>
      </div>

      <div class="col-sm-12">
        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
        <button type="reset" id="resetBtn" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
      </div>

    </form>
  </div>
</div>
<!-- / OFFCANVAS -->