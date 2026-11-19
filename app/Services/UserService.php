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

    public function createUser(string $email, string $password, string $nom, string $prenom, string $tel, string $role): int
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
            $role
        );
    }

    public function updateUser(int $id, string $email, string $password, string $nom, string $prenom, string $tel, string $role): void
    {
        if ($this->userRepo->isNewEmailExist($id, $email)) {
            throw ValidationException::emailExist();
        }
        if ($password) {
            $hashedPassword = $this->passwordHasher::hashPassword($password);
            $this->userRepo->updatePassword($id, $hashedPassword);
        }

        $this->userRepo->updateUser(
            $id,
            $email,
            $nom,
            $prenom,
            $tel,
            $role
        );
    }

    public function deleteUser(int $id): void
    {
        $this->userRepo->deleteUser($id);
    }
}
