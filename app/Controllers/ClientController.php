<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use App\Services\ClientHistoryService;
use App\Services\MaintainService;
use App\Services\StockiService;
use App\Repositories\ClientHistoryRepository;
use App\Repositories\HistoricRepository;
use App\Repositories\MaintainRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\StockiRepository;

class ClientController
{
    private ClientService $clientService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->clientService = new ClientService(new ClientRepository($db));
    }

    public function getClients()
    {
        try {
            Validator::validateMethod('GET');

            Response::success($this->clientService->getClients(), "Clients récupérés avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer les clients");
        }
    }

    // Historique complet d'un client : paiements reçus et impayés, tous modules
    public function getClientHistory()
    {
        try {
            Validator::validateMethod('GET');
            $id = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields(['ID client' => $id]);
            Validator::validateValidId($id);
            $db = Database::getInstance()->getConnection();
            $payments = new PaymentRepository($db);
            $history = new ClientHistoryService(
                new ClientHistoryRepository($db),
                new ClientRepository($db),
                new MaintainService($db, new MaintainRepository($db), $payments, new HistoricRepository($db), new ProjectRepository($db)),
                new StockiService($db, new StockiRepository($db), new HistoricRepository($db), $payments, new ClientRepository($db))
            );
            Response::success($history->getHistory((int) $id), "Historique récupéré avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer l'historique du client");
        }
    }

    public function createClient()
    {
        try {
            Validator::validateMethod('POST');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $nom = trim($data['nom'] ?? '');
            $adresse = trim($data['adresse'] ?? '');
            $email = trim($data['email'] ?? '');
            $tel = trim($data['tel'] ?? '');
            Validator::validateRequiredFields([
                'Nom' => $nom,
                'Adresse' => $adresse,
                'Email' => $email,
                'Téléphone' => $tel
            ]);
            Validator::validateEmail($email);
            Validator::validateMaxLength($nom, 40, 'Nom');
            Validator::validateMaxLength($email, 40, 'Email');
            Validator::validateMaxLength($tel, 40, 'Téléphone');
            Validator::validateMaxLength($adresse, 100, 'Adresse');
            $this->clientService->createClient($email, $nom, $tel, $adresse);
            Response::success(null, 'Client ajouté avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter le client");
        }
    }

    public function updateClient()
    {
        try {
            Validator::validateMethod('PATCH');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $id = trim($data['id'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $adresse = trim($data['adresse'] ?? '');
            $email = trim($data['email'] ?? '');
            $tel = trim($data['tel'] ?? '');
            Validator::validateRequiredFields([
                'ID' => $id,
                'Nom' => $nom,
                'Adresse' => $adresse,
                'Email' => $email,
                'Téléphone' => $tel
            ]);
            Validator::validateEmail($email);
            Validator::validateValidId($id);
            Validator::validateMaxLength($nom, 40, 'Nom');
            Validator::validateMaxLength($email, 40, 'Email');
            Validator::validateMaxLength($tel, 40, 'Téléphone');
            Validator::validateMaxLength($adresse, 100, 'Adresse');
            $this->clientService->updateClient((int)$id, $email, $nom, $tel, $adresse);
            Response::success(null, "Client modifié avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier le client");
        }
    }

    public function deleteClient()
    {
        try {
            Validator::validateMethod('DELETE');

            // Récupérer le contenu brut du corps de la requête
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            // Accéder à la valeur
            $id = trim($data['clientToDelete'] ?? '');

            Validator::validateRequiredFields(['ID client' => $id]);
            Validator::validateValidId($id);
            $this->clientService->deleteClient((int)$id);

            Response::success(null, "Client supprimé avec succès");
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer le client");
        }
    }
}
