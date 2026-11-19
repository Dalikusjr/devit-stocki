<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\HistoricRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StockiPremiumRepository;
use App\Services\StockiPremiumService;

class StockiPremiumController
{
    private StockiPremiumService $stockiPService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->stockiPService = new StockiPremiumService(
            $db,
            new StockiPremiumRepository($db),
            new ClientRepository($db),
            new PaymentRepository($db),
            new HistoricRepository($db)
        );
    }


    public function getStockiPremiums()
    {
        try {
            Validator::validateMethod('GET');

            Response::success($this->stockiPService->getStockiPremiums(), "Abonnements récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les abonnements");
        }
    }

    public function getStockiPremiumHistorics()
    {
        try {
            Validator::validateMethod('GET');
            $stockiId = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields([
                'id de stocki' => $stockiId
            ]);
            Validator::validateValidId($stockiId);
            Response::success($this->stockiPService->getStockiPremiumHistorics((int)$stockiId), "Historique récupéré avec succès");
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
            $rows = $this->stockiPService->getClientsForAutoComplete();
            if (!empty($search)) {
                $rows = array_filter($rows, function ($item) use ($search) {
                    return stripos($item['nom'], $search) !== false;
                });
            }
            Response::success($rows, "Clients récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les clients");
        }
    }


    public function createStockiPremium()
    {

        try {
            Validator::validateMethod('POST');
            $nbrEcheance  = trim($_POST['nbrEcheance'] ?? '');
            $idclient = trim($_POST['idclient'] ?? '');
            $montant = trim($_POST['montant'] ?? '');
            $avance = trim($_POST['avance'] ?? '');
            $comment  = trim($_POST['comment'] ?? '');
            Validator::validateRequiredFields([
                'id de client' => $idclient,
                'montant d\'abonnement' => $montant,
                'Avance payé' => $avance,
                'Nombre d\'échéance' => $nbrEcheance,
                'Commentaire' => $comment
            ]);
            Validator::validateValidId($idclient);
            Validator::validateValidId($nbrEcheance);
            Validator::validatePositiveNumbers(['Avance payé' => $avance, 'Montant' => $montant]);
            $this->stockiPService->createStockiPremium((int)$idclient, (float)$montant, (float)$avance, $comment, (int)$nbrEcheance);

            Response::success(null, "Abonnement ajouté avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter l'abonnement.");
        }
    }

    public function updateStockiPremium()
    {
        try {
            Validator::validateMethod('POST');
            $stockiId = trim($_POST['id'] ?? '');
            $montant = trim($_POST['montant'] ?? '');
            $nbrEcheance  = trim($_POST['nbrEcheance'] ?? '');
            Validator::validateRequiredFields([
                'Stocki id' => $stockiId,
                'montant d\'abonnement' => $montant,
                'Nombre d\'échéance' => $nbrEcheance
            ]);
            Validator::validateValidId($stockiId);
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            $this->stockiPService->updateStockiPremium((int)$stockiId, (float)$montant, (int)$nbrEcheance);
            Response::success(null, "Abonnement modifié avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'abonnement");
        }
    }

    public function deleteStockiPremium()
    {
        try {
            Validator::validateMethod('POST');
            $stockiId = trim($_POST['abonnToDelete'] ?? '');
            Validator::validateRequiredFields([
                'id de stocki' => $stockiId
            ]);
            Validator::validateValidId($stockiId);
            $this->stockiPService->deleteStockiPremium((int)$stockiId);

            Response::success(null, "Abonnement supprimé avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer l'abonnement");
        }
    }
}
