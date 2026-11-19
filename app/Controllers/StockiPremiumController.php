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


    // Corps JSON (envoyé par la page) ou, à défaut, champs de formulaire
    private function input(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : $_POST;
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


    public function createStockiPremium()
    {

        try {
            Validator::validateMethod('POST');
            $data = $this->input();
            $nbrEcheance  = trim($data['nbrEcheance'] ?? '');
            $idclient = trim($data['idclient'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $avance = trim($data['avance'] ?? '') ?: '0';
            $comment  = trim($data['comment'] ?? '');
            $datedebut = trim($data['datedebut'] ?? '') ?: date('Y-m-d');
            Validator::validateRequiredFields([
                'Client' => $idclient,
                'Montant' => $montant,
                "Nombre d'échéances" => $nbrEcheance
            ]);
            Validator::validateValidId($idclient);
            Validator::validatePositiveNumbers(['Avance payée' => $avance, 'Montant' => $montant, "Nombre d'échéances" => $nbrEcheance]);
            Validator::validateValidId($nbrEcheance);
            Validator::validateMaxLength($comment, 100, 'Commentaire');
            Validator::validateDate($datedebut, 'Date de début');
            $this->stockiPService->createStockiPremium((int)$idclient, (float)$montant, (float)$avance, $comment, (int)$nbrEcheance, $datedebut);

            Response::success(null, "Licence ajoutée avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter la licence.");
        }
    }

    public function updateStockiPremium()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $stockiId = trim($data['id'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $nbrEcheance  = trim($data['nbrEcheance'] ?? '');
            $datedebut = trim($data['datedebut'] ?? '');
            Validator::validateRequiredFields([
                'ID licence' => $stockiId,
                'Montant' => $montant,
                "Nombre d'échéances" => $nbrEcheance,
                'Date de début' => $datedebut
            ]);
            Validator::validateValidId($stockiId);
            // le nombre d'échéances (0 si soldée) est contrôlé par le service s'il change
            Validator::validatePositiveNumbers(['Montant' => $montant, "Nombre d'échéances" => $nbrEcheance]);
            Validator::validateDate($datedebut, 'Date de début');
            $this->stockiPService->updateStockiPremium((int)$stockiId, (float)$montant, (int)$nbrEcheance, $datedebut);
            Response::success(null, "Licence modifiée avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier la licence");
        }
    }

    public function getInstallments()
    {
        try {
            Validator::validateMethod('GET');
            $stockiId = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields(['ID licence' => $stockiId]);
            Validator::validateValidId($stockiId);
            Response::success($this->stockiPService->getInstallments((int)$stockiId), "Échéances récupérées avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les échéances");
        }
    }

    public function payInstallment()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $stockiId = trim($data['id'] ?? '');
            $idpay = trim($data['idpay'] ?? '');
            Validator::validateRequiredFields(['ID licence' => $stockiId, 'ID échéance' => $idpay]);
            Validator::validateValidId($stockiId);
            Validator::validateValidId($idpay);
            $this->stockiPService->payInstallment((int)$stockiId, (int)$idpay);
            Response::success(null, "Échéance marquée comme payée");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'enregistrer le paiement");
        }
    }

    public function deleteStockiPremium()
    {
        try {
            Validator::validateMethod('DELETE');
            $stockiId = trim($this->input()['abonnToDelete'] ?? '');
            Validator::validateRequiredFields([
                'id de stocki' => $stockiId
            ]);
            Validator::validateValidId($stockiId);
            $this->stockiPService->deleteStockiPremium((int)$stockiId);

            Response::success(null, "Licence supprimée avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer la licence");
        }
    }
}
