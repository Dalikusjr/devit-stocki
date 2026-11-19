<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Helpers;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\HistoricRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;

class ProjectController
{
    private ProjectService $projectService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->projectService = new ProjectService($db, new ProjectRepository($db), new ClientRepository($db), new PaymentRepository($db), new HistoricRepository($db));
    }


    public function getProjects()
    {
        try {
            Validator::validateMethod('GET');

            $projects = $this->projectService->getProjects();
            // les accès (identifiants, mots de passe) ne sont envoyés qu'aux administrateurs
            if (!Helpers::isAdmin()) {
                foreach ($projects as &$project) {
                    $project['acces'] = null;
                }
                unset($project);
            }
            Response::success($projects, 'Projets récupérés avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les projets.');
        }
    }
    public function createProject()
    {
        try {
            Validator::validateMethod('POST');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $titre = trim($data['titre'] ?? '');  // Required
            $clnom = trim($data['clnom'] ?? '');  // Required
            $email = trim($data['email'] ?? '');  // Required
            $adresse = trim($data['adresse'] ?? '');  // Required
            $cltel = trim($data['tel'] ?? '');  // Required
            $datedebut = trim($data['datedebut'] ?? '');  // Required
            $daterenouv = trim($data['daterenouv'] ?? '');  // Required
            $couttot = trim($data['couttot'] ?? '');  // Required
            $avancepaye = trim($data['avancepaye'] ?? '');
            $comment = trim($data['comment'] ?? '');
            $acces = Helpers::isAdmin() ? trim($data['acces'] ?? '') : '';
            $nbrEcheance = trim($data['nbrEch'] ?? ''); //Required
            Validator::validateRequiredFields([
                'Titre' => $titre,
                'Nom du client' => $clnom,
                'Email' => $email,
                'Adresse' => $adresse,
                'Téléphone' => $cltel,
                'Date de début' => $datedebut,
                'Date de renouvellement' => $daterenouv,
                'Coût total' => $couttot,
                'Nombre d\'echeances' => $nbrEcheance
            ]);
            Validator::validateEmail($email);
            Validator::validateMaxLength($titre, 40, 'Titre');
            Validator::validateDate($datedebut, 'Date de début');
            Validator::validateDate($daterenouv, 'Date de renouvellement');
            Validator::validatePositiveNumbers([
                'Coût total' => $couttot
            ]);
            if ($avancepaye !== '') {
                Validator::validatePositiveNumbers([
                    'Avance payée' => $avancepaye
                ]);
            }
            Validator::validatePositiveNumbers(["Nombre d'échéances" => $nbrEcheance]);
            Validator::validateValidId($nbrEcheance);
            $this->projectService->createProject(
                $email,
                $clnom,
                $cltel,
                $adresse,
                $titre,
                $datedebut,
                $daterenouv,
                (float)$couttot,
                (float)$avancepaye,
                $acces,
                $comment,
                (int)$nbrEcheance
            );
            Response::success(null, 'Projet ajouté avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter le projet");
        }
    }

    public function getClientForAutoComplete()
    {
        try {
            Validator::validateMethod('POST');

            $email = trim($_POST['emailtmp'] ?? '');
            Validator::validateRequiredFields([
                'Email' => $email
            ]);
            Validator::validateEmail($email);
            $client = $this->projectService->getClientForAutoComplete($email);
            Response::success($client);
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les informations du client');
        }
    }

    public function getInstallments()
    {
        try {
            Validator::validateMethod('POST');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $idproj = trim($data['projectId'] ?? '');
            Validator::validateRequiredFields(['ID de projet' => $idproj]);
            Validator::validateValidId($idproj);
            $installments = $this->projectService->getInstallments($idproj);
            Response::success($installments, 'Echéances récupérés avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les échéances');
        }
    }

    public function updateProject()
    {
        try {
            Validator::validateMethod('PATCH');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $idproj = trim($data['idproj'] ?? '');
            $titre = trim($data['titre'] ?? '');
            $datedebut = trim($data['datedebut'] ?? '');
            $daterenouv = trim($data['daterenouv'] ?? '');
            $couttot = trim($data['couttot'] ?? '');
            $acces = Helpers::isAdmin() ? trim($data['acces'] ?? '') : null;
            $nbrEcheance = trim($data['nbrEch'] ?? '');
            Validator::validateRequiredFields([
                'ID de projet' => $idproj,
                'Titre' => $titre,
                'Date de début' => $datedebut,
                'Date de renouvellement' => $daterenouv,
                'Coût total' => $couttot,
                'Nombre d\'echeances' => $nbrEcheance
            ]);
            Validator::validateMaxLength($titre, 40, 'Titre');
            Validator::validateDate($datedebut, 'Date de début');
            Validator::validateDate($daterenouv, 'Date de renouvellement');
            Validator::validatePositiveNumbers([
                'Coût total' => $couttot,
                "Nombre d'échéances" => $nbrEcheance
            ]);
            Validator::validateValidId($idproj);
            Validator::validateValidId($nbrEcheance);

            $this->projectService->updateProject((int)$idproj, $titre, $datedebut, $daterenouv, (float)$couttot, $acces, (int)$nbrEcheance);
            Response::success(null, 'Projet modifié avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de modifier le projet.');
        }
    }

    public function deleteProject()
    {
        try {
            Validator::validateMethod('DELETE');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $idproj = trim($data['projToDelete'] ?? '');
            Validator::validateRequiredFields(['ID de projet' => $idproj]);
            Validator::validateValidId($idproj);

            $this->projectService->deleteProject((int)$idproj);
            Response::success(null, 'Projet supprimé avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de supprimer le projet');
        }
    }

    public function archiveProject()
    {
        try {
            Validator::validateMethod('PATCH');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $idproj = trim($data['projToArchive'] ?? '');
            Validator::validateRequiredFields(['ID de projet' => $idproj]);
            Validator::validateValidId($idproj);

            $this->projectService->archiveProject((int)$idproj);
            Response::success(null, 'Projet archivé avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible d\'archiver le projet');
        }
    }
}
