<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\ClientRepository;

class ClientService
{
    public function __construct(private ClientRepository $clientRepo) {}

    public function getClients(): array
    {
        return $this->clientRepo->getClients();
    }

    public function createClient(string $email, string $nom, string $tel, string $adresse): int
    {
        if ($this->clientRepo->isEmailExist($email)) {
            throw ValidationException::emailExist();
        }

        return $this->clientRepo->addClient($email, $nom, $tel, $adresse);
    }

    public function updateClient(int $id, string $email, string $nom, string $tel, string $adresse): void
    {
        if ($this->clientRepo->isNewEmailExist($id, $email)) {
            throw ValidationException::emailExist();
        }

        $this->clientRepo->updateClient($id, $email, $nom, $tel, $adresse);
    }

    public function deleteClient(int $id): void
    {
        $this->clientRepo->deleteClient($id);
    }
}
