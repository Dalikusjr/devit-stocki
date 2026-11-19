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
