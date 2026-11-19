<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Logging\Logger;
use App\Repositories\UserRepository;
use App\Security\PasswordHasher;
use App\Validators\Validator;

/**
 * « Mon profil » et « Configuration » de l'utilisateur connecté :
 * il ne peut modifier que son propre compte (ni son rôle ni ses droits).
 */
class ProfileController
{
    private UserRepository $userRepo;
    private int $userId;

    public function __construct()
    {
        $this->userRepo = new UserRepository(Database::getInstance()->getConnection());
        $this->userId = (int) ($_SESSION['user_id'] ?? 0);
    }

    private function input(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    private function me(): array
    {
        $user = $this->userRepo->getUser($this->userId);
        if (!$user) {
            throw ValidationException::NotFound("Compte introuvable.");
        }
        return $user;
    }

    public function getProfile()
    {
        try {
            Validator::validateMethod('GET');
            $user = $this->me();
            unset($user['password']);
            Response::success($user, 'Profil récupéré avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer le profil');
        }
    }

    public function updateProfile()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $email = trim($data['email'] ?? '');
            $tel = trim($data['tel'] ?? '');
            Validator::validateRequiredFields(['Nom' => $nom, 'Prénom' => $prenom, 'Email' => $email, 'Téléphone' => $tel]);
            Validator::validateEmail($email);
            Validator::validateMaxLength($nom, 20, 'Nom');
            Validator::validateMaxLength($prenom, 20, 'Prénom');
            Validator::validateMaxLength($email, 40, 'Email');
            Validator::validateMaxLength($tel, 20, 'Téléphone');
            $me = $this->me();
            if ($this->userRepo->isNewEmailExist($this->userId, $email)) {
                throw ValidationException::emailExist();
            }
            // rôle et droits inchangés : seuls les administrateurs les modifient
            $this->userRepo->updateUser($this->userId, $email, $nom, $prenom, $tel, $me['role'], (bool) $me['is_admin']);
            $_SESSION['username'] = $prenom;
            Response::success(null, 'Profil mis à jour avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de mettre à jour le profil');
        }
    }

    public function changePassword()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $current = (string) ($data['currentPassword'] ?? '');
            $new = (string) ($data['newPassword'] ?? '');
            $confirm = (string) ($data['confirmPassword'] ?? '');
            Validator::validateRequiredFields([
                'Mot de passe actuel' => $current,
                'Nouveau mot de passe' => $new,
                'Confirmation' => $confirm,
            ]);
            if (!password_verify($current, $this->me()['password'])) {
                throw ValidationException::invalidValue("Le mot de passe actuel est incorrect.");
            }
            if ($new !== $confirm) {
                throw ValidationException::invalidValue("La confirmation ne correspond pas au nouveau mot de passe.");
            }
            if ($new === $current) {
                throw ValidationException::invalidValue("Le nouveau mot de passe doit être différent de l'actuel.");
            }
            Validator::validateNewPassword($new);
            $this->userRepo->updatePassword($this->userId, PasswordHasher::hashPassword($new));
            Response::success(null, 'Mot de passe modifié avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de modifier le mot de passe');
        }
    }
}
