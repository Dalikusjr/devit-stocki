<?php

namespace App\Controllers;

use App\Config\Database;
use App\Enums\PaymentStatus;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\HistoricRepository;
use App\Repositories\MaintainRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\PaymentRepository;
use App\Services\MaintainService;

class MaintainController
{
    private MaintainService $maintainService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->maintainService = new MaintainService(
            $db,
            new MaintainRepository($db),
            new PaymentRepository($db),
            new HistoricRepository($db),
            new ProjectRepository($db)
        );
    }


    public function getMaintains()
    {
        try {
            Validator::validateMethod('GET');
            Response::success($this->maintainService->getMaintains(), "Abonnements récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les abonnements");
        }
    }

    public function getMaintainHistorics()
    {
        try {
            Validator::validateMethod('GET');
            $maintainId = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields([
                'Maintain Id' => $maintainId,
            ]);
            Validator::validateValidId($maintainId);
            Response::success($this->maintainService->getMaintainHistorics((int)$maintainId), "Historique récupéré avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les historiques");
        }
    }

    public function getProjectsForAutoComplete()
    {
        try {
            Validator::validateMethod('GET');

            $search = trim($_GET['term'] ?? '');

            $rows = $this->maintainService->getProjectsForAutoComplete();

            if (!empty($search)) {
                $rows = array_values(array_filter($rows, function ($item) use ($search) {
                    return stripos($item['titre'], $search) !== false;
                }));
            }

            Response::success(
                $rows,
                "Projets récupérés avec succès"
            );
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les projets");
        }
    }

    public function createMaintain()
    {

        try {
            Validator::validateMethod('POST');
            $idproj  = trim($_POST['idproj'] ?? '');
            $idclient = trim($_POST['idclient'] ?? '');
            $montant = trim($_POST['montant'] ?? '');
            $status  = trim($_POST['status'] ?? '');
            Validator::validateRequiredFields([
                'id de client' => $idclient,
                'montant d\'abonnement' => $montant,
                'Project Id' => $idproj,
                'status d\'abonnement' => $status
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateValidId($idproj);
            Validator::validateValidId($idclient);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            $this->maintainService->createMaintain((int)$idproj,  (int)$idclient,  (float)$montant,  $status);

            Response::success(null, "Abonnement ajouté avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter l'abonnement.");
        }
    }

    public function updateMaintain()
    {
        try {
            Validator::validateMethod('POST');
            $maintainId = trim($_POST['id'] ?? '');
            $idproj  = trim($_POST['idproj'] ?? '');
            $idclient = trim($_POST['idclient'] ?? '');
            $montant = trim($_POST['montant'] ?? '');
            $status  = trim($_POST['status'] ?? '');
            Validator::validateRequiredFields([
                'Maintain Id' => $maintainId,
                'Client Id' => $idclient,
                'montant d\'abonnement' => $montant,
                'Project Id' => $idproj,
                'status d\'abonnement' => $status
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateValidId($maintainId);
            Validator::validateValidId($idproj);
            Validator::validateValidId($idclient);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            $this->maintainService->updateMaintain((int)$maintainId, (int)$idproj, (int)$idclient, (float)$montant, $status);
            Response::success(null, "Abonnement modifié avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'abonnement");
        }
    }

    public function updateMaintainStatus()
    {
        try {
            Validator::validateMethod('POST');
            $maintainId = trim($_POST['id'] ?? '');
            $montant = trim($_POST['montant'] ?? '');
            $status  = trim($_POST['status'] ?? '');
            Validator::validateRequiredFields([
                'Maintain Id' => $maintainId,
                'montant d\'abonnement' => $montant,
                'status d\'abonnement' => $status
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateValidId($maintainId);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            $this->maintainService->updateMaintainStatus((int)$maintainId, (float)$montant, $status);
            Response::success(null, "Statut de l'abonnement mis à jour avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'abonnement");
        }
    }

    public function deleteMaintain()
    {
        try {
            Validator::validateMethod('POST');
            $maintainId = trim($_POST['abonnToDelete'] ?? '');
            Validator::validateRequiredFields([
                'Maintain Id' => $maintainId
            ]);
            Validator::validateValidId($maintainId);
            $this->maintainService->deleteMaintain((int)$maintainId);

            Response::success(null, "Abonnement supprimé avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer l'abonnement");
        }
    }
}
