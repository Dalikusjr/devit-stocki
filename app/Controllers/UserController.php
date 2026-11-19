<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\UserRepository;
use App\Security\PasswordHasher as PassHash;
use App\Services\UserService;

class UserController
{
    private UserService $userService;
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->userService = new UserService(new UserRepository($db), new PassHash());
    }

    public function getUsers()
    {
        try {
            Validator::validateMethod('GET');

            Response::success($this->userService->getUsers(), 'Utilisateurs récupérés avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les utilisateurs.');
        }
    }
    public function createUser()
    {
        try {
            Validator::validateMethod('POST');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $role = trim($data['role'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $tel = trim($data['tel'] ?? '');
            Validator::validateRequiredFields([
                'Nom' => $nom,
                'Prénom' => $prenom,
                'Rôle' => $role,
                'Email' => $email,
                'Mot de passe' => $password,
                'Téléphone' => $tel
            ]);
            Validator::validateEmail($email);
            Validator::validatePassword($password);

            $this->userService->createUser($email, $password, $nom, $prenom, $tel, $role);
            Response::success(null, 'Utilisateur ajouté avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible d\'ajouter l\'utilisateur.');
        }
    }

    public function updateUser()
    {
        try {
            Validator::validateMethod('PATCH');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $id = trim($data['id'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $role = trim($data['role'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $tel = trim($data['tel'] ?? '');
            Validator::validateRequiredFields([
                'ID' => $id,
                'Nom' => $nom,
                'Prénom' => $prenom,
                'Rôle' => $role,
                'Email' => $email,
                'Téléphone' => $tel
            ]);
            Validator::validateEmail($email);
            Validator::validateValidId($id);
            $id = (int) $id;
            $this->userService->updateUser($id, $email, $password, $nom, $prenom, $tel, $role);
            Response::success(null, 'Utilisateur modifié avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de modifier l\'utilisateur.');
        }
    }

    public function deleteUser()
    {
        try {
            Validator::validateMethod('DELETE');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $id = trim($data['userToDelete'] ?? '');
            Validator::validateRequiredFields(['ID utilisateur' => $id]);
            Validator::validateValidId($id);
            $id = (int) $id;
            $this->userService->deleteUser($id);
            Response::success(null, 'Utilisateur supprimé avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de supprimer l\'utilisateur');
        }
    }
}
