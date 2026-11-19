<?php

namespace App\Controllers;

use App\Config\Database;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\HistoricRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StockiRepository;
use App\Services\StockiService;

class StockiController
{
    private StockiService $stockiService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->stockiService = new StockiService(
            $db,
            new StockiRepository($db),
            new HistoricRepository($db),
            new PaymentRepository($db),
            new ClientRepository($db)
        );
    }


    // Corps JSON (envoyé par la page) ou, à défaut, champs de formulaire
    private function input(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : $_POST;
    }

    public function getStockis()
    {
        try {
            Validator::validateMethod('GET');

            Response::success($this->stockiService->getStockis(), "Abonnements récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les abonnements");
        }
    }

    public function getStockiHistorics()
    {
        try {
            Validator::validateMethod('GET');
            $stockiId = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields([
                'id de stocki' => $stockiId
            ]);
            Validator::validateValidId($stockiId);
            Response::success($this->stockiService->getStockiHistorics((int)$stockiId), "Historique récupéré avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les historiques");
        }
    }

    public function getClientsForAutoComplete()
    {
        try {
            Validator::validateMethod('GET');
            $search = trim($_GET['term'] ?? '');
            $rows = $this->stockiService->getClientsForAutoComplete();
            if (!empty($search)) {
                $rows = array_values(array_filter($rows, function ($item) use ($search) {
                    return stripos($item['nom'], $search) !== false;
                }));
            }
            Response::success($rows, "Clients récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les clients");
        }
    }


    public function createStocki()
    {

        try {
            Validator::validateMethod('POST');
            $data = $this->input();
            $paymentType  = trim($data['paymentType'] ?? '');
            $idclient = trim($data['idclient'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $status  = trim($data['status'] ?? '');
            $startDate = trim($data['startDate'] ?? '') ?: date('Y-m-d');
            Validator::validateRequiredFields([
                'Client' => $idclient,
                'Montant' => $montant,
                "Type d'abonnement" => $paymentType,
                'Statut' => $status
            ]);
            Validator::validateValidId($idclient);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            Validator::validateValueInEnum($paymentType, PaymentType::getValues());
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateDate($startDate, 'Date de début');
            $this->stockiService->createStocki((int)$idclient, $paymentType,  (float)$montant,  $status, $startDate);

            Response::success(null, "Abonnement ajouté avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter l'abonnement.");
        }
    }

    public function updateStocki()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $stockiId = trim($data['id'] ?? '');
            $paymentType = trim($data['paymentType'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $status  = trim($data['status'] ?? '');
            $startDate = trim($data['startDate'] ?? '');
            Validator::validateRequiredFields([
                'ID abonnement' => $stockiId,
                'Montant' => $montant,
                "Type d'abonnement" => $paymentType,
                'Date de début' => $startDate,
                'Statut' => $status
            ]);
            Validator::validateValidId($stockiId);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            Validator::validateValueInEnum($paymentType, PaymentType::getValues());
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateDate($startDate, 'Date de début');
            $this->stockiService->updateStocki((int)$stockiId, $paymentType, (float)$montant, $status, $startDate);
            Response::success(null, "Abonnement modifié avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'abonnement");
        }
    }

    public function updateStockiStatus()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $stockiId = trim($data['id'] ?? '');
            $status  = trim($data['status'] ?? '');
            Validator::validateRequiredFields([
                'ID abonnement' => $stockiId,
                'Statut' => $status
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateValidId($stockiId);
            $this->stockiService->updateStockiStatus((int)$stockiId, $status);
            Response::success(null, "Statut de l'abonnement mis à jour avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'abonnement");
        }
    }

    public function deleteStocki()
    {
        try {
            Validator::validateMethod('DELETE');
            $stockiId = trim($this->input()['abonnToDelete'] ?? '');
            Validator::validateRequiredFields([
                'id de stocki' => $stockiId
            ]);
            Validator::validateValidId($stockiId);
            $this->stockiService->deleteStocki((int)$stockiId);

            Response::success(null, "Abonnement supprimé avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer l'abonnement");
        }
    }
}
