<?php

namespace App\Repositories;

use PDO;
use App\Exceptions\DatabaseException;

class UserRepository
{
    private PDO $connection;
    private const TABLE = "users";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countUsers(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function isNewEmailExist(int $id, string $email): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE email=:email 
            AND iduser!=:id 
            LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':email' => $email
        ]);
        return (bool)$stmt->fetch();
    }

    public function isEmailExist(string $email): bool
    {
        $stmt = $this->connection->prepare(
            "
            SELECT * 
            FROM " . self::TABLE . " 
            WHERE email=:email 
            LIMIT 1"
        );
        $stmt->execute([
            ':email' => $email
        ]);
        return (bool)$stmt->fetch();
    }

    public function getUser(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM  " . self::TABLE . " 
            WHERE iduser=:id 
            LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUsers(): array
    {
        $stmt = $this->connection->query(
            "
            SELECT iduser, email, nom, prenom, role, tel, is_admin 
            FROM  " . self::TABLE
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . " 
            WHERE iduser=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'utilisateur n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function countAdmins(): int
    {
        return (int) $this->connection->query("SELECT COUNT(*) FROM " . self::TABLE . " WHERE is_admin=1")->fetchColumn();
    }

    public function updateUser(int $id, string $email, string $nom, string $prenom, string $tel, string $role, bool $isAdmin): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "
                SET email=:email,
                nom=:nom,
                prenom=:prenom,
                tel=:tel,
                role=:role,
                is_admin=:is_admin
                WHERE iduser=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':email' => $email,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':tel' => $tel,
            ':role' => $role,
            ':is_admin' => (int) $isAdmin
        ]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET password=:password 
                WHERE iduser=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':password' => $password,
        ]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'utilisateur n'existe pas ou n'a pas pu être modifié.");
        }
    }

    public function addUser(string $email, string $password, string $nom, string $prenom, string $tel, string $role, bool $isAdmin): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (email,password,nom,prenom,tel,role,is_admin) 
            VALUES (:email,:password,:nom,:prenom,:tel,:role,:is_admin)"
        );

        $stmt->execute([
            ':email' => $email,
            ':password' => $password,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':tel' => $tel,
            ':role' => $role,
            ':is_admin' => (int) $isAdmin
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout du l'utilisateur.");
        }
        return $id;
    }
}
