<?php

namespace App\Repositories;

use App\Enums\SubscriptionType;
use App\Exceptions\DatabaseException;
use PDO;

class MaintainRepository
{
    private PDO $connection;
    private const TABLE = "maintains";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countMaintains(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function isMaintainExists(int $id_proj): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE id_proj=:id_proj 
            LIMIT 1"
        );
        $stmt->execute([
            ':id_proj' => $id_proj
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function isMaintainExistsExceptUpdated(int $id, int $id_proj): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE id_proj=:id_proj 
            AND id!=:id
            LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':id_proj' => $id_proj
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function getMaintainById(int $id): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                m.id,
                m.id_proj,
                p.idpay,
                p.echeance,
                p.commentaire,
                p.dateech,
                p.status 
            FROM  " . self::TABLE . " AS m 
            INNER JOIN payments AS p 
                ON m.id_proj=p.parent_id 
                AND p.parent_type=:parent_type 
            WHERE m.id=:id LIMIT 1"
        );
        $stmt->execute([
            ':parent_type' => SubscriptionType::MAINTAIN,
            ':id' => $id
        ]);
        $maintain = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($maintain === false) {
            throw DatabaseException::operationFailed("L'abonnement est introuvable.");
        }
        return $maintain;
    }

    public function getMaintains(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                m.id,
                m.id_proj,
                pr.titre,
                c.idclient,
                c.nom AS client_nom,
                c.tel AS client_tel,
                p.echeance,
                p.commentaire,
                p.dateech,
                p.status 
            FROM  " . self::TABLE . "  m
            JOIN projects AS pr 
                ON m.id_proj=pr.idproj
            JOIN clients AS c 
                ON pr.idclient=c.idclient
            INNER JOIN payments AS p
                ON m.id=p.parent_id
                AND p.parent_type=:parent_type"
        );
        $stmt->execute([':parent_type' => SubscriptionType::MAINTAIN]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteMaintain(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . "
            WHERE id=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'abonnement n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function updateMaintain(int $id, int $id_proj): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
            SET id_proj=:id_proj,
            WHERE id=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':id_proj' => $id_proj
        ]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'abonnement n'existe pas ou n'a pas pu être modifié.");
        }
    }

    public function addMaintain(int $id_proj): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (id_proj) 
            VALUES (:id_proj)"
        );

        $stmt->execute([
            ':id_proj' => $id_proj,
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout de l'abonnement.");
        }
        return $id;
    }
}
