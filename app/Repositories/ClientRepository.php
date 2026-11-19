<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;
use PDO;

class ClientRepository
{
    private PDO $connection;
    private const TABLE = "clients";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countClients(): int
    {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function isEmailExist(string $email): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE email=:email 
            LIMIT 1"
        );
        $stmt->execute([
            ':email' => $email
        ]);
        return (bool)$stmt->fetch();
    }

    public function isNewEmailExist(int $id, string $email): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE email=:email 
            AND idclient!=:id 
            LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':email' => $email
        ]);
        return (bool)$stmt->fetch();
    }

    public function getClientByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE email=:email"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClients(): array
    {
        $stmt = $this->connection->query(
            "SELECT * 
            FROM " . self::TABLE
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteClient(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . "
            WHERE idclient=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("Le client n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function updateClient(int $id, string $email, string $nom, string $tel, string $adresse): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET email=:email,
                adresse=:adresse,
                nom=:nom,
                tel=:tel
                WHERE idclient=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':email' => $email,
            ':nom' => $nom,
            ':tel' => $tel,
            ':adresse' => $adresse
        ]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("Le client n'existe pas ou n'a pas pu être modifié.");
        }
    }

    public function addClient(string $email, string $nom, string $tel, string $adresse): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (email,nom,tel,adresse) 
            VALUES (:email,:nom,:tel,:adresse)"
        );

        $stmt->execute([
            ':email' => $email,
            ':nom' => $nom,
            ':tel' => $tel,
            ':adresse' => $adresse
        ]);
        $id = (int)$this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout du client.");
        }
        return $id;
    }
}
