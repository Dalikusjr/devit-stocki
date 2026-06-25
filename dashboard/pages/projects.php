              <div class="card">
                <div class="card-datatable table-responsive pt-0">
                  <table class="datatables-projects table-striped table">
                    <thead>
                      <tr>
                        <th></th>
                        <th>id</th>
                        <th>Titre de projet</th>
                        <th>Client</th>
                        <th>Date de début</th>
                        <th>Date de renouvellement</th>
                        <th>Cout Total</th>
                        <th>Total Payée</th>
                        <th>Reste à payer</th>
                        <th>Accés</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>


              <!-- =======================
                OFFCANVAS - NOUVEAU PROJET
            ======================= -->
              <div class="offcanvas offcanvas-end" id="add-new-record">
                <div class="offcanvas-header border-bottom">
                  <h5 class="offcanvas-title" id="titre">Nouveau Projet</h5>
                  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body flex-grow-1">
                  <form class="add-new-record pt-0 row g-3" id="form-add-new-record" onsubmit="return false">

                    <div class="col-sm-12 div-projectTitle">
                      <label class="form-label" for="projectTitle">Titre de projet</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-prompt"></i></span>
                        <input type="text" id="projectTitle" class="form-control dt-projectTitle" name="projectTitle" placeholder="App Angular" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-clientLName">
                      <label class="form-label" for="clientLName">Nom de client</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" id="clientLName" class="form-control dt-clientLName" name="clientLName" placeholder="John Doe" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-tel">
                      <label class="form-label" for="tel">Téléphone</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-phone"></i></span>
                        <input type="text" id="tel" class="form-control dt-tel" name="tel" placeholder="+216 00 000 000" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-hide">
                      <label class="form-label" for="email">E-mail</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-at"></i></span>
                        <input type="email" id="email" class="form-control dt-email" name="email" placeholder="exemple@mail.com" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-hide">
                      <span class="text-decoration-underline text-muted"><i class="ti ti-info-square-rounded-filled me-2"></i>Remarque *:</span><br>
                      <span class="text-muted">Saisissez l'email pour charger automatiquement un client existant.</span>
                    </div>

                    <div class="col-sm-12 div-hide">
                      <label class="form-label" for="addr">Adresse</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
                        <input type="text" id="addr" class="form-control dt-addr" name="addr" placeholder="Adresse" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-startDate">
                      <label class="form-label" for="startDate">Date de début</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar-check"></i></span>
                        <input type="date" id="startDate" name="startDate" class="form-control dt-startDate" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-6 div-renewDate">
                      <label class="form-label" for="renewDate">Date de Renouvellement</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar-bolt"></i></span>
                        <input type="date" id="renewDate" name="renewDate" class="form-control dt-renewDate" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-4 div-totalCost">
                      <label class="form-label" for="totalCost">Montant Total</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-report-money"></i></span>
                        <input type="text" class="form-control dt-totalCost" id="totalCost" name="totalCost" placeholder="TND" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-4 div-hide">
                      <label class="form-label" for="advancePaid">Avance payée</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-currency-dollar"></i></span>
                        <input type="text" id="advancePaid" name="advancePaid" class="form-control dt-advancePaid" placeholder="TND" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-4 div-hide">
                      <label class="form-label" for="comment">Commentaire</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-bubble-text"></i></span>
                        <input type="text" id="comment" name="comment" class="form-control dt-comment" placeholder="Commentaire" />
                      </div>
                    </div>

                    <div class="col-sm-12 col-md-12"> <label class="form-label" for="acces">Accès</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-key"></i></span> <textarea
                          id="acces"
                          name="acces"
                          class="form-control dt-acces"
                          rows="3"
                          placeholder="URL, Identifiants, Mots de passe..."></textarea>
                      </div>
                      <small class="text-muted">Conservez ici les accès serveurs ou interfaces du projet.</small>
                    </div>

                    <div class="col-sm-12 mt-4">
                      <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Valider</button>
                      <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Annuler</button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- / OFFCANVAS -->