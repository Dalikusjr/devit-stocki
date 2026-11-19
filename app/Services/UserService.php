<?php

namespace App\Services;

use App\Security\PasswordHasher;
use App\Repositories\UserRepository;
use App\Exceptions\ValidationException;

class UserService
{
    public function __construct(private UserRepository $userRepo, private PasswordHasher $passwordHasher) {}
    public function getUsers(): array
    {
        return $this->userRepo->getUsers();
    }

    public function createUser(string $email, string $password, string $nom, string $prenom, string $tel, string $role, bool $isAdmin): int
    {
        if ($this->userRepo->isEmailExist($email)) {
            throw ValidationException::emailExist();
        }
        $hashedPassword = $this->passwordHasher::hashPassword($password);

        return $this->userRepo->addUser(
            $email,
            $hashedPassword,
            $nom,
            $prenom,
            $tel,
            $role,
            $isAdmin
        );
    }

    // $currentUserId : l'administrateur qui fait la modification
    public function updateUser(int $id, string $email, string $password, string $nom, string $prenom, string $tel, string $role, bool $isAdmin, int $currentUserId): void
    {
        $user = $this->userRepo->getUser($id);
        if (!$user) {
            throw ValidationException::NotFound("Utilisateur introuvable.");
        }
        if ($user['is_admin'] && !$isAdmin) {
            if ($id === $currentUserId) {
                throw ValidationException::NotAllowed("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            }
            if ($this->userRepo->countAdmins() <= 1) {
                throw ValidationException::NotAllowed("Il doit rester au moins un administrateur.");
            }
        }
        if ($this->userRepo->isNewEmailExist($id, $email)) {
            throw ValidationException::emailExist();
        }
        if ($password !== '') {
            $hashedPassword = $this->passwordHasher::hashPassword($password);
            $this->userRepo->updatePassword($id, $hashedPassword);
        }

        $this->userRepo->updateUser(
            $id,
            $email,
            $nom,
            $prenom,
            $tel,
            $role,
            $isAdmin
        );
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepo->getUser($id);
        if ($user && $user['is_admin'] && $this->userRepo->countAdmins() <= 1) {
            throw ValidationException::NotAllowed("Il doit rester au moins un administrateur.");
        }
        $this->userRepo->deleteUser($id);
    }
}
